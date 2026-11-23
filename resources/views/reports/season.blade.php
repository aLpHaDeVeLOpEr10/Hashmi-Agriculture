@use('App\Support\Money')

@php
    $stageChart = [
        'type' => 'bar', 'format' => 'currency', 'legend' => false,
        'labels' => $season->stages->map->name()->values(),
        'datasets' => [['label' => __('Stage total'), 'data' => $season->stages->map(fn ($s) => (float) ($s->totals?->total ?? 0))->values()]],
    ];
@endphp

<x-layouts.app :title="__('Season summary')" :breadcrumbs="[__('Reports') => route('reports.index'), $season->code]">
    <x-report-toolbar />

    <section class="rounded-xl border border-stone-200 bg-white p-5 shadow-card sm:p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <h2 class="text-xl font-semibold text-stone-900">{{ $season->name }} · {{ $season->crop->name }}</h2>
                    <x-status kind="season" :value="$season->status" />
                </div>
                <p class="mt-1 text-sm text-stone-500">{{ $season->code }} · {{ $season->start_date->translatedFormat('d M Y') }} @if ($season->closed_at) → {{ __('closed :date', ['date' => $season->closed_at->translatedFormat('d M Y')]) }} @endif · {{ Money::number($season->total_area_acres, 2) }} {{ __('acres') }}</p>
            </div>

            <form method="GET" action="{{ route('reports.season', $season) }}" class="flex shrink-0 items-center gap-2" role="search" aria-label="{{ __('Filter by stage') }}">
                <label for="stage-filter" class="text-xs font-medium text-stone-500">{{ __('Stage') }}</label>
                <div class="relative">
                    <x-icon name="list-filter" :class="'pointer-events-none absolute start-2.5 top-1/2 size-4 -translate-y-1/2 '.($stage ? 'text-brand-600' : 'text-stone-400')" />
                    <select id="stage-filter" name="stage" onchange="this.form.submit()"
                            @class(['form-control h-9 w-full min-w-56 py-0 ps-8 pe-8 sm:w-auto', 'border-brand-600 ring-1 ring-brand-600/20' => $stage])>
                        <option value="">{{ __('All stages') }}</option>
                        @foreach ($season->stages as $option)
                            <option value="{{ $option->template->code }}" @selected($stage?->is($option))>{{ $option->name() }}</option>
                        @endforeach
                    </select>
                </div>
                @if ($stage)
                    <a href="{{ route('reports.season', $season) }}" class="inline-flex h-9 items-center gap-1 rounded-md px-2 text-sm text-stone-500 hover:bg-stone-100 hover:text-stone-800" title="{{ __('Clear filter') }}">
                        <x-icon name="x" class="size-4" /><span>{{ __('Clear') }}</span>
                    </a>
                @endif
                <noscript><x-button type="submit" variant="secondary" size="sm">{{ __('Apply') }}</x-button></noscript>
            </form>
        </div>

        @php
            $prov = $kpis->provisional ? __('Provisional') : null;
            $basis = $kpis->harvested_kg > 0 ? __('on :kg kg :basis', ['kg' => Money::number($kpis->harvested_kg), 'basis' => $kpis->provisional ? __('est.') : __('harvested')]) : __('no harvest recorded');
            $groups = [
                __('Unit costs') => [
                    ['label' => $stage ? __('Stage cost') : __('Total crop cost'), 'icon' => 'wallet', 'value' => Money::number($kpis->total), 'caption' => $stage ? $stage->name() : __('recorded expenditure')],
                    ['label' => __('Cost / acre'), 'icon' => 'map', 'value' => $kpis->cost_per_acre !== null ? Money::number($kpis->cost_per_acre) : '—', 'caption' => Money::number($season->total_area_acres, 2).' '.__('acres')],
                    ['label' => __('Cost / kg'), 'icon' => 'scale', 'value' => $kpis->cost_per_kg !== null ? Money::number($kpis->cost_per_kg, 2) : '—', 'caption' => $basis, 'badge' => $prov],
                    ['label' => __('Cost / maund'), 'icon' => 'weight', 'value' => $kpis->cost_per_maund !== null ? Money::number($kpis->cost_per_maund) : '—', 'caption' => __('1 maund = 40 kg'), 'badge' => $prov],
                    ['label' => __('Cost / tonne'), 'icon' => 'package', 'value' => $kpis->cost_per_tonne_harvested !== null ? Money::number($kpis->cost_per_tonne_harvested) : '—', 'caption' => $kpis->harvested_kg > 0 ? Money::number($kpis->harvested_kg / 1000, 1).' t '.__('harvested') : '—', 'badge' => $prov],
                ],
                __('Data quality & yield') => [
                    ['label' => __('Transactions'), 'icon' => 'receipt', 'tone' => 'sky', 'value' => Money::number($kpis->lines), 'caption' => __('recorded lines')],
                    ['label' => __('Incomplete txns'), 'icon' => 'file-warning', 'tone' => 'amber', 'value' => Money::number($kpis->incomplete_lines), 'caption' => __('Rs :amount undocumented', ['amount' => Money::number($kpis->incomplete_amount)])],
                    ['label' => __('% spend incomplete'), 'icon' => 'percent', 'tone' => 'amber', 'value' => Money::number($kpis->incomplete_share, 1).'%', 'caption' => __('documentation gap')],
                    ['label' => __('Identified re-work'), 'icon' => 'rotate-ccw', 'tone' => 'rose', 'value' => Money::number($kpis->rework_amount), 'caption' => trans_choice('avoidable re-work · :count line|avoidable re-work · :count lines', $kpis->rework_lines)],
                    $kpis->benchmark_yield_maund !== null
                        ? ['label' => __('Benchmark yield'), 'icon' => 'sprout', 'tone' => 'amber', 'value' => Money::number($kpis->benchmark_yield_maund), 'caption' => __('maund / acre').($kpis->yield_maund_per_acre !== null && ! $kpis->provisional ? ' · '.__('actual :n', ['n' => Money::number($kpis->yield_maund_per_acre)]) : '')]
                        : ['label' => __('Yield'), 'icon' => 'sprout', 'tone' => 'amber', 'value' => $kpis->yield_maund_per_acre !== null ? Money::number($kpis->yield_maund_per_acre) : '—', 'caption' => __('maund / acre'), 'badge' => $prov],
                ],
                __('Payments & silage') => [
                    ['label' => __('Paid'), 'icon' => 'circle-check', 'value' => Money::number($kpis->paid), 'caption' => $kpis->total > 0 ? Money::number($kpis->paid / $kpis->total * 100, 1).'% '.__('of cost') : '—'],
                    ['label' => __('Outstanding'), 'icon' => 'hand-coins', 'tone' => $kpis->outstanding > 0 ? 'rose' : 'stone', 'value' => Money::number($kpis->outstanding), 'caption' => __('unpaid bills')],
                    ['label' => __('Usable silage'), 'icon' => 'warehouse', 'tone' => 'stone', 'value' => $kpis->silage_usable_kg > 0 ? Money::number($kpis->silage_usable_kg / 1000, 1).' t' : '—', 'caption' => $kpis->silage_usable_kg > 0 ? __('measured from bunkers') : __('not measured yet')],
                    ['label' => __('Silage cost / tonne'), 'icon' => 'wheat', 'tone' => 'stone', 'value' => $kpis->silage_cost_per_tonne !== null ? Money::number($kpis->silage_cost_per_tonne) : '—', 'caption' => $kpis->silage_cost_per_kg !== null ? Money::number($kpis->silage_cost_per_kg, 2).' / kg' : __('pending measurement')],
                    ['label' => __('Cost / kg DM'), 'icon' => 'droplets', 'tone' => 'stone', 'value' => $kpis->cost_per_kg_dm !== null ? Money::number($kpis->cost_per_kg_dm, 2) : '—', 'caption' => __(':pct% dry matter', ['pct' => Money::quantity($kpis->dry_matter_pct)]).($kpis->silage_usable_kg > 0 ? '' : ' · '.__('on harvest')), 'badge' => $kpis->silage_usable_kg > 0 ? null : $prov],
                ],
            ];
        @endphp

        <div class="mt-6 space-y-5 border-t border-stone-100 pt-5">
            @foreach ($groups as $title => $tiles)
                <div>
                    <h3 class="mb-2.5 text-xs font-semibold tracking-wide text-stone-400 uppercase">{{ $title }}@if ($loop->first) <span class="font-normal normal-case">· {{ config('agri.currency') }}</span>@endif</h3>
                    <div @class(['grid grid-cols-1 gap-3 sm:grid-cols-2', 'lg:grid-cols-5' => count($tiles) === 5, 'lg:grid-cols-4' => count($tiles) === 4])>
                        @foreach ($tiles as $tile)
                            <x-kpi-tile :label="$tile['label']" :icon="$tile['icon']" :value="$tile['value']" :caption="$tile['caption'] ?? null" :tone="$tile['tone'] ?? 'brand'" :badge="$tile['badge'] ?? null" />
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-2">
        <x-card :title="__('Cost by stage')">
            <x-chart :config="$stageChart" :label="__('Bar chart of cost by stage')" height="h-64" />
        </x-card>
        <x-card :title="__('Cost by category')" :description="$stage?->name()">
            @if ($categories->isEmpty())
                <p class="text-sm text-stone-500">{{ __('No expenses in this stage yet.') }}</p>
            @endif
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
                    <td class="text-stone-900">
                        {{ $expense->description }}
                        @if ($expense->is_rework)
                            <span class="ms-1 inline-flex rounded bg-rose-50 px-1.5 py-0.5 text-[10px] font-semibold text-rose-700 ring-1 ring-rose-200 ring-inset">{{ __('Re-work') }}</span>
                        @endif
                        @if ($expense->completeness === 'incomplete')
                            <span class="ms-1 inline-flex rounded bg-amber-50 px-1.5 py-0.5 text-[10px] font-semibold text-amber-700 ring-1 ring-amber-200 ring-inset">{{ __('Incomplete') }}</span>
                        @endif
                    </td>
                    <td class="whitespace-nowrap">{{ $expense->vendor?->name ?? '—' }}</td>
                    <td class="text-end font-medium whitespace-nowrap tabular-nums">{{ Money::number($expense->amount) }}</td>
                    <td><x-status kind="payment" :value="$expense->status?->payment_status" /></td>
                </tr>
            @endforeach
        </x-table>
    </x-card>
</x-layouts.app>
