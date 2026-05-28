@props([
    'label',
    'value',
])

<article {{ $attributes->merge(['class' => 'metric-card']) }}>
    <span>{{ $label }}</span>
    <strong>{{ $value }}</strong>
    <p>{{ $slot }}</p>
</article>
