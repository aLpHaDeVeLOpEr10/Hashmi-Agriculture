@props(['model', 'options', 'counts' => []])

<div {{ $attributes->class(['scrollbar-thin flex w-fit max-w-full gap-1 overflow-x-auto rounded-lg bg-stone-100 p-1']) }} role="tablist">
    @foreach ($options as $value => $label)
        <button type="button" role="tab"
            x-on:click="{{ $model }} = @js($value)"
            :aria-selected="{{ $model }} === @js($value)"
            :class="{{ $model }} === @js($value) ? 'bg-white text-stone-900 shadow-xs' : 'text-stone-500 hover:text-stone-800'"
            class="inline-flex shrink-0 items-center gap-1.5 rounded-md px-3 py-1.5 text-sm font-medium whitespace-nowrap transition-colors focus-visible:outline-2 focus-visible:outline-brand-500">
            {{ $label }}
            @isset($counts[$value])
                <span class="rounded-full bg-stone-200/70 px-1.5 text-xs text-stone-600 tabular-nums">{{ $counts[$value] }}</span>
            @endisset
        </button>
    @endforeach
</div>
