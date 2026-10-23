@use('App\Support\Money')
@use('App\Support\CostReport')

<x-pdf-layout :title="__('Payables ageing')" :subtitle="__('As of :date', ['date' => now()->format('d M Y')])">
    <table class="kpis">
        <tr>
            @foreach ($buckets as $bucket => $row)
                <td><div class="label">{{ $bucket }} {{ __('days') }}</div><div class="value">{{ Money::format($row['amount']) }}</div><div class="muted">{{ trans_choice(':count bill|:count bills', $row['count']) }}</div></td>
            @endforeach
        </tr>
    </table>

    <h2>{{ __('By vendor') }}</h2>
    <table>
        <tr>
            <th>{{ __('Vendor') }}</th>
            @foreach (CostReport::AGEING_BUCKETS as $bucket)
                <th class="num">{{ $bucket }}</th>
            @endforeach
            <th class="num">{{ __('Total') }}</th>
        </tr>
        @foreach ($vendors as $vendor)
            <tr>
                <td>{{ $vendor['vendor'] }}</td>
                @foreach (CostReport::AGEING_BUCKETS as $bucket)
                    <td class="num">{{ Money::number($vendor[$bucket]) }}</td>
                @endforeach
                <td class="num">{{ Money::number($vendor['total']) }}</td>
            </tr>
        @endforeach
    </table>

    <h2>{{ __('Bills') }}</h2>
    <table>
        <tr><th>{{ __('Date') }}</th><th>{{ __('Vendor') }}</th><th>{{ __('Season') }}</th><th>{{ __('Description') }}</th><th class="num">{{ __('Balance') }}</th><th class="num">{{ __('Days') }}</th></tr>
        @foreach ($bills as $bill)
            <tr>
                <td>{{ \Illuminate\Support\Carbon::parse($bill->expense_date)->format('d M Y') }}</td>
                <td>{{ $bill->vendor_name ?? '—' }}</td>
                <td>{{ $bill->season_code }}</td>
                <td>{{ $bill->description }}</td>
                <td class="num">{{ Money::number($bill->balance) }}</td>
                <td class="num">{{ $bill->age_days }}</td>
            </tr>
        @endforeach
    </table>
</x-pdf-layout>
