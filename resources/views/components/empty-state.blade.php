@props(['icon' => 'search-x', 'title' => 'No results found', 'description' => 'Try adjusting your search or filters.'])

<div {{ $attributes->class(['flex flex-col items-center justify-center px-6 py-12 text-center']) }}>
    <span class="grid size-12 place-items-center rounded-full bg-stone-100 text-stone-400">
        <x-icon :name="$icon" class="size-6" />
    </span>
    <p class="mt-3 text-sm font-semibold text-stone-800">{{ $title }}</p>
    <p class="mt-1 max-w-sm text-sm text-stone-500">{{ $description }}</p>
    @if ($slot->isNotEmpty())
        <div class="mt-4">{{ $slot }}</div>
    @endif
</div>
