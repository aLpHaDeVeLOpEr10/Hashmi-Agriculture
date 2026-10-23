@props(['label', 'description' => null, 'checked' => false, 'name'])

<label class="flex cursor-pointer items-start justify-between gap-4 py-4" x-data="{ on: @js($checked) }">
    <span class="min-w-0">
        <span class="block text-sm font-medium text-stone-800">{{ $label }}</span>
        @if ($description)
            <span class="mt-0.5 block text-sm text-stone-500">{{ $description }}</span>
        @endif
    </span>
    <input type="checkbox" name="{{ $name }}" class="sr-only" x-model="on" role="switch" :aria-checked="on.toString()">
    <span :class="on ? 'bg-brand-600' : 'bg-stone-300'" class="relative mt-0.5 inline-flex h-6 w-11 shrink-0 rounded-full transition-colors ring-brand-500 ring-offset-2 [label:has(:focus-visible)_&]:ring-2" aria-hidden="true">
        <span :class="on ? 'translate-x-5' : 'translate-x-0.5'" class="mt-0.5 inline-block size-5 rounded-full bg-white shadow transition-transform"></span>
    </span>
</label>
