<?php

namespace App\Services;

use App\Models\CognitiveSession;
use App\Models\CognitiveSkillScore;
use App\Models\User;
use Throwable;

class CognifitSessionSyncService
{
    private const MAX_ATTEMPTS = 8;

    private const RETRY_MINUTES = [5, 10, 20, 40, 90, 180, 360, 720];

    public function __construct(private readonly CognifitService $cognifit)
    {
    }

    public function recordLauncherCallback(User $user, array $payload): array
    {
        $session = $this->findRecentSession($user, $payload['game_key'])
            ?: CognitiveSession::create([
                'user_id' => $user->id,
                'area' => 'cognifit',
                'game_key' => $payload['game_key'],
                'duration_minutes' => 0,
                'status' => $payload['status'] === 'completed' ? 'sync_pending' : 'cancelled',
                'started_at' => now(),
                'completed_at' => now(),
                'scheduled_for' => $payload['status'] === 'completed' ? now()->addMinutes(5) : null,
                'metadata' => [
                    'mode' => $payload['mode'] ?? 'gameMode',
                    'sync_attempts' => 0,
                    'launcher_callback_at' => now()->toISOString(),
                ],
            ]);

        if ($payload['status'] !== 'completed') {
            return [
                'session' => $session,
                'synced' => false,
                'played_games' => 0,
                'skill_scores' => 0,
                'message' => 'Actividad cancelada registrada.',
            ];
        }

        return $this->attemptSync($session, immediate: true);
    }

    public function syncDueSessions(int $limit = 50): array
    {
        $summary = [
            'checked' => 0,
            'synced' => 0,
            'delayed' => 0,
            'failed' => 0,
        ];

        CognitiveSession::query()
            ->with('user')
            ->whereIn('status', ['sync_pending', 'sync_delayed'])
            ->where(function ($query) {
                $query->whereNull('scheduled_for')
                    ->orWhere('scheduled_for', '<=', now());
            })
            ->oldest('scheduled_for')
            ->oldest('id')
            ->limit($limit)
            ->get()
            ->each(function (CognitiveSession $session) use (&$summary) {
                $summary['checked']++;

                $result = $this->attemptSync($session);

                if ($result['synced']) {
                    $summary['synced']++;
                } elseif ($session->fresh()?->status === 'sync_failed') {
                    $summary['failed']++;
                } else {
                    $summary['delayed']++;
                }
            });

        return $summary;
    }

    public function attemptSync(CognitiveSession $session, bool $immediate = false): array
    {
        $session->loadMissing('user');
        $user = $session->user;

        if (! $user) {
            $this->markFailed($session, 'Usuario no encontrado.');

            return $this->result($session, false, 0, 0, 'Usuario no encontrado.');
        }

        $playedGames = [];
        $scores = [];
        $syncError = null;

        try {
            $playedGames = $this->cognifit->playedGames($user);
            $scores = $this->cognifit->historicalScores($user);
        } catch (Throwable $th) {
            report($th);
            $syncError = $th->getMessage();
        }

        $latestGame = $this->latestPlayedGame($playedGames, (string) $session->game_key);

        if (! $latestGame) {
            $this->delaySession($session, $syncError ?: 'CogniFit aún no publica el resultado.', $immediate);

            return $this->result(
                $session->fresh() ?: $session,
                false,
                count((array) ($playedGames['historicalPlayedGames'] ?? [])),
                0,
                'Resultado pendiente de procesamiento por CogniFit.'
            );
        }

        $session->forceFill([
            'status' => 'completed',
            'score' => $this->scoreFromGame($latestGame),
            'duration_minutes' => $this->durationFromGame($latestGame),
            'scheduled_for' => null,
            'metadata' => array_merge($session->metadata ?? [], [
                'cognifit_game' => $latestGame,
                'synced_at' => now()->toISOString(),
                'sync_error' => null,
            ]),
        ])->save();

        $storedScores = $this->storeCognifitSkillScores($user, $session, $scores);

        return $this->result(
            $session,
            true,
            count((array) ($playedGames['historicalPlayedGames'] ?? [])),
            $storedScores,
            'Resultados sincronizados.'
        );
    }

    private function findRecentSession(User $user, string $gameKey): ?CognitiveSession
    {
        return CognitiveSession::query()
            ->where('user_id', $user->id)
            ->where('game_key', $gameKey)
            ->where('completed_at', '>=', now()->subMinutes(10))
            ->whereIn('status', ['sync_pending', 'sync_delayed', 'completed'])
            ->latest('id')
            ->first();
    }

