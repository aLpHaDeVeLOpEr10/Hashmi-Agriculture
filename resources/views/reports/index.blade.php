@use('App\Support\Money')

<x-layouts.app :title="__('Reports')" :breadcrumbs="[__('Reports')]">
    <x-report-toolbar :description="__('Open a season summary, or compare seasons, categories and unpaid bills. Every report exports to PDF and Excel.')" />

    <x-card :title="__('Season summaries')" flush>
        @if ($seasons->isEmpty())
            <x-empty-state icon="sprout" :title="__('No seasons yet')" :description="__('Reports appear once a season has expenses.')" />
        @else
            <x-table min-width="min-w-[900px]">
                <x-slot:head>
                    <tr>
                        <th>{{ __('Season') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th class="text-end">{{ __('Total cost') }}</th>
                        <th class="text-end">{{ __('Per acre') }}</th>
                        <th class="text-end">{{ __('Harvested') }}</th>
                        <th class="text-end">{{ __('Silage cost / t') }}</th>
                        <th class="text-end">{{ __('Export') }}</th>
                    </tr>
                </x-slot:head>
                @foreach ($seasons as $season)
                    <tr>
                        <td>
                            <a href="{{ route('reports.season', $season) }}" class="font-medium text-stone-900 hover:text-brand-700">{{ $season->name }} · {{ $season->crop->name }}</a>
                            <span class="block font-mono text-xs text-stone-500">{{ $season->code }}</span>
                        </td>
                        <td><x-status kind="season" :value="$season->status" /></td>
                        <td class="text-end font-semibold whitespace-nowrap tabular-nums">{{ Money::format($season->summary?->total_cost) }}</td>
                        <td class="text-end whitespace-nowrap tabular-nums">{{ Money::number($season->summary?->cost_per_acre) }}</td>
                        <td class="text-end whitespace-nowrap tabular-nums">{{ $season->summary?->harvested_kg > 0 ? Money::number($season->summary->harvested_kg / 1000, 1).' t' : '—' }}</td>
                        <td class="text-end whitespace-nowrap tabular-nums">{{ $season->summary?->silage_cost_per_tonne ? Money::number($season->summary->silage_cost_per_tonne) : '—' }}</td>
                        <td class="text-end whitespace-nowrap">
                            <a href="{{ route('reports.season', [$season, 'format' => 'pdf']) }}" class="text-sm font-medium text-brand-700 hover:text-brand-800">PDF</a>
                            <span class="text-stone-300">·</span>
                            <a href="{{ route('reports.season', [$season, 'format' => 'xlsx']) }}" class="text-sm font-medium text-brand-700 hover:text-brand-800">Excel</a>
                        </td>
                    </tr>
                @endforeach
            </x-table>
        @endif
    </x-card>
</x-layouts.app>
