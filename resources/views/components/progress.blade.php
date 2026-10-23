@props(['value', 'tone' => null])

@php
    $tone ??= 'brand';
    $bar = ['brand' => 'bg-brand-500', 'amber' => 'bg-amber-500', 'red' => 'bg-red-500', 'sky' => 'bg-sky-500', 'violet' => 'bg-violet-500'][$tone];
@endphp

<div {{ $attributes->class(['h-1.5 w-full overflow-hidden rounded-full bg-stone-100']) }} role="progressbar" aria-valuenow="{{ $value }}" aria-valuemin="0" aria-valuemax="100">
    <div class="h-full rounded-full {{ $bar }}" style="width: {{ min(100, max(0, $value)) }}%"></div>
</div>
