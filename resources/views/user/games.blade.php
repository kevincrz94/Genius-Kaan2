@extends('layouts.public')

@section('content')
    @php
        $moduleCount = count($availableGames);
        $skillCount = count($skillFilters);
        $credentialReady = filled($user->cognifit_user_token);
        $identitySummary = collect([
            $user->rank,
            $user->badge_number ? 'ID ' . $user->badge_number : null,
            $user->securityUnit?->name,
        ])->filter()->implode(' · ');
    @endphp

    <section class="section">
        <div class="section-header">
            <div>
                <span class="eyebrow">Simuladores</span>
                <h2>Simuladores disponibles.</h2>
                <p class="section-copy">Filtra el catálogo por capacidad cognitiva y abre cada módulo con tu sesión individual.</p>
            </div>
            <a href="{{ route('user.profile') }}" class="btn btn-secondary">Ver perfil</a>
        </div>

        <div class="dashboard simulator-hero-grid">
            <article class="panel card simulator-welcome-card">
                <span class="eyebrow">Operador activo</span>
                <h2 class="simulator-operator-name">{{ $user->name }}</h2>

                @if ($identitySummary !== '')
                    <p class="key-copy">{{ $identitySummary }}</p>
                @endif

                <div class="simulator-status-row">
                    <span class="profile-status">
                        {{ $credentialReady ? 'Acceso CogniFit listo' : 'Acceso CogniFit pendiente' }}
                    </span>
                </div>

                <p class="section-copy simulator-copy">
                    {{ $credentialReady
                        ? 'Tu credencial está activa. Puedes iniciar cualquier simulador del catálogo y conservar la trazabilidad de resultados.'
                        : 'Tu credencial todavía no está lista. Puedes revisar el catálogo, pero el inicio de módulos quedará bloqueado hasta que el administrador complete la activación.' }}
                </p>
            </article>

            <article class="panel card simulator-overview-card">
                <span class="eyebrow">Panel operativo</span>
                <h2>Tu panel operativo.</h2>
                <p class="section-copy">Resumen rápido del catálogo disponible para esta cuenta.</p>

                <div class="simulator-overview-grid">
                    <div class="simulator-overview-item">
                        <span>Módulos</span>
                        <strong>{{ $moduleCount }}</strong>
                    </div>
                    <div class="simulator-overview-item">
                        <span>Capacidades</span>
                        <strong>{{ $skillCount }}</strong>
                    </div>
                    <div class="simulator-overview-item">
                        <span>Acceso</span>
                        <strong>{{ $credentialReady ? 'Activo' : 'Pendiente' }}</strong>
                    </div>
                </div>
            </article>
        </div>

        @unless ($credentialReady)
            <x-alert type="warning">
                {{ $cognifitError ?: 'Solicita al administrador revisar las credenciales de CogniFit.' }}
            </x-alert>
        @endunless
    </section>

    <section class="section">
        <article class="panel card simulator-filter-shell">
            <div class="simulator-filter-copy">
                <span class="eyebrow">Catálogo</span>
                <h2>Módulos listos para entrenamiento.</h2>
                <p id="trainingGridStatus" class="section-copy">
                    Mostrando {{ $moduleCount }} módulos{{ $skillCount === 1 ? ' distribuidos en 1 capacidad.' : ($skillCount > 1 ? ' distribuidos en ' . $skillCount . ' capacidades.' : ' disponibles.') }}
                </p>
            </div>

            @if (! empty($skillFilters))
                <div class="skill-filter-panel" aria-label="Filtros por capacidad cognitiva">
                    <button type="button" class="skill-filter is-active" data-skill-filter="all" data-skill-label="Todas">
                        <span class="skill-filter-icon" aria-hidden="true">
                            <span>GK</span>
                        </span>
                        <span>Todas</span>
                    </button>

                    @foreach ($skillFilters as $skill)
                        <button type="button" class="skill-filter" data-skill-filter="{{ $skill['key'] }}"
                            data-skill-label="{{ $skill['label'] }}">
                            <span class="skill-filter-icon" aria-hidden="true">
                                @if (! empty($skill['icon']))
                                    <img src="{{ $skill['icon'] }}" alt="">
                                @else
                                    <span>{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($skill['label'], 0, 1)) }}</span>
                                @endif
                            </span>
                            <span>{{ $skill['label'] }}</span>
                        </button>
                    @endforeach
                </div>
            @endif
        </article>

        <div id="no-skill-results" class="hero-note d-none">
            No hay módulos disponibles para la capacidad seleccionada.
        </div>

        <div class="grid-3 training-grid">
            @foreach ($availableGames as $game)
                <x-training-card :game="$game" :user="$user" />
            @endforeach
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const buttons = document.querySelectorAll('[data-skill-filter]');
            const cards = document.querySelectorAll('.training-card');
            const emptyState = document.getElementById('no-skill-results');
            const status = document.getElementById('trainingGridStatus');
            const totalCards = cards.length;

            function updateStatus(visibleCount, label) {
                if (!status) {
                    return;
                }

                if (!label || label === 'Todas') {
                    status.textContent = `Mostrando ${visibleCount} módulos disponibles.`;
                    return;
                }

                status.textContent = `Mostrando ${visibleCount} módulos para ${label}.`;
            }

            updateStatus(totalCards, 'Todas');

            buttons.forEach((button) => {
                button.addEventListener('click', function() {
                    const selectedSkill = this.dataset.skillFilter;
                    const selectedLabel = this.dataset.skillLabel || 'Todas';
                    let visibleCount = 0;

                    buttons.forEach((item) => item.classList.remove('is-active'));
                    this.classList.add('is-active');

                    cards.forEach((card) => {
                        let skillKeys = [];

                        try {
                            skillKeys = JSON.parse(card.dataset.skillKeys || '[]');
                        } catch (error) {
                            skillKeys = [];
                        }

                        const shouldShow = selectedSkill === 'all' || skillKeys.includes(selectedSkill);

                        card.classList.toggle('is-hidden', !shouldShow);

                        if (shouldShow) {
                            visibleCount++;
                        }
                    });

                    emptyState && emptyState.classList.toggle('d-none', visibleCount > 0);
                    updateStatus(visibleCount, selectedLabel);
                });
            });
        });
    </script>
@endpush
