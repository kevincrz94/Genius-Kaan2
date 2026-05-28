@props([
    'type' => 'info',
])

<div {{ $attributes->merge(['class' => 'hero-note alert-message alert-' . $type]) }}>
    {{ $slot }}
</div>
