@props(['config', 'label', 'height' => 'h-72'])

<div {{ $attributes->class(['relative w-full', $height]) }}>
    <canvas data-chart="{{ json_encode($config) }}" role="img" aria-label="{{ $label }}"></canvas>
</div>
