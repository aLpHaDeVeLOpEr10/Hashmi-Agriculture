@props(['align' => 'right', 'width' => 'w-56'])

<div x-data="{ open: false }" x-on:keydown.escape="open = false" x-on:click.outside="open = false" class="relative">
    <div x-on:click="open = !open">
        {{ $trigger }}
    </div>

    <div x-cloak x-show="open"
        x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
        {{ $attributes->class([
            'absolute z-40 mt-2 rounded-xl border border-stone-200 bg-white p-1.5 shadow-pop',
            $width,
            'end-0 origin-top-right' => $align === 'right',
            'start-0 origin-top-left' => $align === 'left',
        ]) }}>
        {{ $slot }}
    </div>
</div>
