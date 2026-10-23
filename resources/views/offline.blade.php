<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ur' ? 'rtl' : 'ltr' }}" class="h-full">
<head>
    <x-layouts.head :title="__('Offline')" />
</head>
<body class="grid h-full place-items-center bg-stone-50 px-6">
    <div class="max-w-sm text-center">
        <span class="mx-auto grid size-14 place-items-center rounded-2xl bg-brand-600 text-white"><x-icon name="wifi-off" class="size-7" stroke="2" /></span>
        <h1 class="mt-5 text-xl font-semibold text-stone-900">{{ __('You are offline') }}</h1>
        <p class="mt-2 text-sm text-stone-600">{{ __('This page has not been opened on this phone before, so it is not available without a connection.') }}</p>
        <p class="mt-2 text-sm text-stone-600">{{ __('Pages you opened recently still work, including Add expense for a season. Expenses saved offline sync when you are back online.') }}</p>
        <button type="button" onclick="history.back()" class="mt-6 inline-flex h-10 items-center rounded-lg bg-brand-600 px-4 text-sm font-medium text-white hover:bg-brand-700">{{ __('Go back') }}</button>
    </div>
</body>
</html>
