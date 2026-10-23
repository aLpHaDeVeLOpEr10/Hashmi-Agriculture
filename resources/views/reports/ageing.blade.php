@use('App\Support\Money')
@use('App\Support\CostReport')

<x-layouts.app :title="__('Payables ageing')" :breadcrumbs="[__('Reports') => route('reports.index'), __('Payables ageing')]">
    <x-report-toolbar :description="__('Unpaid balances by how long they have been outstanding, counted from the expense date.')" />

    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        @foreach ($buckets as $bucket => $row)
            <x-stat-card :label="__(':range days', ['range' => $bucket])" :value="Money::format($row['amount'])" icon="clock" :hint="trans_choice(':count bill|:count bills', $row['count'])" :tone="$bucket === '90+' ? 'rose' : ($bucket === '61–90' ? 'amber' : 'sky')" />
        @endforeach
    </div>

    <x-card :title="__('By vendor')" flush class="mt-6">
        @if ($vendors->isEmpty())
            <x-empty-state icon="circle-check" :title="__('Nothing outstanding')" :description="__('All bills are paid.')" />
        @else
            <x-table min-width="min-w-[640px]">
                <x-slot:head>
                    <tr>
                        <th>{{ __('Vendor') }}</th>
                        @foreach (CostReport::AGEING_BUCKETS as $bucket)
                            <th class="text-end">{{ $bucket }} {{ __('days') }}</th>
                        @endforeach
                        <th class="text-end">{{ __('Total') }}</th>
                    </tr>
                </x-slot:head>
                @foreach ($vendors as $vendor)
                    <tr>
                        <td class="font-medium text-stone-900">{{ $vendor['vendor'] }}</td>
                        @foreach (CostReport::AGEING_BUCKETS as $bucket)
                            <td @class(['text-end tabular-nums', 'font-medium text-red-700' => $bucket === '90+' && $vendor[$bucket] > 0])>{{ $vendor[$bucket] > 0 ? Money::number($vendor[$bucket]) : '—' }}</td>
                        @endforeach
                        <td class="text-end font-semibold text-stone-900 tabular-nums">{{ Money::number($vendor['total']) }}</td>
                    </tr>
                @endforeach
            </x-table>
        @endif
    </x-card>
</x-layouts.app>
