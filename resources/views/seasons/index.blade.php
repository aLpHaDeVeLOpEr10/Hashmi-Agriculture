@use('App\Support\Money')
@use('App\Models\CropSeason')

<x-layouts.app :title="__('Crop Seasons')" :breadcrumbs="[__('Crop Seasons')]">
    <x-page-header :description="__('Each season groups its expenses by stage, from land preparation to silage production.')">
        <x-slot:actions>
            @can('manage-seasons')
                <x-button icon="plus" :href="route('seasons.create')">{{ __('New season') }}</x-button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <form method="GET" class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="scrollbar-thin flex w-fit max-w-full gap-1 overflow-x-auto rounded-lg bg-stone-100 p-1">
            @foreach (['' => __('All')] + collect(CropSeason::STATUSES)->mapWithKeys(fn ($s) => [$s => CropSeason::statusLabel($s)])->all() as $value => $label)
                <a href="{{ route('seasons.index', array_filter(['status' => $value, 'year' => $filters['year'] ?? null])) }}"
                    @class([
                        'shrink-0 rounded-md px-3 py-1.5 text-sm font-medium whitespace-nowrap transition-colors',
                        'bg-white text-stone-900 shadow-xs' => ($filters['status'] ?? '') === $value,
                        'text-stone-500 hover:text-stone-800' => ($filters['status'] ?? '') !== $value,
                    ])>{{ $label }}</a>
            @endforeach
        </div>
        @if ($years->count() > 1)
            <div class="flex items-center gap-2">
                <input type="hidden" name="status" value="{{ $filters['status'] ?? '' }}">
                <select name="year" onchange="this.form.submit()" class="form-control h-9 w-auto py-0 pe-8" aria-label="{{ __('Year') }}">
                    <option value="">{{ __('All years') }}</option>
                    @foreach ($years as $year)
                        <option value="{{ $year }}" @selected(($filters['year'] ?? null) == $year)>{{ $year }}</option>
                    @endforeach
                </select>
            </div>
        @endif
    </form>

    @if ($seasons->isEmpty())
        <div class="rounded-xl border border-dashed border-stone-300 bg-white">
            <x-empty-state icon="sprout" :title="__('No seasons found')" :description="__('Create a season to start recording expenses, or change the filters.')">
                @can('manage-seasons')
                    <x-button icon="plus" :href="route('seasons.create')">{{ __('New season') }}</x-button>
                @endcan
            </x-empty-state>
        </div>
    @else
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 2xl:grid-cols-3">
            @foreach ($seasons as $season)
                <a href="{{ route('seasons.show', $season) }}" class="group flex flex-col rounded-xl border border-stone-200 bg-white p-5 shadow-card transition-shadow hover:shadow-pop focus-visible:outline-2 focus-visible:outline-brand-500">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="font-mono text-xs text-stone-500">{{ $season->code }}</p>
                            <h3 class="mt-0.5 truncate font-semibold text-stone-900 group-hover:text-brand-800">{{ $season->name }}</h3>
                            <p class="text-sm text-stone-500">{{ $season->crop->name }} · {{ $season->start_date->translatedFormat('d M Y') }}</p>
                        </div>
                        <x-status kind="season" :value="$season->status" />
                    </div>
                    <dl class="mt-5 grid grid-cols-2 gap-4 border-t border-stone-100 pt-4 text-sm sm:grid-cols-4">
                        <div class="col-span-2">
                            <dt class="text-xs text-stone-500">{{ __('Total cost') }}</dt>
                            <dd class="text-lg font-semibold text-stone-900 tabular-nums">{{ Money::format($season->summary?->total_cost) }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-stone-500">{{ __('Acres') }}</dt>
                            <dd class="font-medium text-stone-800 tabular-nums">{{ Money::number($season->total_area_acres, 2) }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-stone-500">{{ __('Per acre') }}</dt>
                            <dd class="font-medium text-stone-800 tabular-nums">{{ Money::number($season->summary?->cost_per_acre) }}</dd>
                        </div>
                        <div class="col-span-2">
                            <dt class="text-xs text-stone-500">{{ __('Silage') }}</dt>
                            <dd class="font-medium text-stone-800 tabular-nums">{{ $season->summary?->silage_usable_kg > 0 ? Money::number($season->summary->silage_usable_kg / 1000, 1).' t' : '—' }}</dd>
                        </div>
                        <div class="col-span-2">
                            <dt class="text-xs text-stone-500">{{ __('Cost per tonne') }}</dt>
                            <dd class="font-medium text-stone-800 tabular-nums">{{ $season->summary?->silage_cost_per_tonne ? Money::format($season->summary->silage_cost_per_tonne) : '—' }}</dd>
                        </div>
                    </dl>
                </a>
            @endforeach
        </div>
    @endif
</x-layouts.app>
