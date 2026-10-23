@props(['title', 'description', 'record' => [], 'modal' => 'record'])

@php
    $tabs = [
        'settings.categories.index' => [__('Categories'), 'tags'],
        'settings.vendors.index' => [__('Vendors'), 'store'],
        'settings.fields.index' => [__('Fields'), 'map'],
        'settings.crops.index' => [__('Crops'), 'sprout'],
        'settings.units.index' => [__('Units'), 'ruler'],
        'settings.stages.index' => [__('Stage templates'), 'list-ordered'],
    ];
    if (auth()->user()->can('manage-users')) {
        $tabs['settings.users.index'] = [__('Users & roles'), 'shield-check'];
    }
    $restore = old('_record') ? json_decode(old('_record'), true) + request()->old() : null;
@endphp

<x-layouts.app :title="__('Settings')" :breadcrumbs="[__('Settings') => route('settings.index'), $title]">
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-[220px_minmax(0,1fr)]"
        x-data="{ record: {{ Js::from($restore ?? $record) }}, blank: {{ Js::from($record) }} }">
        <nav class="scrollbar-thin -mx-4 flex gap-1 overflow-x-auto px-4 lg:mx-0 lg:flex-col lg:px-0" aria-label="{{ __('Settings sections') }}">
            @foreach ($tabs as $route => [$label, $icon])
                <a href="{{ route($route) }}" @class([
                    'flex shrink-0 items-center gap-2.5 rounded-lg px-3 py-2 text-sm font-medium whitespace-nowrap transition-colors',
                    'bg-white text-brand-800 shadow-xs ring-1 ring-stone-200' => request()->routeIs(str_replace('.index', '.*', $route)),
                    'text-stone-600 hover:bg-stone-100 hover:text-stone-900' => ! request()->routeIs(str_replace('.index', '.*', $route)),
                ])>
                    <x-icon :name="$icon" class="size-4" /> {{ $label }}
                </a>
            @endforeach
        </nav>

        <div class="min-w-0">
            <x-card :title="$title" :description="$description" flush>
                @isset($actions)
                    <x-slot:actions>{{ $actions }}</x-slot:actions>
                @endisset
                {{ $slot }}
            </x-card>
            {{ $after ?? '' }}
        </div>

        {{ $modals ?? '' }}

        @if ($restore)
            <div x-init="$nextTick(() => $dispatch('open-modal', @js($modal)))"></div>
        @endif
    </div>
</x-layouts.app>
