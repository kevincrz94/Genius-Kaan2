<?php

it('shows the public cognitive development landing page', function () {
    $response = $this->get('/');

    $response
        ->assertOk()
        ->assertSee('Genius Kaan')
        ->assertSee('Desarrollo cognitivo');
});

it('shows the launcher setup page', function () {
    $response = $this->get('/launcher');

    $response
        ->assertOk()
        ->assertSee('Configurar entrenamiento')
        ->assertSee('User token');
});

it('shows the launcher shell page', function () {
    $this->withoutVite();
    config()->set('services.cognifit.sdk_version', 'test-sdk-version');

    $response = $this->get('/start-game');

    $response
        ->assertOk()
        ->assertSee('Sesión cognitiva')
        ->assertSee('JavaScript')
        ->assertSee('test-sdk-version');
});
