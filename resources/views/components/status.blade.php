@props(['kind', 'value'])

@php
    [$tone, $label] = match ($kind) {
        'season' => [
            ['planned' => 'sky', 'active' => 'green', 'harvested' => 'amber', 'ensiled' => 'violet', 'closed' => 'stone'][$value] ?? 'stone',
            \App\Models\CropSeason::statusLabel($value),
        ],
        'stage' => [
            ['not_started' => 'stone', 'in_progress' => 'sky', 'completed' => 'green'][$value] ?? 'stone',
            \App\Models\SeasonStage::statusLabel($value),
        ],
        'payment' => [
            ['unpaid' => 'red', 'partial' => 'amber', 'paid' => 'green', 'notional' => 'stone'][$value] ?? 'stone',
            __(['unpaid' => 'Unpaid', 'partial' => 'Partial', 'paid' => 'Paid', 'notional' => 'Notional'][$value] ?? ucfirst((string) $value)),
        ],
        'batch' => [
            ['filling' => 'sky', 'sealed' => 'amber', 'feeding' => 'violet', 'finished' => 'stone'][$value] ?? 'stone',
            __(ucfirst((string) $value)),
        ],
        default => ['stone', __(ucfirst((string) $value))],
    };
@endphp

<x-badge :tone="$tone" {{ $attributes }}>{{ $label }}</x-badge>
