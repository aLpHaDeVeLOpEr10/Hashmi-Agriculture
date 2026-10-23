@props(['title', 'breadcrumbs' => [], 'preview' => false])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ur' ? 'rtl' : 'ltr' }}" class="h-full">
<head>
    <x-layouts.head :title="$title" />
</head>
<body class="h-full">
    <div class="min-h-full">
        <x-layouts.sidebar />

        <div class="flex min-h-screen flex-col transition-[padding] duration-200 lg:ps-64 lg:collapsed:ps-18">
            <x-layouts.topbar :title="$title" :breadcrumbs="$breadcrumbs" />

            <main x-data class="mx-auto w-full max-w-[1600px] flex-1 px-4 py-6 sm:px-6 lg:px-8">
                <x-offline-status />

                @if ($preview)
                    <div class="mb-6 flex items-start gap-3 rounded-xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-900">
                        <x-icon name="info" class="mt-0.5 size-5 shrink-0 text-sky-600" />
                        <p><span class="font-semibold">{{ __('Upcoming module preview.') }}</span> {{ __('This page shows sample data only. It will be connected to the shared farm core when the module is built.') }}</p>
                    </div>
                @endif

                @if ($errors->has('season') || $errors->has('expense'))
                    <div class="mb-6 flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
                        <x-icon name="circle-alert" class="mt-0.5 size-5 shrink-0 text-red-600" />
                        <p>{{ $errors->first('season') ?: $errors->first('expense') }}</p>
                    </div>
                @endif

                {{ $slot }}
            </main>

            <footer class="mx-auto w-full max-w-[1600px] px-4 pb-6 text-xs text-stone-400 sm:px-6 lg:px-8">
                © {{ now()->year }} {{ config('agri.name') }}
            </footer>
        </div>

        <x-toasts />
    </div>

    @if (session('status'))
        <div x-data x-init="$store.toast.show(@js(session('status')))"></div>
    @endif
</body>
</html>
