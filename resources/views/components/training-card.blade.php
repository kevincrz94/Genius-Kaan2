@props([
    'game',
    'user',
])

@php
    $credentialReady = filled($user->cognifit_user_token);
@endphp

<article {{ $attributes->merge([
    'class' => 'card feature-card training-card',
    'data-skill-keys' => json_encode(array_values($game['skill_keys'] ?? [])),
]) }}>
    @if (! empty($game['image']))
        <div class="training-card-media">
            <img class="training-card-image" src="{{ $game['image'] }}" alt="{{ $game['title'] }}">
        </div>
    @endif

    <div class="training-card-body">
        <div class="training-card-head">
            <span class="line-tag">Módulo</span>
            <span class="training-card-status {{ $credentialReady ? 'is-ready' : 'is-pending' }}">
                {{ $credentialReady ? 'Listo' : 'Pendiente' }}
            </span>
        </div>

        <h3>{{ $game['title'] }}</h3>
        <p class="training-card-copy">{{ \Illuminate\Support\Str::words($game['focus'], 24, '...') }}</p>

        <div class="training-card-meta">
            <span class="training-card-code">{{ $game['key'] }}</span>
        </div>

        @if (! empty($game['skills']))
            <div class="badge-row">
                @foreach (array_slice($game['skills'], 0, 3) as $skill)
                    <span class="soft-chip">{{ $skill }}</span>
                @endforeach
            </div>
        @else
            <p class="helper-copy training-card-helper">Entrenamiento general sin capacidades clasificadas.</p>
        @endif

        <div class="training-card-footer">
            @if ($credentialReady)
                <a class="btn btn-primary" href="{{ route('start.game', [
                    'participant' => $user->name,
                    'goal' => $game['focus'],
                    'locale' => $user->cognifit_locale ?: 'es',
                    'user_token' => $user->cognifit_user_token,
                    'game_key' => $game['key'],
                    'image' => $game['image'] ?? '',
                ]) }}">
                    Iniciar módulo
                </a>
                <span class="helper-copy training-card-helper">Abrirá tu sesión individual en CogniFit.</span>
            @else
                <span class="soft-chip warning">Credencial pendiente de asignación</span>
                <span class="helper-copy training-card-helper">Solicita al administrador activar tu acceso.</span>
            @endif
        </div>
    </div>
</article>
