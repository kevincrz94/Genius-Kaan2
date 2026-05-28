@extends('layouts.public')

@section('content')
    <section class="section">
        <div class="section-header">
            <div>
                <span class="eyebrow">Entrenamiento operativo</span>
                <h2>Módulos de entrenamiento operativo para {{ $user->name }}.</h2>
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
                    <div class="line-item">
                        <div>
                            <strong>Estado operativo</strong>
                            <p>{{ filled($user->cognifit_user_token) ? 'Apto para entrenamiento' : 'Requiere sincronización' }}</p>
                        </div>
                    </div>
                </div>
            </article>

            <article class="panel card scoreboard-card">
                <span class="eyebrow">Credencial CogniFit</span>
                <h2>{{ filled($user->cognifit_user_token) ? 'Credencial activa.' : 'Credencial pendiente.' }}</h2>
                <p class="section-copy">
                    @if (filled($user->cognifit_user_token))
                        Tu usuario está listo para iniciar sesiones de entrenamiento.
                    @else
                        El sistema intentó registrar tu usuario automáticamente en CogniFit, pero no se pudo completar.
                    @endif
                </p>

                @unless (filled($user->cognifit_user_token))
                    <x-alert type="warning">
                        {{ $cognifitError ?: 'Solicita al administrador revisar las credenciales de CogniFit.' }}
                    </x-alert>
                @endunless
            </article>
        </div>
    </section>

    <section class="section">
        <div class="section-header">
            <div>
                <span class="eyebrow">Módulos</span>
                <h2>Selecciona un entrenamiento.</h2>
            </div>
            <p class="section-copy">
                Cada módulo se abre con tu credencial CogniFit para conservar la trazabilidad individual.
            </p>
        </div>

        <div class="grid-3">
            @foreach ($availableGames as $game)
                <x-training-card :game="$game" :user="$user" />
            @endforeach
        </div>
    </section>
@endsection
