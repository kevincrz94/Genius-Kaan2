@extends('layouts.public')

@section('content')
    <section class="section">
        <div class="section-header">
            <div>
                <span class="eyebrow">Entrenamiento operativo</span>
                <h2>Juegos disponibles para {{ $user->name }}.</h2>
            </div>
            <form method="post" action="{{ route('user.logout') }}">
                @csrf
                <button type="submit" class="btn btn-secondary">Cerrar sesión</button>
            </form>
        </div>

        <div class="dashboard">
            <article class="panel card">
                <span class="eyebrow">Perfil</span>
                <h2>Datos operativos.</h2>

                <div class="stack-list">
                    <div class="line-item">
                        <div>
                            <strong>Placa / ID</strong>
                            <p>{{ $user->badge_number ?: 'Sin placa registrada' }}</p>
                        </div>
                    </div>
                    <div class="line-item">
                        <div>
                            <strong>Rango / cargo</strong>
                            <p>{{ $user->rank ?: 'Sin rango registrado' }}</p>
                        </div>
                    </div>
                    <div class="line-item">
                        <div>
                            <strong>Unidad y grupo</strong>
                            <p>
                                {{ $user->securityUnit?->name ?: 'Sin unidad' }}
                                /
                                {{ $user->operationalGroup?->name ?: 'Sin grupo' }}
                            </p>
                        </div>
                    </div>
                    <div class="line-item">
                        <div>
                            <strong>Área asignada</strong>
                            <p>{{ $user->assignment_area ?: 'Sin área asignada' }}</p>
                        </div>
                    </div>
                </div>
            </article>

            <article class="panel card scoreboard-card">
                <span class="eyebrow">Estado Cognifit</span>
                <h2>{{ filled($user->cognifit_user_token) ? 'Token activo.' : 'Token pendiente.' }}</h2>
                <p class="section-copy">
                    @if (filled($user->cognifit_user_token))
                        Tu usuario está listo para iniciar sesiones de entrenamiento.
                    @else
                        El sistema intentó registrar tu usuario automáticamente en Cognifit, pero no se pudo completar.
                    @endif
                </p>

                @unless (filled($user->cognifit_user_token))
                    <div class="hero-note">
                        {{ $cognifitError ?: 'Solicita al administrador revisar las credenciales de Cognifit.' }}
                    </div>
                @endunless
            </article>
        </div>
    </section>

    <section class="section">
        <div class="section-header">
            <div>
                <span class="eyebrow">Biblioteca</span>
                <h2>Selecciona un entrenamiento.</h2>
            </div>
            <p class="section-copy">
                Cada sesión se abre con tu token Cognifit para conservar la trazabilidad individual.
            </p>
        </div>

        <div class="grid-3">
            @foreach ($availableGames as $game)
                <article class="card feature-card">
                    <div class="accent-dot" style="--card-accent: #00254c"></div>
                    <h3>{{ $game['title'] }}</h3>
                    <p>{{ $game['focus'] }}</p>

                    <div class="cta-row">
                        @if (filled($user->cognifit_user_token))
                            <a class="btn btn-primary" href="{{ route('start.game', [
                                'participant' => $user->name,
                                'goal' => $game['focus'],
                                'locale' => $user->cognifit_locale ?: 'es',
                                'user_token' => $user->cognifit_user_token,
                                'game_key' => $game['key'],
                            ]) }}">
                                Iniciar
                            </a>
                        @else
                            <span class="soft-chip">Requiere token</span>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    </section>
@endsection
