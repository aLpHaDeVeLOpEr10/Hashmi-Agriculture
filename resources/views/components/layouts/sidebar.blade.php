@php
    $groups = collect(config('agri.navigation'))->groupBy('group');
@endphp

<div x-data>
    <div x-cloak x-show="$store.sidebar.mobileOpen" x-transition.opacity
        x-on:click="$store.sidebar.mobileOpen = false"
        class="fixed inset-0 z-40 bg-stone-900/40 backdrop-blur-[2px] lg:hidden"></div>

    <aside
        x-on:keydown.escape.window="$store.sidebar.mobileOpen = false"
        :class="{ 'is-open': $store.sidebar.mobileOpen }"
        class="fixed inset-y-0 start-0 z-50 flex w-72 -translate-x-full flex-col border-e border-stone-200 bg-white transition-[transform,width] duration-200 rtl:translate-x-full lg:w-64 lg:translate-x-0 lg:collapsed:w-18 rtl:lg:translate-x-0 [&.is-open]:translate-x-0"
        aria-label="{{ __('Main navigation') }}">

        <div class="flex h-16 shrink-0 items-center gap-3 border-b border-stone-100 px-5 lg:collapsed:justify-center lg:collapsed:px-0">
            <a href="{{ route('dashboard') }}" class="flex min-w-0 items-center gap-3">
                <span class="grid size-9 shrink-0 place-items-center rounded-lg bg-brand-600 text-white shadow-sm">
                    <x-icon name="wheat" class="size-5" stroke="2" />
                </span>
                <span class="min-w-0 leading-tight lg:collapsed:hidden">
                    <span class="block truncate text-[15px] font-semibold text-stone-900">{{ config('agri.name') }}</span>
                    <span class="block text-xs text-stone-500">{{ __(config('agri.tagline')) }}</span>
                </span>
            </a>
            <button type="button" x-on:click="$store.sidebar.mobileOpen = false"
                class="ms-auto rounded-md p-1.5 text-stone-400 hover:bg-stone-100 hover:text-stone-600 lg:hidden" aria-label="{{ __('Close menu') }}">
                <x-icon name="x" class="size-5" />
            </button>
        </div>

        <nav class="scrollbar-thin flex-1 overflow-y-auto px-3 py-4">
            @foreach ($groups as $group => $items)
                <div @class(['mt-5' => ! $loop->first])>
                    <p class="mb-1.5 px-3 text-[11px] font-semibold tracking-wider text-stone-400 uppercase lg:collapsed:hidden">{{ __($group) }}</p>
                    @unless ($loop->first)
                        <div class="mx-3 mb-3 hidden border-t border-stone-100 lg:collapsed:block"></div>
                    @endunless
                    <ul class="space-y-0.5">
                        @foreach ($items as $item)
                            @php $active = request()->routeIs(...(array) $item['match']); @endphp
                            <li>
                                <a href="{{ route($item['route']) }}" title="{{ __($item['label']) }}"
                                    @if ($active) aria-current="page" @endif
                                    @class([
                                        'group relative flex items-center gap-3 rounded-lg px-3 py-2 text-sm transition-colors focus-visible:outline-2 focus-visible:outline-brand-500 lg:collapsed:justify-center lg:collapsed:px-0',
                                        'bg-brand-50 font-semibold text-brand-800' => $active,
                                        'font-medium text-stone-600 hover:bg-stone-100 hover:text-stone-900' => ! $active,
                                    ])>
                                    @if ($active)
                                        <span class="absolute inset-y-1.5 -start-3 w-1 rounded-e-full bg-brand-600"></span>
                                    @endif
                                    <x-icon :name="$item['icon']" @class(['size-[18px]', 'text-brand-600' => $active, 'text-stone-400 group-hover:text-stone-600' => ! $active]) />
                                    <span class="truncate lg:collapsed:sr-only">{{ __($item['label']) }}</span>
                                    @if ($item['preview'] ?? false)
                                        <span class="ms-auto rounded bg-stone-100 px-1.5 py-0.5 text-[10px] font-medium text-stone-500 lg:collapsed:hidden">{{ __('Preview') }}</span>
                                    @endif
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </nav>

        <div class="shrink-0 border-t border-stone-100 p-3">
            <div class="mb-2 flex items-center gap-3 rounded-lg px-3 py-2 lg:collapsed:hidden">
                <x-avatar :name="auth()->user()->name" class="size-8" />
                <div class="min-w-0 leading-tight">
                    <p class="truncate text-sm font-medium text-stone-800">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-stone-500">{{ \App\Models\User::roleLabel(auth()->user()->role) }}</p>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}" data-logout>
                @csrf
                <button type="submit" title="{{ __('Logout') }}"
                    class="group flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-stone-600 transition-colors hover:bg-red-50 hover:text-red-700 lg:collapsed:justify-center lg:collapsed:px-0">
                    <x-icon name="log-out" class="size-[18px] text-stone-400 group-hover:text-red-600 rtl:-scale-x-100" />
                    <span class="lg:collapsed:sr-only">{{ __('Logout') }}</span>
                </button>
            </form>
        </div>
    </aside>
</div>
