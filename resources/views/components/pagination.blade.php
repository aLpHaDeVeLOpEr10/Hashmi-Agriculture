@props(['paginator'])

@php
    /** @var \Illuminate\Pagination\LengthAwarePaginator $paginator */
    $pageName = $paginator->getPageName();
    $current = $paginator->currentPage();
    $last = $paginator->lastPage();

    // Page numbers to show: first, last, and a window around the current page, with gaps marked by null.
    $pages = collect([1, $last, $current - 1, $current, $current + 1])
        ->filter(fn ($n) => $n >= 1 && $n <= $last)->unique()->sort()->values();
    $links = [];
    foreach ($pages as $i => $n) {
        if ($i > 0 && $n - $pages[$i - 1] > 1) {
            $links[] = null;
        }
        $links[] = $n;
    }

    // Keep every other filter when the page size changes; restart this table at page 1.
    $keep = collect(explode('&', http_build_query(request()->except(['per_page', $pageName]))))
        ->filter()->map(fn ($pair) => array_map('urldecode', array_pad(explode('=', $pair, 2), 2, '')));
    $fragment = $paginator->fragment();
    $action = $paginator->path().($fragment ? '#'.$fragment : '');
@endphp

@if ($paginator->total() > \App\Support\Pagination::SIZES[0])
    <nav {{ $attributes->class(['flex flex-wrap items-center justify-between gap-3 border-t border-stone-100 px-5 py-3 text-sm']) }} aria-label="{{ __('Page navigation') }}">
        <div class="flex flex-wrap items-center gap-x-4 gap-y-2 text-stone-600">
            <span class="tabular-nums">{{ __('Showing :from–:to of :total', ['from' => $paginator->firstItem() ?? 0, 'to' => $paginator->lastItem() ?? 0, 'total' => $paginator->total()]) }}</span>
            <form method="GET" action="{{ $action }}" class="flex items-center gap-2">
                @foreach ($keep as [$name, $value])
                    <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                @endforeach
                <label class="text-stone-500" for="per-page-{{ $pageName }}">{{ __('Rows per page') }}</label>
                <select id="per-page-{{ $pageName }}" name="per_page" onchange="this.form.submit()" class="form-control h-8 w-auto py-0 pe-8 text-sm">
                    @foreach (\App\Support\Pagination::SIZES as $size)
                        <option value="{{ $size }}" @selected($paginator->perPage() === $size)>{{ $size }}</option>
                    @endforeach
                </select>
                <noscript><button type="submit" class="rounded-md border border-stone-300 px-2 py-1">{{ __('Apply') }}</button></noscript>
            </form>
        </div>

        @if ($last > 1)
            <div class="flex items-center gap-1">
                @if ($paginator->onFirstPage())
                    <span class="inline-flex h-8 items-center gap-1 rounded-md px-2.5 text-stone-300" aria-disabled="true"><x-icon name="chevron-left" class="size-4 rtl:rotate-180" /> {{ __('Previous') }}</span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex h-8 items-center gap-1 rounded-md px-2.5 text-stone-700 hover:bg-stone-100"><x-icon name="chevron-left" class="size-4 rtl:rotate-180" /> {{ __('Previous') }}</a>
                @endif

                <span class="hidden items-center gap-1 sm:flex">
                    @foreach ($links as $n)
                        @if ($n === null)
                            <span class="px-1 text-stone-400">…</span>
                        @elseif ($n === $current)
                            <span class="grid h-8 min-w-8 place-items-center rounded-md bg-brand-600 px-2 font-semibold text-white tabular-nums" aria-current="page">{{ $n }}</span>
                        @else
                            <a href="{{ $paginator->url($n) }}" class="grid h-8 min-w-8 place-items-center rounded-md px-2 text-stone-700 tabular-nums hover:bg-stone-100" aria-label="{{ __('Page :page', ['page' => $n]) }}">{{ $n }}</a>
                        @endif
                    @endforeach
                </span>
                <span class="px-2 text-stone-500 tabular-nums sm:hidden">{{ $current }} / {{ $last }}</span>

                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex h-8 items-center gap-1 rounded-md px-2.5 text-stone-700 hover:bg-stone-100">{{ __('Next') }} <x-icon name="chevron-right" class="size-4 rtl:rotate-180" /></a>
                @else
                    <span class="inline-flex h-8 items-center gap-1 rounded-md px-2.5 text-stone-300" aria-disabled="true">{{ __('Next') }} <x-icon name="chevron-right" class="size-4 rtl:rotate-180" /></span>
                @endif
            </div>
        @endif
    </nav>
@endif
