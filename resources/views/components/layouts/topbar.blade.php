@props(['title', 'breadcrumbs' => []])

@php $user = auth()->user(); @endphp

<header class="sticky top-0 z-30 border-b border-stone-200 bg-white/90 backdrop-blur supports-[backdrop-filter]:bg-white/80">
    <div class="mx-auto flex h-16 max-w-[1600px] items-center gap-3 px-4 sm:px-6 lg:px-8" x-data="{ searchOpen: false }">
        <button type="button" x-on:click="$store.sidebar.toggle()"
            class="-ms-1.5 rounded-lg p-2 text-stone-500 transition-colors hover:bg-stone-100 hover:text-stone-800 focus-visible:outline-2 focus-visible:outline-brand-500"
            aria-label="{{ __('Toggle sidebar') }}">
            <x-icon name="menu" class="size-5 lg:hidden" />
            <x-icon name="panel-left-close" class="hidden size-5 lg:block lg:collapsed:hidden rtl:-scale-x-100" />
            <x-icon name="panel-left-open" class="hidden size-5 lg:collapsed:block rtl:-scale-x-100" />
        </button>

        <div class="min-w-0 flex-1" x-show="!searchOpen">
            @if ($breadcrumbs)
                <nav aria-label="{{ __('Breadcrumb') }}" class="hidden sm:block">
                    <ol class="flex items-center gap-1.5 text-xs text-stone-500">
                        <li><a href="{{ route('dashboard') }}" class="hover:text-stone-800">{{ __('Home') }}</a></li>
                        @foreach ($breadcrumbs as $label => $url)
                            <li class="flex min-w-0 items-center gap-1.5">
                                <x-icon name="chevron-right" class="size-3 shrink-0 text-stone-300 rtl:-scale-x-100" />
                                @if ($loop->last)
                                    <span class="truncate text-stone-700" aria-current="page">{{ is_int($label) ? $url : $label }}</span>
                                @else
                                    <a href="{{ $url }}" class="truncate hover:text-stone-800">{{ $label }}</a>
                                @endif
                            </li>
                        @endforeach
                    </ol>
                </nav>
            @endif
            <h1 class="truncate text-base font-semibold text-stone-900 sm:text-lg">{{ $title }}</h1>
        </div>

        <div x-data="globalSearch(@js($searchItems))" x-on:click.outside="open = false"
            :class="searchOpen && 'flex! flex-1'"
            class="relative hidden md:block md:w-64 md:flex-none xl:w-80">
            <form x-on:submit.prevent="go()" class="relative w-full" role="search">
                <x-icon name="search" class="pointer-events-none absolute top-1/2 start-3 size-4 -translate-y-1/2 text-stone-400" />
                <input type="search" x-model="q" x-on:focus="open = true" x-on:input="open = true" x-on:keydown.escape="open = false; searchOpen = false"
                    placeholder="{{ __('Search seasons, pages…') }}" aria-label="{{ __('Global search') }}"
                    class="form-control h-9 border-stone-200 bg-stone-50 pe-3 ps-9 focus:bg-white">
            </form>
            <div x-cloak x-show="open && q.trim() !== ''" x-transition.opacity
                class="absolute inset-x-0 top-full z-40 mt-2 rounded-xl border border-stone-200 bg-white p-1.5 shadow-pop">
                <template x-for="item in results" :key="item.url + item.label">
                    <a :href="item.url" class="flex items-center justify-between gap-3 rounded-lg px-2.5 py-2 text-sm text-stone-700 hover:bg-stone-100">
                        <span x-text="item.label" class="truncate"></span>
                        <span x-text="item.group" class="shrink-0 text-xs text-stone-400"></span>
                    </a>
                </template>
                <p x-show="results.length === 0" class="px-2.5 py-3 text-sm text-stone-500">{{ __('No matches for') }} “<span x-text="q"></span>”</p>
            </div>
        </div>

        <div class="flex items-center gap-1 sm:gap-2" x-show="!searchOpen">
            <button type="button" x-on:click="searchOpen = true; $nextTick(() => $root.querySelector('input[type=search]').focus())"
                class="rounded-lg p-2 text-stone-500 hover:bg-stone-100 hover:text-stone-800 md:hidden" aria-label="{{ __('Open search') }}">
                <x-icon name="search" class="size-5" />
            </button>

            <div class="hidden items-center gap-2 rounded-lg border border-stone-200 px-3 py-1.5 text-sm text-stone-600 xl:flex">
                <x-icon name="calendar" class="size-4 text-stone-400" />
                <time datetime="{{ now()->toDateString() }}">{{ now()->translatedFormat('D, d M Y') }}</time>
            </div>

            <x-dropdown align="none" width="w-80 sm:w-96" class="-end-24 origin-top-right sm:end-0 rtl:origin-top-left">
                <x-slot:trigger>
                    <button type="button" class="relative rounded-lg p-2 text-stone-500 hover:bg-stone-100 hover:text-stone-800 focus-visible:outline-2 focus-visible:outline-brand-500"
                        aria-label="{{ __('Notifications') }}">
                        <x-icon name="bell" class="size-5" />
                        @if ($notifications)
                            <span class="absolute top-1.5 end-1.5 size-2 rounded-full bg-red-500 ring-2 ring-white"></span>
                        @endif
                    </button>
                </x-slot:trigger>

                <p class="px-2.5 pt-1.5 pb-2 text-sm font-semibold text-stone-900">{{ __('Notifications') }}</p>
                @forelse ($notifications as $note)
                    <a href="{{ $note['url'] }}" class="flex gap-3 rounded-lg px-2.5 py-2.5 hover:bg-stone-50">
                        <span class="grid size-8 shrink-0 place-items-center rounded-lg {{ $note['tone'] }}">
                            <x-icon :name="$note['icon']" class="size-4" />
                        </span>
                        <span class="min-w-0">
                            <span class="block text-sm font-medium text-stone-800">{{ $note['title'] }}</span>
                            <span class="block text-xs text-stone-500">{{ $note['body'] }}</span>
                        </span>
                    </a>
                @empty
                    <p class="px-2.5 pb-3 text-sm text-stone-500">{{ __('You are all caught up.') }}</p>
                @endforelse
            </x-dropdown>

            <x-dropdown width="w-60" class="rtl:origin-top-left">
                <x-slot:trigger>
                    <button type="button" class="flex items-center gap-2.5 rounded-lg p-1 hover:bg-stone-100 focus-visible:outline-2 focus-visible:outline-brand-500 sm:pe-2" aria-label="{{ __('Account menu') }}">
                        <x-avatar :name="$user->name" class="size-8" />
                        <span class="hidden text-start leading-tight lg:block">
                            <span class="block text-sm font-medium text-stone-800">{{ $user->name }}</span>
                            <span class="block text-xs text-stone-500">{{ \App\Models\User::roleLabel($user->role) }}</span>
                        </span>
                        <x-icon name="chevron-down" class="hidden size-4 text-stone-400 lg:block" />
                    </button>
                </x-slot:trigger>

                <div class="border-b border-stone-100 px-2.5 pt-1.5 pb-2.5">
                    <p class="text-sm font-medium text-stone-900">{{ $user->name }}</p>
                    <p class="truncate text-xs text-stone-500" dir="ltr">{{ \App\Support\Phone::display($user->phone) }}</p>
                </div>
                <div class="py-1">
                    @can('manage-settings')
                        <x-dropdown-item icon="settings" :href="route('settings.index')">{{ __('Settings') }}</x-dropdown-item>
                    @endcan
                    <form method="POST" action="{{ route('locale') }}">
                        @csrf
                        <input type="hidden" name="locale" value="{{ app()->getLocale() === 'ur' ? 'en' : 'ur' }}">
                        <x-dropdown-item icon="languages" type="submit">{{ app()->getLocale() === 'ur' ? 'English' : 'اردو' }}</x-dropdown-item>
                    </form>
                </div>
                <form method="POST" action="{{ route('logout') }}" data-logout class="border-t border-stone-100 pt-1">
                    @csrf
                    <x-dropdown-item icon="log-out" type="submit" danger>{{ __('Logout') }}</x-dropdown-item>
                </form>
            </x-dropdown>
        </div>

        <button type="button" x-cloak x-show="searchOpen" x-on:click="searchOpen = false" class="rounded-lg p-2 text-stone-500 hover:bg-stone-100 md:hidden" aria-label="{{ __('Close search') }}">
            <x-icon name="x" class="size-5" />
        </button>
    </div>
</header>
