@use('App\Support\Money')

@php
    $summary = $season->summary;
    $stageChart = [
        'type' => 'bar', 'format' => 'currency', 'legend' => false,
        'labels' => $season->stages->map->name()->values(),
        'datasets' => [['label' => __('Stage total'), 'data' => $season->stages->map(fn ($s) => (float) ($s->totals?->total ?? 0))->values()]],
    ];
@endphp

<x-layouts.app :title="__('Season summary')" :breadcrumbs="[__('Reports') => route('reports.index'), $season->code]">
    <x-report-toolbar />

    <section class="rounded-xl border border-stone-200 bg-white p-5 shadow-card sm:p-6">
        <div class="flex flex-wrap items-center gap-2">
            <h2 class="text-xl font-semibold text-stone-900">{{ $season->name }} · {{ $season->crop->name }}</h2>
            <x-status kind="season" :value="$season->status" />
        </div>
        <p class="mt-1 text-sm text-stone-500">{{ $season->code }} · {{ $season->start_date->translatedFormat('d M Y') }} @if ($season->closed_at) → {{ __('closed :date', ['date' => $season->closed_at->translatedFormat('d M Y')]) }} @endif · {{ Money::number($season->total_area_acres, 2) }} {{ __('acres') }}</p>

        <dl class="mt-6 grid grid-cols-2 gap-x-6 gap-y-5 border-t border-stone-100 pt-5 md:grid-cols-4">
            @foreach ([
                [__('Season total'), Money::format($summary->total_cost)],
                [__('Cost per acre'), Money::format($summary->cost_per_acre)],
                [__('Paid / outstanding'), Money::number($summary->paid).' / '.Money::number($summary->outstanding)],
                [__('Yield per acre'), $summary->yield_kg_per_acre ? Money::number($summary->yield_kg_per_acre).' kg' : '—'],
                [__('Harvested'), $summary->harvested_kg > 0 ? Money::number($summary->harvested_kg / 1000, 1).' t' : '—'],
                [__('Cost per tonne harvested'), $summary->cost_per_tonne_harvested ? Money::format($summary->cost_per_tonne_harvested) : '—'],
                [__('Usable silage'), $summary->silage_usable_kg > 0 ? Money::number($summary->silage_usable_kg / 1000, 1).' t' : '—'],
                [__('Silage cost per tonne'), $summary->silage_cost_per_tonne ? Money::format($summary->silage_cost_per_tonne).' ('.Money::number($summary->silage_cost_per_kg, 2).'/kg)' : '—'],
            ] as [$label, $value])
                <div>
                    <dt class="text-xs text-stone-500">{{ $label }}</dt>
                    <dd class="mt-1 font-semibold text-stone-900 tabular-nums">{{ $value }}</dd>
                </div>
            @endforeach
        </dl>
    </section>

    <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-2">
        <x-card :title="__('Cost by stage')">
            <x-chart :config="$stageChart" :label="__('Bar chart of cost by stage')" height="h-64" />
        </x-card>
        <x-card :title="__('Cost by category')">
            @foreach ($categories as $category)
                <div @class(['mt-3' => ! $loop->first])>
                    <div class="mb-1 flex justify-between gap-3 text-sm">
                        <span class="text-stone-800">{{ __($category->name) }}</span>
                        <span class="text-stone-500 tabular-nums"><span class="font-medium text-stone-800">{{ Money::number($category->total) }}</span> · {{ $category->share }}%</span>
                    </div>
                    <x-progress :value="$category->share" />
                </div>
            @endforeach
        </x-card>
    </div>

    <x-card :title="__('All expenses')" :description="trans_choice(':count line|:count lines', $expenses->count())" flush class="mt-6">
        <x-table min-width="min-w-[900px]">
            <x-slot:head>
                <tr>
                    <th>{{ __('Date') }}</th>
                    <th>{{ __('Stage') }}</th>
                    <th>{{ __('Category') }}</th>
                    <th>{{ __('Description') }}</th>
                    <th>{{ __('Vendor') }}</th>
                    <th class="text-end">{{ __('Amount') }}</th>
                    <th>{{ __('Payment') }}</th>
                </tr>
            </x-slot:head>
            @foreach ($expenses as $expense)
                <tr>
                    <td class="whitespace-nowrap">{{ $expense->expense_date->translatedFormat('d M Y') }}</td>
                    <td class="whitespace-nowrap">{{ $expense->stage->name() }}</td>
                    <td class="whitespace-nowrap">{{ __($expense->category->name) }}</td>
                    <td class="text-stone-900">{{ $expense->description }}</td>
                    <td class="whitespace-nowrap">{{ $expense->vendor?->name ?? '—' }}</td>
                    <td class="text-end font-medium whitespace-nowrap tabular-nums">{{ Money::number($expense->amount) }}</td>
                    <td><x-status kind="payment" :value="$expense->status?->payment_status" /></td>
                </tr>
            @endforeach
        </x-table>
    </x-card>
</x-layouts.app>
