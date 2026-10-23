@props(['icon' => null, 'href' => null, 'danger' => false])

@php
    $classes = 'flex w-full items-center gap-2.5 rounded-lg px-2.5 py-2 text-start text-sm transition-colors focus-visible:outline-none '
        .($danger ? 'text-red-600 hover:bg-red-50 focus-visible:bg-red-50' : 'text-stone-700 hover:bg-stone-100 focus-visible:bg-stone-100');
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->class($classes) }}>
        @if ($icon) <x-icon :name="$icon" class="size-4 {{ $danger ? 'text-red-500' : 'text-stone-400' }}" /> @endif
        {{ $slot }}
    </a>
@else
    <button type="{{ $attributes->get('type', 'button') }}" {{ $attributes->except('type')->class($classes) }}>
        @if ($icon) <x-icon :name="$icon" class="size-4 {{ $danger ? 'text-red-500' : 'text-stone-400' }}" /> @endif
        {{ $slot }}
    </button>
@endif
