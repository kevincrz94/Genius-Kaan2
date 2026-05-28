@props([
    'title',
    'accentColor' => 'var(--accent)',
])

<article {{ $attributes->merge(['class' => 'card feature-card']) }} style="--card-accent: {{ $accentColor }}">
    <div class="accent-dot" aria-hidden="true"></div>
    <h3>{{ $title }}</h3>
    <p>{{ $slot }}</p>
</article>
