<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CognitiveSession;
use App\Models\CognitiveSkillScore;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class ReportApiController extends Controller
{
    public function userReport(User $user): JsonResponse
    {
        $sessions = CognitiveSession::query()
            ->where('user_id', $user->id)
            ->get();

        $scores = CognitiveSkillScore::query()
            ->where('user_id', $user->id)
            ->orderBy('name')
            ->orderByDesc('measured_at')
            ->get()
            ->groupBy('name')
            ->map(function ($items, string $name) {
                $latest = $items->first();

                return [
                    'name' => $name,
                    'score' => (float) $latest->score,
                    'trend' => $latest->trend,
                ];
            })
            ->values();

        if ($scores->isEmpty()) {
            $scores = collect($this->fallbackScores($sessions));
        }

        return response()->json([
            'user_id' => (string) $user->id,
            'summary' => $this->summary($sessions->count(), $scores->count()),
            'generated_at' => now()->toISOString(),
            'skills' => $scores,
        ]);
    }

    private function fallbackScores($sessions): array
    {
        $completed = $sessions->where('status', 'completed');

        if ($completed->isEmpty()) {
            return [
                ['name' => 'Atencion', 'score' => 0, 'trend' => 'stable'],
                ['name' => 'Memoria', 'score' => 0, 'trend' => 'stable'],
                ['name' => 'Razonamiento', 'score' => 0, 'trend' => 'stable'],
            ];
        }

        return $completed
            ->groupBy('area')
            ->map(function ($items, string $area) {
                $average = $items
                    ->filter(fn (CognitiveSession $session) => $session->score !== null)
                    ->avg('score') ?? 0;

                return [
                    'name' => $area,
                    'score' => round((float) $average, 2),
                    'trend' => 'stable',
                ];
            })
            ->values()
            ->all();
    }

    private function summary(int $sessionCount, int $skillCount): string
    {
        if ($sessionCount === 0) {
            return 'Aun no hay sesiones registradas para este usuario.';
        }

        return "Reporte generado con {$sessionCount} sesiones y {$skillCount} habilidades medidas.";
    }
}
