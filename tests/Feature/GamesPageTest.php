<?php

use App\Models\User;

it('renders the simulator dashboard overview for an active user', function () {
    $user = User::factory()->make([
        'name' => 'Kevin Cruz',
        'rank' => 'Inspector',
        'badge_number' => 'GK-01',
        'status' => 1,
        'cognifit_user_token' => 'token-123',
        'cognifit_locale' => 'es',
    ]);

    $html = view('user.games', [
        'user' => $user,
        'availableGames' => [
            [
                'key' => 'THE_BLUE_SHAPE',
                'title' => 'The Blue Shape',
                'focus' => 'Atención selectiva y velocidad de respuesta.',
                'image' => null,
                'skill_keys' => ['attention'],
                'skills' => ['Atención'],
            ],
            [
                'key' => 'MAHJONG',
                'title' => 'Mahjong',
                'focus' => 'Memoria visual y reconocimiento de patrones.',
                'image' => null,
                'skill_keys' => ['memory'],
                'skills' => ['Memoria'],
            ],
        ],
        'skillFilters' => [
            [
                'key' => 'attention',
                'label' => 'Atención',
                'icon' => null,
            ],
        ],
        'cognifitError' => null,
    ])->render();

    expect($html)
        ->toContain('Simuladores disponibles.')
        ->toContain('Tu panel operativo.')
        ->toContain('Acceso CogniFit listo')
        ->toContain('Mostrando 2 módulos distribuidos en 1 capacidad.')
        ->toContain('Iniciar módulo')
        ->toContain('data-skill-label="Atención"');
});

it('renders pending credential messaging when Cognifit access is missing', function () {
    $user = User::factory()->make([
        'name' => 'Kevin Cruz',
        'status' => 1,
        'cognifit_user_token' => null,
    ]);

    $html = view('user.games', [
        'user' => $user,
        'availableGames' => [
            [
                'key' => 'THE_BLUE_SHAPE',
                'title' => 'The Blue Shape',
                'focus' => 'Atención selectiva y velocidad de respuesta.',
                'image' => null,
                'skill_keys' => [],
                'skills' => [],
            ],
        ],
        'skillFilters' => [],
        'cognifitError' => 'Error de prueba CogniFit',
    ])->render();

    expect($html)
        ->toContain('Acceso CogniFit pendiente')
        ->toContain('Credencial pendiente de asignación')
        ->toContain('Error de prueba CogniFit');
});

it('allows an admin session to access the simulator portal', function () {
    $user = User::factory()->create([
        'name' => 'Admin Operativo',
        'role' => 'admin',
        'status' => 1,
        'onboarding_completed_at' => null,
        'cognifit_user_token' => 'token-admin-123',
        'cognifit_locale' => 'es',
    ]);

    $response = $this->withSession([
        'admin_id' => $user->id,
        'admin_role' => 'admin',
    ])->get(route('user.games'));

    $response
        ->assertOk()
        ->assertSee('Simuladores disponibles.')
        ->assertSee('Admin Operativo')
        ->assertSee('Acceso CogniFit listo')
        ->assertSessionHas('operational_user_id', $user->id);
});
