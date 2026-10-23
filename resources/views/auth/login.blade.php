<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ur' ? 'rtl' : 'ltr' }}" class="h-full">
<head>
    <x-layouts.head :title="__('Sign in')" />
</head>
<body class="h-full bg-white">
    <div class="grid min-h-full lg:grid-cols-2">
        <div class="relative hidden overflow-hidden bg-brand-900 lg:flex lg:flex-col lg:justify-between lg:p-12">
            <svg class="absolute inset-0 h-full w-full text-brand-800/60" aria-hidden="true">
                <defs>
                    <pattern id="rows" width="56" height="56" patternUnits="userSpaceOnUse" patternTransform="rotate(-24)">
                        <path d="M0 28h56" stroke="currentColor" stroke-width="10" />
                    </pattern>
                </defs>
                <rect width="100%" height="100%" fill="url(#rows)" />
            </svg>
            <div class="relative flex items-center gap-3 text-white">
                <span class="grid size-10 place-items-center rounded-lg bg-white/10 ring-1 ring-white/20"><x-icon name="wheat" class="size-6" stroke="2" /></span>
                <span class="text-lg font-semibold">{{ config('agri.name') }}</span>
            </div>
            <div class="relative max-w-md">
                <p class="text-3xl leading-tight font-semibold text-white">{{ __('Every rupee from land preparation to sealed silage.') }}</p>
                <p class="mt-4 text-brand-100/80">{{ __('Record crop season expenses by stage, track what is still unpaid, and see the true cost per tonne of silage.') }}</p>
            </div>
        </div>

        <div class="flex flex-col justify-center px-6 py-12 sm:px-12">
            <div class="mx-auto w-full max-w-sm">
                <form method="POST" action="{{ route('locale') }}" class="mb-8 flex justify-end">
                    @csrf
                    <input type="hidden" name="locale" value="{{ app()->getLocale() === 'ur' ? 'en' : 'ur' }}">
                    <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg border border-stone-200 px-3 py-1.5 text-sm text-stone-600 hover:bg-stone-50">
                        <x-icon name="languages" class="size-4" /> {{ app()->getLocale() === 'ur' ? 'English' : 'اردو' }}
                    </button>
                </form>
                <div class="flex items-center gap-3 lg:hidden">
                    <span class="grid size-10 place-items-center rounded-lg bg-brand-600 text-white"><x-icon name="wheat" class="size-6" stroke="2" /></span>
                    <span class="text-lg font-semibold text-stone-900">{{ config('agri.name') }}</span>
                </div>
                <h1 class="mt-8 text-2xl font-semibold text-stone-900 lg:mt-0">{{ __('Sign in to your farm') }}</h1>
                <p class="mt-2 text-sm text-stone-500">{{ __('Use the mobile number registered by your farm owner.') }}</p>

                <form method="POST" action="{{ url('/login') }}" class="mt-8 space-y-5" novalidate x-data="validatedForm" x-on:submit="submit($event)">
                    @csrf
                    <x-form.input :label="__('Mobile number')" name="phone" type="tel" required autocomplete="username" inputmode="tel" placeholder="0300 1234567" :value="old('phone')" dir="ltr" autofocus />
                    <div x-data="{ show: false }">
                        <x-form.input :label="__('Password')" name="password" type="password" required autocomplete="current-password" ::type="show ? 'text' : 'password'" />
                        <label class="mt-2 inline-flex items-center gap-2 text-sm text-stone-600">
                            <input type="checkbox" x-model="show" class="size-4 rounded border-stone-300 text-brand-600 focus:ring-brand-500"> {{ __('Show password') }}
                        </label>
                    </div>
                    <label class="inline-flex items-center gap-2 text-sm text-stone-600">
                        <input type="checkbox" name="remember" value="1" class="size-4 rounded border-stone-300 text-brand-600 focus:ring-brand-500"> {{ __('Keep me signed in on this device') }}
                    </label>
                    <x-button type="submit" class="w-full" icon="log-in" x-bind:disabled="busy">{{ __('Sign in') }}</x-button>
                </form>
                <p class="mt-8 text-xs text-stone-500">{{ __('Forgot your password? Ask the farm owner to reset it from Settings → Users.') }}</p>
            </div>
        </div>
    </div>
</body>
</html>
