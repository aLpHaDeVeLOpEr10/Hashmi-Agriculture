@props(['title' => null, 'description' => null, 'flush' => false])

<section {{ $attributes->class(['flex min-w-0 flex-col rounded-xl border border-stone-200 bg-white shadow-card']) }}>
    @if ($title || isset($actions))
        <header class="flex flex-wrap items-start justify-between gap-x-4 gap-y-2 border-b border-stone-100 px-5 py-4">
            <div class="min-w-0">
                @if ($title)
                    <h2 class="text-[15px] font-semibold text-stone-900">{{ $title }}</h2>
                @endif
                @if ($description)
                    <p class="mt-0.5 text-sm text-stone-500">{{ $description }}</p>
                @endif
            </div>
            @isset($actions)
                <div class="flex flex-wrap items-center gap-2 max-sm:w-full">{{ $actions }}</div>
            @endisset
        </header>
    @endif

    <div @class(['flex-1', 'p-5' => ! $flush])>
        {{ $slot }}
    </div>

    @isset($footer)
        <footer class="border-t border-stone-100 px-5 py-3 text-sm">{{ $footer }}</footer>
    @endisset
</section>
