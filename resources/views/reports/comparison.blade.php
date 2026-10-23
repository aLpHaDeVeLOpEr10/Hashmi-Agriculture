@use('App\Support\Money')

@php
    $base = $seasons->first();
    $chart = [
        'type' => 'bar', 'format' => 'currency',
        'labels' => $templates->map(fn ($t) => __($t->name))->values(),
        'datasets' => $seasons->take(6)->values()->map(fn ($s, $i) => [
            'label' => $s->name,
            'data' => $templates->map(fn ($t) => (float) ($s->stages->firstWhere('stage_template_id', $t->id)?->totals?->total ?? 0))->values(),
        ]),
    ];
    $perUnit = ['cost_per_acre', 'cost_per_tonne_harvested', 'silage_cost_per_tonne'];
@endphp

<x-layouts.app :title="__('Season comparison')" :breadcrumbs="[__('Reports') => route('reports.index'), __('Season comparison')]">
    <x-report-toolbar :description="__('Totals, stage costs and unit costs side by side. Change is measured against the earliest season selected.')" />

    <x-season-picker :all="$all" :selected="$seasons" :min="2" />

    @if ($seasons->count() < 2)
        <div class="rounded-xl border border-dashed border-stone-300 bg-white">
            <x-empty-state icon="git-compare" :title="__('Pick two or more seasons')" :description="__('A comparison needs at least two seasons.')" />
        </div>
    @else
        <x-card :title="__('Comparison')" flush>
            <x-table min-width="min-w-[720px]">
                <x-slot:head>
                    <tr>
                        <th>{{ __('Measure') }}</th>
                        @foreach ($seasons as $season)
                            <th class="text-end">
                                {{ $season->name }}
                                <span class="block font-mono text-[10px] font-normal tracking-normal text-stone-400 normal-case">{{ $season->code }} · {{ $season->crop->name }}</span>
                            </th>
                        @endforeach
                    </tr>
                </x-slot:head>
                @foreach ($rows as $row)
                    @if ($row['key'] === 'stage' && ($rows[$loop->index - 1]['key'] ?? null) !== 'stage')
                        <tr class="bg-stone-50/70">
                            <td colspan="{{ $seasons->count() + 1 }}" class="text-xs font-semibold tracking-wide text-stone-500 uppercase">{{ __('Stage costs') }}</td>
                        </tr>
                    @endif
                    <tr>
                        <td @class(['whitespace-nowrap', 'font-medium text-stone-900' => $row['key'] !== 'stage', 'ps-8 text-stone-600' => $row['key'] === 'stage'])>{{ $row['label'] }}</td>
                        @foreach ($row['values'] as $i => $value)
                            <td class="text-end whitespace-nowrap tabular-nums">
                                @if ($value === null)
                                    <span class="text-stone-400">—</span>
                                @else
                                    <span @class(['font-semibold text-stone-900' => in_array($row['key'], ['total_cost', ...$perUnit], true)])>{{ $row['key'] === 'yield_kg_per_acre' ? Money::number($value).' kg' : Money::number($value) }}</span>
                                    @if ($i > 0 && $row['change'][$i] !== null)
                                        @php
                                            $up = $row['change'][$i] > 0;
                                            $good = $row['key'] === 'yield_kg_per_acre' ? $up : ! $up;
                                        @endphp
                                        <span @class(['ms-1 inline-block rounded px-1 text-xs font-medium', 'bg-brand-50 text-brand-700' => $good && $row['change'][$i] != 0, 'bg-red-50 text-red-700' => ! $good && $row['change'][$i] != 0, 'bg-stone-100 text-stone-500' => $row['change'][$i] == 0])>
                                            {{ $up ? '+' : '' }}{{ $row['change'][$i] }}%
                                        </span>
                                    @endif
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </x-table>
            <x-slot:footer>
                <p class="text-xs text-stone-500">{{ __('Amounts in :currency. Green means cheaper (or higher yield) than :base.', ['currency' => config('agri.currency'), 'base' => $base->name]) }}</p>
            </x-slot:footer>
        </x-card>

        <x-card :title="__('Stage costs by season')" class="mt-6">
            <x-chart :config="$chart" :label="__('Grouped bar chart of stage costs for each selected season')" height="h-80" />
            @if ($seasons->count() > 6)
                <p class="mt-2 text-xs text-stone-500">{{ __('The chart shows the first six seasons; the table shows all.') }}</p>
            @endif
        </x-card>
    @endif
</x-layouts.app>
