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
