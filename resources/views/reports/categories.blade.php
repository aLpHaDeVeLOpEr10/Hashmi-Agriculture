@use('App\Support\Money')

<x-layouts.app :title="__('Category breakdown')" :breadcrumbs="[__('Reports') => route('reports.index'), __('Category breakdown')]">
    <x-report-toolbar :description="__('Spending by category group for the chosen seasons, optionally limited to a date range.')" />

    <x-season-picker :all="$all" :selected="$seasons">
        <div>
            <label for="from" class="mb-1 block text-xs font-medium text-stone-600">{{ __('From') }}</label>
            <input id="from" type="date" name="from" value="{{ $from }}" class="form-control h-9 w-40">
        </div>
        <div>
            <label for="to" class="mb-1 block text-xs font-medium text-stone-600">{{ __('To') }}</label>
            <input id="to" type="date" name="to" value="{{ $to }}" class="form-control h-9 w-40">
        </div>
    </x-season-picker>

    <x-card :title="__('Spending by category')" :description="$from || $to ? __('Expenses dated :from – :to', ['from' => $from ?? '…', 'to' => $to ?? '…']) : null" flush>
        @if ($rows->isEmpty())
            <x-empty-state icon="tags" :title="__('No expenses in this selection')" :description="__('Try other seasons or a wider date range.')" />
        @else
            @php $grand = max(array_sum($totals), 0.01); @endphp
            <x-table min-width="min-w-[640px]">
                <x-slot:head>
                    <tr>
                        <th>{{ __('Category') }}</th>
                        @foreach ($seasons as $season)
                            <th class="text-end">{{ $season->code }}</th>
                        @endforeach
                        <th class="text-end">{{ __('Total') }}</th>
                        <th class="w-40">{{ __('Share') }}</th>
                    </tr>
                </x-slot:head>
                @foreach ($rows as $row)
                    <tr>
                        <td class="font-medium whitespace-nowrap text-stone-900">{{ __($row['category']) }} <span class="block text-xs font-normal text-stone-500">{{ __(ucfirst($row['cost_type'])) }}</span></td>
                        @foreach ($seasons as $season)
                            <td class="text-end whitespace-nowrap tabular-nums">{{ $row['values'][$season->id] > 0 ? Money::number($row['values'][$season->id]) : '—' }}</td>
                        @endforeach
                        <td class="text-end font-semibold whitespace-nowrap text-stone-900 tabular-nums">{{ Money::number($row['total']) }}</td>
                        <td>
                            <div class="flex items-center gap-2">
                                <x-progress :value="$row['total'] / $grand * 100" />
                                <span class="w-10 text-end text-xs text-stone-500 tabular-nums">{{ round($row['total'] / $grand * 100, 1) }}%</span>
                            </div>
                        </td>
                    </tr>
                @endforeach
                <tr class="bg-stone-50 font-semibold text-stone-900">
                    <td>{{ __('Total') }}</td>
                    @foreach ($seasons as $season)
                        <td class="text-end tabular-nums">{{ Money::number($totals[$season->id]) }}</td>
                    @endforeach
                    <td class="text-end tabular-nums">{{ Money::number(array_sum($totals)) }}</td>
                    <td></td>
                </tr>
            </x-table>
        @endif
    </x-card>
</x-layouts.app>
