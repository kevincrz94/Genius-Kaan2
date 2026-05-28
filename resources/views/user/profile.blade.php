@extends('layouts.public')

@section('content')
    @php
        $image = $user->image;
        $imagePath = $image ? public_path('UserImages/' . $image) : null;
        $avatar = $imagePath && file_exists($imagePath) ? asset('UserImages/' . $image) : asset('common/favicon.png');
    @endphp

    <section class="section">
        <div class="section-header">
            <div>
                <span class="eyebrow">Perfil operativo</span>
                <h2>{{ $user->name }}</h2>
            </div>
            <a href="{{ route('user.games') }}" class="btn btn-secondary">Volver a simuladores</a>
        </div>

        <div class="dashboard">
            <article class="panel card profile-summary-card">
                <img class="profile-avatar-large" src="{{ $avatar }}" alt="Foto de {{ $user->name }}">
                <h2>{{ $user->name }}</h2>
                <p class="section-copy">{{ $user->email }}</p>
                <div class="profile-status">
                    {{ filled($user->onboarding_completed_at) ? 'Perfil verificado' : 'Verificación pendiente' }}
                </div>
            </article>

            <article class="panel card">
                <span class="eyebrow">Datos personales</span>
                <h2>Información registrada.</h2>

                <div class="stack-list">
                    <div class="line-item">
                        <div>
                            <strong>Edad</strong>
                            <p>{{ $user->age ?: 'Sin edad registrada' }}</p>
                        </div>
                    </div>
                    <div class="line-item">
                        <div>
                            <strong>Sexo</strong>
                            <p>
                                @switch($user->gender)
                                    @case('male')
                                        Masculino
                                    @break

                                    @case('female')
                                        Femenino
                                    @break

                                    @case('other')
                                        Otro
                                    @break

                                    @default
                                        Sin dato registrado
                                @endswitch
                            </p>
                        </div>
                    </div>
                    <div class="line-item">
                        <div>
                            <strong>Correo</strong>
                            <p>{{ $user->email }}</p>
                        </div>
                    </div>
                </div>
            </article>
        </div>
    </section>

    <section class="section">
        <div class="section-header">
            <div>
                <span class="eyebrow">Estadísticas</span>
                <h2>Resumen de entrenamiento.</h2>
            </div>
        </div>

        <div class="profile-stats-grid">
            <article class="profile-stat-card">
                <span>Sesiones completadas</span>
                <strong>{{ $stats['completed_sessions'] }}</strong>
            </article>
            <article class="profile-stat-card">
                <span>Tiempo acumulado</span>
                <strong>{{ $stats['total_minutes'] }} min</strong>
            </article>
            <article class="profile-stat-card">
                <span>Puntaje promedio</span>
                <strong>{{ $stats['average_score'] ?: '0' }}/100</strong>
            </article>
            <article class="profile-stat-card">
                <span>Última sesión</span>
                <strong>{{ $stats['last_session_at'] ?: 'Sin registro' }}</strong>
            </article>
        </div>

        <div class="dashboard profile-metrics-dashboard">
            <article class="panel card">
                <span class="eyebrow">Capacidades medidas</span>
                <h2>Últimos puntajes.</h2>

                @forelse ($skillStats as $skill)
                    <div class="score-row profile-score-row">
                        <div>
                            <span>{{ $skill['name'] }}</span>
                            <strong>{{ $skill['score'] }}/100</strong>
                        </div>
                        <div class="progress-bar" role="progressbar" aria-valuenow="{{ $skill['score'] }}"
                            aria-valuemin="0" aria-valuemax="100">
                            <span style="width: {{ max(0, min(100, $skill['score'])) }}%"></span>
                        </div>
                    </div>
                @empty
                    <p class="section-copy">Aún no hay puntajes sincronizados.</p>
                @endforelse
            </article>

            <article class="panel card">
                <span class="eyebrow">Historial reciente</span>
                <h2>Últimos simuladores.</h2>

                <div class="stack-list">
                    @forelse ($latestSessions as $session)
                        <div class="line-item">
                            <div>
                                <strong>{{ $session->game_key ?: 'Simulador cognitivo' }}</strong>
                                <p>
                                    {{ optional($session->completed_at)->format('d/m/Y H:i') ?: 'Sin fecha' }}
                                    · {{ $session->duration_minutes }} min
                                    @if (filled($session->score))
                                        · {{ round((float) $session->score, 1) }}/100
                                    @endif
                                </p>
                            </div>
                        </div>
                    @empty
                        <p class="section-copy">Sin sesiones completadas todavía.</p>
                    @endforelse
                </div>
            </article>
        </div>
    </section>

    <section class="section">
        <div class="dashboard">
            <article class="panel card">
                <span class="eyebrow">Asignación institucional</span>
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
                            <strong>Unidad</strong>
                            <p>{{ $user->securityUnit?->name ?: 'Sin unidad' }}</p>
                        </div>
                    </div>
                    <div class="line-item">
                        <div>
                            <strong>Grupo operativo</strong>
                            <p>{{ $user->operationalGroup?->name ?: 'Sin grupo' }}</p>
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

            <article class="panel card">
                <span class="eyebrow">Áreas de refuerzo</span>
                <h2>Capacidades seleccionadas.</h2>

                @if ($attentionAreas)
                    <div class="profile-chip-list">
                        @foreach ($attentionAreas as $area)
                            <span>{{ $area }}</span>
                        @endforeach
                    </div>
                @else
                    <p class="section-copy">Sin áreas de atención seleccionadas.</p>
                @endif
            </article>
        </div>
    </section>
@endsection
