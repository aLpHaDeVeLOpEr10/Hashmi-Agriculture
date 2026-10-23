@use('App\Support\Money')

<x-pdf-layout :title="__('Season comparison')" :subtitle="__('Change measured against :code', ['code' => $seasons->first()?->code])">
    <table>
        <tr>
            <th>{{ __('Measure') }}</th>
            @foreach ($seasons as $season)
                <th class="num">{{ $season->name }}<br><span class="muted">{{ $season->code }}</span></th>
            @endforeach
        </tr>
        @foreach ($rows as $row)
            <tr>
                <td>{{ $row['label'] }}</td>
                @foreach ($row['values'] as $i => $value)
                    <td class="num">
                        {{ $value === null ? '—' : Money::number($value) }}
                        @if ($i > 0 && $row['change'][$i] !== null)
                            <br><span class="{{ $row['change'][$i] > 0 ? 'up' : 'down' }}">{{ $row['change'][$i] > 0 ? '+' : '' }}{{ $row['change'][$i] }}%</span>
                        @endif
                    </td>
                @endforeach
            </tr>
        @endforeach
    </table>
</x-pdf-layout>
