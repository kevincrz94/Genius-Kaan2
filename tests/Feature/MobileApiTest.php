<?php

use App\Models\CognitiveSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

it('exposes a public api health check', function () {
    $response = $this->getJson('/api/health');

    $response
        ->assertOk()
        ->assertJsonPath('status', 'ok')
        ->assertJsonPath('database.ok', true)
        ->assertJsonStructure([
            'app',
            'environment',
            'laravel',
            'php',
            'database' => ['connection', 'ok'],
            'cognifit' => ['configured', 'client_id_present'],
        ]);
});

it('exposes public cognifit configuration status', function () {
    $response = $this->getJson('/api/cognifit/status');

    $response
        ->assertOk()
        ->assertJsonStructure([
            'configured',
            'client_id_present',
        ]);
});

it('issues a mobile api token on login', function () {
    User::factory()->create([
        'email' => 'terapeuta@geniuskaan.com',
        'password' => Hash::make('secret123'),
    ]);

    $response = $this->postJson('/api/auth/login', [
        'email' => 'terapeuta@geniuskaan.com',
        'password' => 'secret123',
    ]);

    $response
        ->assertOk()
        ->assertJsonStructure([
            'token',
            'token_type',
            'user' => ['id', 'name', 'email'],
        ]);
});

it('lists users for authenticated mobile clients', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;

    $response = $this
        ->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/users');

    $response
        ->assertOk()
        ->assertJsonFragment([
            'id' => (string) $user->id,
            'email' => $user->email,
        ]);
});

it('creates cognitive sessions for authenticated mobile clients', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;

    $response = $this
        ->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/sessions', [
            'user_id' => $user->id,
            'area' => 'Atencion',
            'game_key' => 'THE_BLUE_SHAPE',
            'duration_minutes' => 12,
        ]);

    $response
        ->assertCreated()
        ->assertJsonPath('user_id', (string) $user->id)
        ->assertJsonPath('status', 'pending');
});

it('builds a user cognitive report', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;

    CognitiveSession::query()->create([
        'user_id' => $user->id,
        'area' => 'Memoria',
        'game_key' => 'MAHJONG',
        'duration_minutes' => 15,
        'status' => 'completed',
        'score' => 84,
    ]);

    $response = $this
        ->withHeader('Authorization', "Bearer {$token}")
        ->getJson("/api/reports/users/{$user->id}");

    $response
        ->assertOk()
        ->assertJsonPath('user_id', (string) $user->id)
        ->assertJsonStructure([
            'summary',
            'generated_at',
            'skills' => [
                ['name', 'score', 'trend'],
            ],
        ]);
});
