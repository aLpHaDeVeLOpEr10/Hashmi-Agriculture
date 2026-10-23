@use('App\Support\Money')

@php
    $stageChart = $focus ? [
        'type' => 'hbar', 'format' => 'currency', 'legend' => false,
        'labels' => $focus->stages->map->name()->values(),
        'datasets' => [['label' => __('Stage total'), 'data' => $focus->stages->map(fn ($s) => (float) ($s->totals?->total ?? 0))->values()]],
    ] : null;
@endphp

<x-layouts.app :title="__('Dashboard')" :breadcrumbs="[__('Dashboard')]">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm text-stone-500">{{ now()->translatedFormat('l, d F Y') }}</p>
            <h2 class="mt-1 text-xl font-semibold text-stone-900 sm:text-2xl">{{ __('Welcome, :name', ['name' => explode(' ', auth()->user()->name)[0]]) }}</h2>
            <p class="mt-1 text-sm text-stone-500">
                {{ trans_choice(':count open crop season|:count open crop seasons', $openSeasons->count()) }} · {{ __(':amount still unpaid', ['amount' => Money::format($outstanding)]) }}
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            @can('manage-seasons')
                <x-button variant="secondary" icon="plus" :href="route('seasons.create')">{{ __('Season') }}</x-button>
            @endcan
            @if ($focus && ! $focus->isClosed())
                @can('enter-expenses')
                    <x-button icon="plus" :href="route('expenses.create', $focus)">{{ __('Expense') }}</x-button>
                @endcan
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-stat-card :label="__('Open season spend')" :value="Money::format($activeSpend)" icon="sprout" :hint="trans_choice(':count season not yet closed|:count seasons not yet closed', $openSeasons->count())" />
        <x-stat-card :label="__('Spent this month')" :value="Money::format($monthSpend)" icon="calendar-days" :hint="now()->translatedFormat('F Y')" tone="sky" />
        <x-stat-card :label="__('Outstanding payables')" :value="Money::format($outstanding)" icon="receipt" :hint="__('Unpaid and partly paid bills')" tone="rose" />
        <x-stat-card :label="__('Latest silage cost')" :value="$latestSilageCost ? Money::format($latestSilageCost).' / t' : '—'" icon="warehouse" :hint="$latestSilageCost ? __('Per tonne of usable silage') : __('Appears once a silage batch is recorded')" tone="amber" />
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-3">
        <x-card :title="__('Stage totals')" :description="$focus ? $focus->name.' · '.$focus->crop->name.' · '.$focus->code : __('No open season')" class="xl:col-span-2">
            @if ($openSeasons->count() > 1)
                <x-slot:actions>
                    <form method="GET" action="{{ route('dashboard') }}">
                        <select name="season" onchange="this.form.submit()" class="form-control h-9 w-auto py-0 pe-8" aria-label="{{ __('Season') }}">
                            @foreach ($openSeasons as $season)
                                <option value="{{ $season->code }}" @selected($focus?->is($season))>{{ $season->name }} ({{ $season->code }})</option>
                            @endforeach
                        </select>
                    </form>
                </x-slot:actions>
            @endif

            @if ($focus)
                <div class="mb-4 flex flex-wrap items-baseline gap-x-6 gap-y-1">
                    <p class="text-2xl font-semibold text-stone-900 tabular-nums">{{ Money::format($focus->summary?->total_cost) }}</p>
                    <p class="text-sm text-stone-500">{{ __(':amount per acre over :acres acres', ['amount' => Money::format($focus->summary?->cost_per_acre), 'acres' => Money::number($focus->total_area_acres, 2)]) }}</p>
                    <a href="{{ route('seasons.show', $focus) }}" class="text-sm font-medium text-brand-700 hover:text-brand-800">{{ __('Open season') }} →</a>
                </div>
                <x-chart :config="$stageChart" :label="__('Bar chart of expense totals by stage')" height="h-64" />
            @else
                <x-empty-state icon="sprout" :title="__('No open crop season')" :description="__('Create a season to start recording expenses by stage.')">
                    @can('manage-seasons')
                        <x-button icon="plus" :href="route('seasons.create')">{{ __('New season') }}</x-button>
                    @endcan
                </x-empty-state>
            @endif
        </x-card>

        <x-card :title="__('Open seasons')" flush>
            <x-slot:actions>
                <a href="{{ route('seasons.index') }}" class="text-sm font-medium text-brand-700 hover:text-brand-800">{{ __('All seasons') }}</a>
            </x-slot:actions>
            <ul class="divide-y divide-stone-100">
                @forelse ($openSeasons as $season)
                    <li>
                        <a href="{{ route('seasons.show', $season) }}" class="flex items-center gap-3 px-5 py-3.5 hover:bg-stone-50">
                            <span class="grid size-9 shrink-0 place-items-center rounded-lg bg-brand-50 text-brand-700"><x-icon name="sprout" class="size-4" /></span>
                            <span class="min-w-0 flex-1">
                                <span class="block truncate text-sm font-medium text-stone-900">{{ $season->name }} · {{ $season->crop->name }}</span>
                                <span class="block text-xs text-stone-500">{{ $season->code }} · {{ Money::number($season->total_area_acres, 2) }} {{ __('acres') }}</span>
                            </span>
                            <span class="text-end">
                                <span class="block text-sm font-semibold text-stone-900 tabular-nums">{{ Money::format($season->summary?->total_cost) }}</span>
                                <x-status kind="season" :value="$season->status" class="mt-1" />
                            </span>
                        </a>
                    </li>
                @empty
                    <li class="px-5 py-6 text-sm text-stone-500">{{ __('No open seasons.') }}</li>
                @endforelse
            </ul>
        </x-card>
    </div>

    <x-card :title="__('Latest expenses')" :description="__('Last 10 entries across all seasons')" flush class="mt-6">
        @if ($recent->isEmpty())
            <x-empty-state icon="receipt" :title="__('No expenses yet')" :description="__('Expenses you record will appear here.')" />
        @else
            <x-table>
                <x-slot:head>
                    <tr>
                        <th>{{ __('Date') }}</th>
                        <th>{{ __('Description') }}</th>
                        <th>{{ __('Season / stage') }}</th>
                        <th>{{ __('Category') }}</th>
                        <th class="text-end">{{ __('Amount') }}</th>
                        <th>{{ __('Payment') }}</th>
                    </tr>
                </x-slot:head>
                @foreach ($recent as $expense)
                    <tr>
                        <td class="whitespace-nowrap">{{ $expense->expense_date->translatedFormat('d M Y') }}</td>
                        <td class="font-medium text-stone-900">{{ $expense->description }}</td>
                        <td class="whitespace-nowrap">
                            <a href="{{ route('stages.show', [$expense->season, $expense->season_stage_id]) }}" class="hover:text-brand-700">
                                <span class="block">{{ $expense->season->name }}</span>
                                <span class="block text-xs text-stone-500">{{ $expense->stage->name() }}</span>
                            </a>
                        </td>
                        <td class="whitespace-nowrap">{{ $expense->category->fullName() }}</td>
                        <td class="text-end font-medium whitespace-nowrap tabular-nums">{{ Money::format($expense->amount) }}</td>
                        <td><x-status kind="payment" :value="$expense->status?->payment_status" /></td>
                    </tr>
                @endforeach
            </x-table>
        @endif
    </x-card>
</x-layouts.app>
