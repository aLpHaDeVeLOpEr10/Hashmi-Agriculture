@use('App\Support\Money')

@php $summary = $season->summary; @endphp

<x-pdf-layout :title="__('Season summary').' · '.$season->name" :subtitle="$season->code.' · '.$season->crop->name.' · '.\App\Models\CropSeason::statusLabel($season->status)">
    <table class="kpis">
        <tr>
            <td><div class="label">{{ __('Season total') }}</div><div class="value">{{ Money::format($summary->total_cost) }}</div></td>
            <td><div class="label">{{ __('Cost per acre') }}</div><div class="value">{{ Money::format($summary->cost_per_acre) }}</div><div class="muted">{{ Money::number($season->total_area_acres, 2) }} {{ __('acres') }}</div></td>
            <td><div class="label">{{ __('Cost per tonne harvested') }}</div><div class="value">{{ $summary->cost_per_tonne_harvested ? Money::format($summary->cost_per_tonne_harvested) : '—' }}</div><div class="muted">{{ Money::number($summary->harvested_kg / 1000, 1) }} t</div></td>
            <td><div class="label">{{ __('Silage cost per tonne') }}</div><div class="value">{{ $summary->silage_cost_per_tonne ? Money::format($summary->silage_cost_per_tonne) : '—' }}</div><div class="muted">{{ Money::number($summary->silage_usable_kg / 1000, 1) }} t {{ __('usable') }}</div></td>
        </tr>
    </table>

    <h2>{{ __('Stages') }}</h2>
    <table>
        <tr><th>{{ __('Stage') }}</th><th class="num">{{ __('Total') }}</th><th class="num">{{ __('Share') }}</th><th class="num">{{ __('Paid') }}</th><th class="num">{{ __('Balance') }}</th></tr>
        @foreach ($season->stages as $stage)
            <tr>
                <td>{{ $stage->name() }}</td>
                <td class="num">{{ Money::number($stage->totals?->total) }}</td>
                <td class="num">{{ $summary->total_cost > 0 ? round($stage->totals?->total / $summary->total_cost * 100, 1) : 0 }}%</td>
                <td class="num">{{ Money::number($stage->totals?->paid) }}</td>
                <td class="num">{{ Money::number($stage->totals?->balance) }}</td>
            </tr>
        @endforeach
        <tr class="total"><td>{{ __('Season total') }}</td><td class="num">{{ Money::number($summary->total_cost) }}</td><td class="num">100%</td><td class="num">{{ Money::number($summary->paid) }}</td><td class="num">{{ Money::number($summary->outstanding) }}</td></tr>
    </table>

    <h2>{{ __('Cost by category') }}</h2>
    <table>
        <tr><th>{{ __('Category') }}</th><th class="num">{{ __('Total') }}</th><th class="num">{{ __('Share') }}</th></tr>
        @foreach ($categories as $category)
            <tr><td>{{ __($category->name) }}</td><td class="num">{{ Money::number($category->total) }}</td><td class="num">{{ $category->share }}%</td></tr>
        @endforeach
    </table>

    <h2>{{ __('Expenses') }}</h2>
    <table>
        <tr><th>{{ __('Date') }}</th><th>{{ __('Stage') }}</th><th>{{ __('Category') }}</th><th>{{ __('Description') }}</th><th>{{ __('Vendor') }}</th><th class="num">{{ __('Amount') }}</th><th>{{ __('Payment') }}</th></tr>
        @foreach ($expenses as $expense)
            <tr>
                <td>{{ $expense->expense_date->format('d M Y') }}</td>
                <td>{{ $expense->stage->name() }}</td>
                <td>{{ __($expense->category->name) }}</td>
                <td>{{ $expense->description }}</td>
                <td>{{ $expense->vendor?->name }}</td>
                <td class="num">{{ Money::number($expense->amount) }}</td>
                <td>{{ __(ucfirst((string) $expense->status?->payment_status)) }}</td>
            </tr>
        @endforeach
    </table>
</x-pdf-layout>
