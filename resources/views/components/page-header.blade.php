@props(['description' => null])

<div {{ $attributes->class(['mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between']) }}>
    <p class="max-w-2xl text-sm text-stone-500">{{ $description }}</p>
    @isset($actions)
        <div class="flex flex-wrap items-center gap-2">{{ $actions }}</div>
    @endisset
</div>
