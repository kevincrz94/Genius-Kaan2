<?php

use App\Models\User;

it('redirects guests away from the profile page', function () {
    $this->get(route('user.profile'))
        ->assertRedirect(route('user.login'));
});

it('shows the operational profile overview with a single page heading', function () {
    $email = 'kevin+' . uniqid() . '@example.com';

    $user = User::factory()->create([
        'name' => 'Kevin Cruz',
        'email' => $email,
        'rank' => 'Inspector',
        'badge_number' => 'GK-01',
        'status' => 1,
    ]);

    $response = $this->withSession([
        'operational_user_id' => $user->id,
    ])->get(route('user.profile'));

    $response
        ->assertOk()
        ->assertSee('Resumen del perfil.')
        ->assertSee('Consulta tus datos, avance cognitivo y asignación operativa.')
        ->assertSee('Identidad y asignación.')
        ->assertSee('Continúa tu entrenamiento.')
        ->assertSee('Kevin Cruz')
        ->assertSee('Inspector')
        ->assertSee('ID GK-01')
        ->assertSee('Correo institucional')
        ->assertSee('<h2 class="profile-overview-title">Resumen del perfil.</h2>', false)
        ->assertSee('<h2 class="profile-summary-name">Kevin Cruz</h2>', false)
        ->assertDontSee('<h2 class="profile-overview-title">Kevin Cruz</h2>', false);

    expect(substr_count($response->getContent(), $email))->toBe(1);
});
