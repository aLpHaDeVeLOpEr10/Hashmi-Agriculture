@props(['all', 'selected', 'min' => 1])

@php $chosen = $selected->pluck('code')->all(); @endphp

<form method="GET" class="mb-6 rounded-xl border border-stone-200 bg-white p-4 shadow-card" x-data="{ count: {{ count($chosen) }} }" x-on:change="count = $el.querySelectorAll('input[name=\'seasons[]\']:checked').length">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <fieldset class="min-w-0">
            <legend class="mb-2 text-sm font-medium text-stone-700">{{ __('Seasons') }}</legend>
            <div class="flex flex-wrap gap-2">
                @foreach ($all as $season)
                    <label class="cursor-pointer">
                        <input type="checkbox" name="seasons[]" value="{{ $season->code }}" @checked(in_array($season->code, $chosen, true)) class="peer sr-only">
                        <span class="inline-flex items-center gap-1.5 rounded-full border border-stone-300 px-3 py-1.5 text-sm text-stone-600 transition-colors peer-checked:border-brand-500 peer-checked:bg-brand-50 peer-checked:text-brand-800 peer-focus-visible:outline-2 peer-focus-visible:outline-brand-500 hover:border-stone-400">
                            {{ $season->name }} <span class="text-xs text-stone-400">{{ $season->code }}</span>
                        </span>
                    </label>
                @endforeach
            </div>
        </fieldset>
        <div class="flex flex-wrap items-end gap-3">
            {{ $slot }}
            <x-button type="submit" icon="refresh-cw" x-bind:disabled="count < {{ $min }}">{{ __('Update') }}</x-button>
        </div>
    </div>
    @if ($min > 1)
        <p class="mt-2 text-xs text-stone-500" x-show="count < {{ $min }}">{{ __('Choose at least :n seasons.', ['n' => $min]) }}</p>
    @endif
</form>
