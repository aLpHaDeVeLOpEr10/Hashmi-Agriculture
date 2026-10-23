@use('App\Support\Money')

<x-pdf-layout :title="__('Category breakdown')" :subtitle="($from || $to) ? __('Expenses dated :from – :to', ['from' => $from ?? '…', 'to' => $to ?? '…']) : null">
    <table>
        <tr>
            <th>{{ __('Category') }}</th>
            @foreach ($seasons as $season)
                <th class="num">{{ $season->code }}</th>
            @endforeach
            <th class="num">{{ __('Total') }}</th>
        </tr>
        @foreach ($rows as $row)
            <tr>
                <td>{{ __($row['category']) }}</td>
                @foreach ($seasons as $season)
                    <td class="num">{{ Money::number($row['values'][$season->id]) }}</td>
                @endforeach
                <td class="num">{{ Money::number($row['total']) }}</td>
            </tr>
        @endforeach
        <tr class="total">
            <td>{{ __('Total') }}</td>
            @foreach ($seasons as $season)
                <td class="num">{{ Money::number($totals[$season->id]) }}</td>
            @endforeach
            <td class="num">{{ Money::number(array_sum($totals)) }}</td>
        </tr>
    </table>
</x-pdf-layout>
