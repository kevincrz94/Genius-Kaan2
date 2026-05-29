<?php

use App\Models\CognitiveSession;
use App\Models\User;
use App\Services\CognifitService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Mockery\MockInterface;

uses(RefreshDatabase::class);

it('syncs a completed cognifit session through a signed launcher url', function () {
    $user = User::factory()->create([
        'cognifit_user_token' => 'cognifit-user-token',
    ]);

    $signedUrl = URL::temporarySignedRoute('cognifit.session.sync', now()->addMinutes(30), ['user' => $user]);

    $this->mock(CognifitService::class, function (MockInterface $mock) use ($user) {
        $mock->shouldReceive('playedGames')
            ->once()
            ->withArgs(fn (User $candidate) => $candidate->is($user))
            ->andReturn([
                'historicalPlayedGames' => [
                    [
                        'key' => 'THE_BLUE_SHAPE',
                        'score' => 88,
                        'duration' => 720,
                    ],
                ],
            ]);

        $mock->shouldReceive('historicalScores')
            ->once()
            ->withArgs(fn (User $candidate) => $candidate->is($user))
            ->andReturn([]);
    });

    $response = $this->postJson($signedUrl, [
        'game_key' => 'THE_BLUE_SHAPE',
        'status' => 'completed',
        'mode' => 'gameMode',
    ]);

    $response
        ->assertOk()
        ->assertJsonPath('status', true)
        ->assertJsonPath('played_games', 1);

    $session = CognitiveSession::query()->sole();

    expect($session->user_id)->toBe($user->id);
    expect($session->status)->toBe('completed');
    expect((int) $session->duration_minutes)->toBe(12);
    expect((float) $session->score)->toBe(88.0);
});

it('keeps a completed session pending until Cognifit publishes results', function () {
    $user = User::factory()->create([
        'cognifit_user_token' => 'cognifit-user-token',
    ]);

    $signedUrl = URL::temporarySignedRoute('cognifit.session.sync', now()->addMinutes(30), ['user' => $user]);

    $this->mock(CognifitService::class, function (MockInterface $mock) use ($user) {
        $mock->shouldReceive('playedGames')
            ->once()
            ->withArgs(fn (User $candidate) => $candidate->is($user))
            ->andReturn(['historicalPlayedGames' => []]);

        $mock->shouldReceive('historicalScores')
            ->once()
            ->withArgs(fn (User $candidate) => $candidate->is($user))
            ->andReturn([]);
    });

    $response = $this->postJson($signedUrl, [
        'game_key' => 'THE_BLUE_SHAPE',
        'status' => 'completed',
        'mode' => 'gameMode',
    ]);

    $response
        ->assertOk()
        ->assertJsonPath('status', true)
        ->assertJsonPath('results_ready', false)
        ->assertJsonPath('sync_status', 'sync_delayed');

    $session = CognitiveSession::query()->sole();

    expect($session->status)->toBe('sync_delayed');
    expect($session->score)->toBeNull();
    expect($session->scheduled_for)->not->toBeNull();
});

it('syncs a delayed session from the scheduled command', function () {
    $user = User::factory()->create([
        'cognifit_user_token' => 'cognifit-user-token',
    ]);

    CognitiveSession::query()->create([
        'user_id' => $user->id,
        'area' => 'cognifit',
        'game_key' => 'THE_BLUE_SHAPE',
        'duration_minutes' => 0,
        'status' => 'sync_delayed',
        'completed_at' => now()->subMinutes(15),
        'scheduled_for' => now()->subMinute(),
        'metadata' => ['sync_attempts' => 1],
    ]);

    $this->mock(CognifitService::class, function (MockInterface $mock) use ($user) {
        $mock->shouldReceive('playedGames')
            ->once()
            ->withArgs(fn (User $candidate) => $candidate->is($user))
            ->andReturn([
                'historicalPlayedGames' => [
                    [
                        'key' => 'THE_BLUE_SHAPE',
                        'score' => 91,
                        'duration' => 600,
                    ],
                ],
            ]);

        $mock->shouldReceive('historicalScores')
            ->once()
            ->withArgs(fn (User $candidate) => $candidate->is($user))
            ->andReturn([]);
    });

    $this->artisan('cognifit:sync-sessions --limit=10')
        ->expectsOutput('CogniFit sync: 1 revisadas, 1 sincronizadas, 0 diferidas, 0 fallidas.')
        ->assertExitCode(0);

    $session = CognitiveSession::query()->sole();

    expect($session->status)->toBe('completed');
    expect((int) $session->duration_minutes)->toBe(10);
    expect((float) $session->score)->toBe(91.0);
});
