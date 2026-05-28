@props([
    'game',
    'user',
])

<article {{ $attributes->merge(['class' => 'card feature-card training-card']) }}>
    @if (! empty($game['image']))
        <img class="training-card-image" src="{{ $game['image'] }}" alt="{{ $game['title'] }}">
    @endif

    <div class="accent-dot" aria-hidden="true"></div>
    <h3>{{ $game['title'] }}</h3>
    <p>{{ \Illuminate\Support\Str::words($game['focus'], 24, '...') }}</p>

    @if (! empty($game['skills']))
        <div class="badge-row">
            @foreach (array_slice($game['skills'], 0, 3) as $skill)
                <span class="soft-chip">{{ $skill }}</span>
            @endforeach
        </div>
    @endif

    <div class="cta-row">
        @if (filled($user->cognifit_user_token))
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
        @else
            <span class="soft-chip warning">Credencial pendiente de asignación</span>
        @endif
    </div>
</article>
