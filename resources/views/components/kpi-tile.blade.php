@props(['label', 'value', 'icon', 'caption' => null, 'tone' => 'brand', 'badge' => null])

@php
    [$bar, $iconColor, $valueColor] = [
        'brand' => ['bg-brand-600', 'text-brand-600', 'text-stone-900'],
        'sky' => ['bg-sky-600', 'text-sky-600', 'text-sky-800'],
        'amber' => ['bg-amber-500', 'text-amber-600', 'text-amber-700'],
        'rose' => ['bg-rose-600', 'text-rose-600', 'text-rose-700'],
        'stone' => ['bg-stone-400', 'text-stone-500', 'text-stone-900'],
    ][$tone];
@endphp

<div {{ $attributes->class(['relative overflow-hidden rounded-lg border border-stone-200 bg-white px-4 pt-4 pb-3.5']) }}>
    <span class="absolute inset-x-0 top-0 h-1 {{ $bar }}" aria-hidden="true"></span>
    <div class="flex items-center justify-between gap-2">
        <p class="flex min-w-0 items-center gap-1.5 text-[11px] font-semibold tracking-wide text-stone-500 uppercase">
            <x-icon :name="$icon" :class="'size-4 shrink-0 '.$iconColor" />
            <span class="truncate">{{ $label }}</span>
        </p>
        @if ($badge)
            <span class="shrink-0 rounded bg-amber-50 px-1.5 py-0.5 text-[10px] font-semibold text-amber-700 ring-1 ring-amber-200 ring-inset">{{ $badge }}</span>
        @endif
    </div>
    <p class="mt-2.5 text-2xl font-semibold tracking-tight tabular-nums {{ $valueColor }}">{{ $value }}</p>
    @if ($caption)
        <p class="mt-1 truncate text-xs text-stone-500">{{ $caption }}</p>
    @endif
</div>
