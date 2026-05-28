@extends('layouts.public')

@section('content')
    <section class="section">
        <div class="section-header">
            <div>
                <span class="eyebrow">Bienvenido</span>
                <h2>{{ $user->name }}</h2>
                <p class="section-copy">Continúa tu entrenamiento cognitivo operativo.</p>
            </div>
            <a href="{{ route('user.profile') }}" class="btn btn-secondary">Ver perfil</a>
        </div>
        @unless (filled($user->cognifit_user_token))
            <x-alert type="warning">
                {{ $cognifitError ?: 'Solicita al administrador revisar las credenciales de CogniFit.' }}
            </x-alert>
        @endunless
    </section>

    <section class="section">
        <div class="section-header">
            <div>
                <span class="eyebrow">Módulos</span>
                <h2>Selecciona un simulador.</h2>
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

            buttons.forEach((button) => {
                button.addEventListener('click', function() {
                    const selectedSkill = this.dataset.skillFilter;
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
                });
            });
        });
    </script>
@endpush
