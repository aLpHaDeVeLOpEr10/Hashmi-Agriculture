@props(['label', 'value', 'icon', 'change' => null, 'period' => 'vs last month', 'invert' => false, 'trend' => [], 'hint' => null, 'tone' => 'brand'])

@php
    $positive = $change !== null && $change >= 0;
    $good = $invert ? ! $positive : $positive;
    $iconTone = [
        'brand' => 'bg-brand-50 text-brand-700',
        'amber' => 'bg-amber-50 text-amber-700',
        'sky' => 'bg-sky-50 text-sky-700',
        'violet' => 'bg-violet-50 text-violet-700',
        'rose' => 'bg-rose-50 text-rose-700',
        'teal' => 'bg-teal-50 text-teal-700',
    ][$tone];
    $stroke = [
        'brand' => '#2f855a', 'amber' => '#c27c0e', 'sky' => '#3182ce', 'violet' => '#805ad5', 'rose' => '#b7472a', 'teal' => '#0d9488',
    ][$tone];

    $points = '';
    if (count($trend) > 1) {
        $min = min($trend);
        $range = max(max($trend) - $min, 1);
        $step = 100 / (count($trend) - 1);
        $points = collect($trend)->map(fn ($v, $i) => round($i * $step, 2).','.round(28 - (($v - $min) / $range) * 24, 2))->implode(' ');
    }
@endphp

<div {{ $attributes->class(['rounded-xl border border-stone-200 bg-white p-5 shadow-card']) }}>
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
            <p class="text-sm font-medium text-stone-500">{{ $label }}</p>
            <p class="mt-2 text-2xl font-semibold tracking-tight text-stone-900 tabular-nums">{{ $value }}</p>
        </div>
        <span class="grid size-10 shrink-0 place-items-center rounded-lg {{ $iconTone }}">
            <x-icon :name="$icon" class="size-5" />
        </span>
    </div>

    <div class="mt-4 flex items-end justify-between gap-3">
        <div class="min-w-0 text-xs">
            @if ($change !== null)
                <span @class([
                    'inline-flex items-center gap-0.5 rounded-md px-1.5 py-0.5 font-semibold',
                    'bg-brand-50 text-brand-700' => $good,
                    'bg-red-50 text-red-700' => ! $good,
                ])>
                    <x-icon :name="$positive ? 'trending-up' : 'trending-down'" class="size-3.5" stroke="2" />
                    {{ $positive ? '+' : '' }}{{ $change }}%
                </span>
                <span class="ms-1 text-stone-500">{{ $period }}</span>
            @elseif ($hint)
                <span class="text-stone-500">{{ $hint }}</span>
            @endif
        </div>
        @if ($points)
            <svg viewBox="0 0 100 30" preserveAspectRatio="none" class="h-8 w-20 shrink-0 overflow-visible" aria-hidden="true">
                <polyline points="{{ $points }}" fill="none" stroke="{{ $stroke }}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" vector-effect="non-scaling-stroke" />
            </svg>
        @endif
    </div>
</div>
