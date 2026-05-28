@extends('layouts.public')

@section('content')
    @php
        $image = $user->image;
        $imagePath = $image ? public_path('UserImages/' . $image) : null;
        $avatar = $imagePath && file_exists($imagePath) ? asset('UserImages/' . $image) : asset('common/favicon.png');
        $genderLabel = match ($user->gender) {
            'male' => 'Masculino',
            'female' => 'Femenino',
            'other' => 'Otro',
            default => 'Sin dato registrado',
        };
        $identitySummary = collect([
            $user->rank,
            $user->badge_number ? 'ID ' . $user->badge_number : null,
            $user->securityUnit?->name,
        ])->filter()->implode(' · ');
        $personalFacts = [
            [
                'label' => 'Correo institucional',
                'value' => $user->email,
            ],
            [
                'label' => 'Edad',
                'value' => $user->age ?: 'Sin edad registrada',
            ],
            [
                'label' => 'Sexo',
                'value' => $genderLabel,
            ],
        ];
        $operationalFacts = [
            [
                'label' => 'Placa / ID',
                'value' => $user->badge_number ?: 'Sin placa registrada',
            ],
            [
                'label' => 'Rango / cargo',
                'value' => $user->rank ?: 'Sin rango registrado',
            ],
            [
                'label' => 'Unidad',
                'value' => $user->securityUnit?->name ?: 'Sin unidad',
            ],
            [
                'label' => 'Grupo operativo',
                'value' => $user->operationalGroup?->name ?: 'Sin grupo',
            ],
            [
                'label' => 'Área asignada',
                'value' => $user->assignment_area ?: 'Sin área asignada',
            ],
            [
                'label' => 'Último registro',
                'value' => $stats['last_session_at'] ?: 'Sin registro',
            ],
        ];
    @endphp

    <section class="section">
        <div class="section-header">
            <div>
                <span class="eyebrow">Perfil operativo</span>
                <h2 class="profile-overview-title">Resumen del perfil.</h2>
                <p class="section-copy">Consulta tus datos, avance cognitivo y asignación operativa.</p>
            </div>
            <a href="{{ route('user.games') }}" class="btn btn-secondary">Volver a simuladores</a>
        </div>

        <div class="dashboard profile-hero-grid">
            <article class="panel card profile-summary-card">
                <div class="profile-summary-head">
                    <img class="profile-avatar-large" src="{{ $avatar }}" alt="Foto de {{ $user->name }}">

                    <div class="profile-summary-copy">
                        <h2 class="profile-summary-name">{{ $user->name }}</h2>

                        @if ($identitySummary !== '')
                            <p class="key-copy">{{ $identitySummary }}</p>
                        @endif

                        <div class="profile-status">
                            {{ filled($user->onboarding_completed_at) ? 'Perfil verificado' : 'Verificación pendiente' }}
                        </div>
                    </div>
                </div>

                <p class="section-copy profile-summary-note">
                    {{ filled($user->onboarding_completed_at)
                        ? 'Tu cuenta está lista para entrenamiento, seguimiento y evaluación cognitiva.'
                        : 'Completa tu verificación para mantener el seguimiento operativo al día.' }}
                </p>

                <div class="profile-summary-meta">
                    @foreach ($personalFacts as $fact)
                        <div class="profile-meta-chip">
                            <span>{{ $fact['label'] }}</span>
                            <strong>{{ $fact['value'] }}</strong>
                        </div>
                    @endforeach
                </div>
            </article>

            <article class="panel card profile-context-card">
                <span class="eyebrow">Ficha operativa</span>
                <h2>Identidad y asignación.</h2>
                <p class="section-copy">Estos datos controlan la trazabilidad institucional de tu actividad.</p>

                <div class="profile-key-grid">
                    @foreach ($operationalFacts as $fact)
                        <div class="profile-key-item">
                            <span>{{ $fact['label'] }}</span>
                            <strong>{{ $fact['value'] }}</strong>
                        </div>
                    @endforeach
                </div>
            </article>
        </div>
    </section>

    <section class="section">
        <div class="section-header">
            <div>
                <span class="eyebrow">Actividad cognitiva</span>
                <h2>Rendimiento reciente.</h2>
                <p class="section-copy">Indicadores consolidados y sesiones registradas para este perfil.</p>
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
                <span>Último registro</span>
                <strong>{{ $stats['last_session_at'] ?: 'Sin registro' }}</strong>
            </article>
        </div>

        <div class="dashboard profile-metrics-dashboard">
            <article class="panel card profile-skill-card">
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

            <article class="panel card profile-history-card">
                <span class="eyebrow">Historial reciente</span>
                <h2>Últimos simuladores.</h2>

                @if ($latestSessions->isNotEmpty())
                    <div class="profile-history-list">
                        @foreach ($latestSessions as $session)
                            <div class="profile-history-item">
                                <div class="profile-history-copy">
                                    <strong>{{ $session->game_key ?: 'Simulador cognitivo' }}</strong>
                                    <p>
                                        {{ optional($session->completed_at)->format('d/m/Y H:i') ?: 'Sin fecha' }}
                                        · {{ $session->duration_minutes }} min
                                    </p>
                                </div>

                                @if (filled($session->score))
                                    <span class="profile-history-badge">{{ round((float) $session->score, 1) }}/100</span>
                                @else
                                    <span class="profile-history-badge profile-history-badge-muted">Sin puntaje</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="section-copy">Sin sesiones completadas todavía.</p>
                @endif
            </article>
        </div>
    </section>

    <section class="section">
        <div class="dashboard profile-support-dashboard">
            <article class="panel card">
                <span class="eyebrow">Áreas de refuerzo</span>
                <h2>Capacidades seleccionadas.</h2>
                <p class="section-copy">Estas áreas guían la recomendación de módulos y el seguimiento cognitivo.</p>

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

            <article class="panel card profile-next-step-card">
                <span class="eyebrow">Siguiente paso</span>
                <h2>Continúa tu entrenamiento.</h2>
                <p class="section-copy">Vuelve al catálogo de simuladores para iniciar una nueva sesión con tu perfil actual.</p>
                <div class="cta-row">
                    <a href="{{ route('user.games') }}" class="btn btn-primary">Ir a simuladores</a>
                </div>
            </article>
        </div>
    </section>
@endsection
