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

        @if (! empty($skillFilters))
            <div class="skill-filter-panel card" aria-label="Filtros por capacidad cognitiva">
                <button type="button" class="skill-filter is-active" data-skill-filter="all">
                    <span class="skill-filter-icon" aria-hidden="true">
                        <span>GK</span>
                    </span>
                    <span>Todas</span>
                </button>

                @foreach ($skillFilters as $skill)
                    <button type="button" class="skill-filter" data-skill-filter="{{ $skill['key'] }}">
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

        <div id="no-skill-results" class="hero-note d-none">
            No hay mÃ³dulos disponibles para la capacidad seleccionada.
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

            buttons.forEach((button) => {
                button.addEventListener('click', function() {
                    const selectedSkill = this.dataset.skillFilter;
                    let visibleCount = 0;

                    buttons.forEach((item) => item.classList.remove('is-active'));
                    this.classList.add('is-active');

                    cards.forEach((card) => {
                        const skillKeys = (card.dataset.skillKeys || '').split(' ').filter(Boolean);
                        const shouldShow = selectedSkill === 'all' || skillKeys.includes(selectedSkill);

                        card.classList.toggle('is-hidden', !shouldShow);

                        if (shouldShow) {
                            visibleCount++;
                        }
                    });

                    emptyState && emptyState.classList.toggle('d-none', visibleCount > 0);
                });
            });
        });
    </script>
@endpush
