@props(['placeholder' => 'Search…'])

<div {{ $attributes->class(['relative']) }}>
    <x-icon name="search" class="pointer-events-none absolute top-1/2 start-3 size-4 -translate-y-1/2 text-stone-400" />
    <input type="search" x-model="q" placeholder="{{ $placeholder }}" aria-label="{{ $placeholder }}" class="form-control h-9 ps-9">
</div>
