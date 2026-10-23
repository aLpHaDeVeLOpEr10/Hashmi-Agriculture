@props(['title', 'subtitle' => null])

<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        @page { margin: 28px 32px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #292524; }
        h1 { font-size: 16px; margin: 0; color: #1e4d35; }
        h2 { font-size: 12px; margin: 18px 0 6px; color: #1c1917; }
        .muted { color: #78716c; }
        .header { border-bottom: 2px solid #2f855a; padding-bottom: 8px; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; font-size: 9px; text-transform: uppercase; color: #57534e; background: #f5f5f4; padding: 5px 6px; border-bottom: 1px solid #d6d3d1; }
        td { padding: 5px 6px; border-bottom: 1px solid #e7e5e4; vertical-align: top; }
        .num { text-align: right; white-space: nowrap; }
        .total td { font-weight: bold; background: #f5f5f4; }
        .kpis td { border: 1px solid #e7e5e4; width: 25%; }
        .kpis .label { font-size: 8px; color: #78716c; text-transform: uppercase; }
        .kpis .value { font-size: 12px; font-weight: bold; margin-top: 2px; }
        .up { color: #b91c1c; } .down { color: #15803d; }
        .footer { position: fixed; bottom: -14px; left: 0; right: 0; font-size: 8px; color: #a8a29e; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $title }}</h1>
        <div class="muted">{{ config('agri.name') }}@if ($subtitle) · {{ $subtitle }}@endif · {{ __('Generated :date', ['date' => now()->format('d M Y H:i')]) }}</div>
    </div>
    {{ $slot }}
    <div class="footer">{{ __('Amounts in :currency', ['currency' => config('agri.currency')]) }}</div>
</body>
</html>
