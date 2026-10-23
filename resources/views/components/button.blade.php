@props(['variant' => 'primary', 'size' => 'md', 'icon' => null, 'href' => null, 'type' => 'button'])

@php
    $classes = [
        'inline-flex items-center justify-center gap-2 rounded-lg font-medium whitespace-nowrap transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 disabled:pointer-events-none disabled:opacity-50',
        match ($size) {
            'sm' => 'h-8 px-3 text-xs',
            'icon' => 'size-9',
            default => 'h-9 px-3.5 text-sm',
        },
        match ($variant) {
            'secondary' => 'border border-stone-300 bg-white text-stone-700 shadow-xs hover:bg-stone-50 hover:text-stone-900 focus-visible:outline-brand-500',
            'ghost' => 'text-stone-600 hover:bg-stone-100 hover:text-stone-900 focus-visible:outline-brand-500',
            'soft' => 'bg-brand-50 text-brand-700 hover:bg-brand-100 focus-visible:outline-brand-500',
            'danger' => 'bg-red-600 text-white shadow-xs hover:bg-red-700 focus-visible:outline-red-600',
            default => 'bg-brand-600 text-white shadow-xs hover:bg-brand-700 focus-visible:outline-brand-600',
        },
    ];
    $iconClass = $size === 'sm' ? 'size-3.5' : 'size-4';
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->class($classes) }}>
        @if ($icon) <x-icon :name="$icon" :class="$iconClass" stroke="2" /> @endif
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->class($classes) }}>
        @if ($icon) <x-icon :name="$icon" :class="$iconClass" stroke="2" /> @endif
        {{ $slot }}
    </button>
@endif
