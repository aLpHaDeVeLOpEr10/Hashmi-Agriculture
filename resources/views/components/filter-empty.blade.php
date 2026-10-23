@props(['title' => 'No matching records', 'description' => 'Try a different search term or clear the filters.'])

<div x-cloak x-show="visible === 0">
    <x-empty-state :title="$title" :description="$description">
        <x-button variant="secondary" size="sm" icon="rotate-ccw" x-on:click="reset()">Clear filters</x-button>
    </x-empty-state>
</div>
