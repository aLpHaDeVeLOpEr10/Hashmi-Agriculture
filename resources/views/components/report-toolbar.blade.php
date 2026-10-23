@props(['description' => null])

@php
    $reports = [
        'reports.comparison' => __('Season comparison'),
        'reports.categories' => __('Category breakdown'),
        'reports.ageing' => __('Payables ageing'),
    ];
@endphp

<div class="mb-6 flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
    <div class="scrollbar-thin flex w-fit max-w-full gap-1 overflow-x-auto rounded-lg bg-stone-100 p-1">
        <a href="{{ route('reports.index') }}" @class(['shrink-0 rounded-md px-3 py-1.5 text-sm font-medium whitespace-nowrap', 'bg-white text-stone-900 shadow-xs' => request()->routeIs('reports.index', 'reports.season'), 'text-stone-500 hover:text-stone-800' => ! request()->routeIs('reports.index', 'reports.season')])>{{ __('Season summary') }}</a>
        @foreach ($reports as $route => $label)
            <a href="{{ route($route) }}" @class(['shrink-0 rounded-md px-3 py-1.5 text-sm font-medium whitespace-nowrap', 'bg-white text-stone-900 shadow-xs' => request()->routeIs($route), 'text-stone-500 hover:text-stone-800' => ! request()->routeIs($route)])>{{ $label }}</a>
        @endforeach
    </div>
    @unless (request()->routeIs('reports.index'))
        <div class="flex flex-wrap gap-2">
            <x-button variant="secondary" size="sm" icon="file-text" :href="request()->fullUrlWithQuery(['format' => 'pdf'])">PDF</x-button>
            <x-button variant="secondary" size="sm" icon="sheet" :href="request()->fullUrlWithQuery(['format' => 'xlsx'])">Excel</x-button>
        </div>
    @endunless
</div>

@if ($description)
    <p class="-mt-2 mb-6 max-w-3xl text-sm text-stone-500">{{ $description }}</p>
@endif
