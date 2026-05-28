@props([
    'label',
    'value',
    'display' => null,
])

@php
    $score = max(0, min(100, (int) $value));
    $displayValue = $display ?? $score . '/100';
@endphp

<div class="score-row">
    <div>
        <span>{{ $label }}</span>
        <strong>{{ $displayValue }}</strong>
    </div>
    <div
        class="progress-bar"
        role="progressbar"
        aria-label="{{ $label }}"
        aria-valuenow="{{ $score }}"
        aria-valuemin="0"
        aria-valuemax="100"
    >
        <span style="width: {{ $score }}%"></span>
    </div>
</div>