    private function delaySession(CognitiveSession $session, string $reason, bool $immediate): void
    {
        $metadata = $session->metadata ?? [];
        $attempts = (int) ($metadata['sync_attempts'] ?? 0);

        if (! $immediate) {
            $attempts++;
        }

        if ($attempts >= self::MAX_ATTEMPTS) {
            $this->markFailed($session, $reason, $attempts);

            return;
        }

        $retryMinutes = self::RETRY_MINUTES[min($attempts, count(self::RETRY_MINUTES) - 1)];

        $session->forceFill([
            'status' => 'sync_delayed',
            'scheduled_for' => now()->addMinutes($retryMinutes),
            'metadata' => array_merge($metadata, [
                'sync_attempts' => $attempts,
                'sync_error' => $reason,
                'last_sync_attempt_at' => now()->toISOString(),
            ]),
        ])->save();
    }

    private function markFailed(CognitiveSession $session, string $reason, ?int $attempts = null): void
    {
        $metadata = $session->metadata ?? [];

        $session->forceFill([
            'status' => 'sync_failed',
            'scheduled_for' => null,
            'metadata' => array_merge($metadata, [
                'sync_attempts' => $attempts ?? (int) ($metadata['sync_attempts'] ?? self::MAX_ATTEMPTS),
                'sync_error' => $reason,
                'last_sync_attempt_at' => now()->toISOString(),
            ]),
        ])->save();
    }

    private function latestPlayedGame(array $playedGames, string $gameKey): ?array
    {
        return collect($playedGames['historicalPlayedGames'] ?? [])
            ->map(fn ($game) => (array) $game)
            ->filter(fn (array $game) => strtoupper((string) ($game['key'] ?? $game['game_key'] ?? '')) === strtoupper($gameKey))
            ->sortByDesc(fn (array $game) => $game['time'] ?? $game['date'] ?? $game['played_at'] ?? '')
            ->values()
            ->first();
    }

    private function scoreFromGame(?array $game): ?float
    {
        if (! $game) {
            return null;
        }

        foreach (['score', 'percentage', 'accuracy', 'result'] as $key) {
            if (isset($game[$key]) && is_numeric($game[$key])) {
                return round(min(100, max(0, (float) $game[$key])), 2);
            }
        }

        return null;
    }

    private function durationFromGame(?array $game): int
    {
        if (! $game) {
            return 0;
        }

        foreach (['duration_minutes', 'duration', 'time_spent'] as $key) {
            if (isset($game[$key]) && is_numeric($game[$key])) {
                $duration = (float) $game[$key];

                return (int) max(0, $duration > 180 ? round($duration / 60) : round($duration));
            }
        }

        return 0;
    }

    private function storeCognifitSkillScores(User $user, CognitiveSession $session, array $scores): int
    {
        $skillScores = collect($this->extractSkillScores($scores));

        if ($skillScores->isEmpty()) {
            return 0;
        }

        CognitiveSkillScore::query()
            ->where('cognitive_session_id', $session->id)
            ->delete();

        $stored = 0;

        foreach ($skillScores as $skill) {
            $name = trim((string) ($skill['name'] ?? $skill['key'] ?? ''));

            if ($name === '' || ! isset($skill['score']) || ! is_numeric($skill['score'])) {
                continue;
            }

            CognitiveSkillScore::create([
                'user_id' => $user->id,
                'cognitive_session_id' => $session->id,
                'name' => $name,
                'score' => round(min(100, max(0, (float) $skill['score'])), 2),
                'trend' => $skill['trend'] ?? 'stable',
                'measured_at' => now(),
            ]);

            $stored++;
        }

        return $stored;
    }

    private function extractSkillScores(mixed $payload): array
    {
        if (! is_array($payload)) {
            return [];
        }

        $scores = [];

        foreach ($payload as $key => $value) {
            if (is_array($value)) {
                if (isset($value['score']) && (isset($value['name']) || isset($value['key']))) {
                    $scores[] = $value;

                    continue;
                }

                foreach ($this->extractSkillScores($value) as $nested) {
                    $scores[] = $nested;
                }
            } elseif (is_numeric($value) && is_string($key)) {
                $scores[] = [
                    'name' => $key,
                    'score' => $value,
                ];
            }
        }

        return $scores;
    }

    private function result(CognitiveSession $session, bool $synced, int $playedGames, int $skillScores, string $message): array
    {
        return [
            'session' => $session,
            'synced' => $synced,
            'played_games' => $playedGames,
            'skill_scores' => $skillScores,
            'message' => $message,
        ];
    }
}
