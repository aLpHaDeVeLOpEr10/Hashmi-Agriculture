@php
    $editing = $season->exists;
    $selected = old('fields', $selected);
@endphp

<x-layouts.app :title="$editing ? __('Edit season') : __('New crop season')"
    :breadcrumbs="$editing ? [__('Crop Seasons') => route('seasons.index'), $season->name => route('seasons.show', $season), __('Edit')] : [__('Crop Seasons') => route('seasons.index'), __('New season')]">

    <form method="POST" action="{{ $editing ? route('seasons.update', $season) : route('seasons.store') }}" novalidate
        x-data="validatedForm" x-on:submit="submit($event)" class="mx-auto max-w-3xl space-y-6">
        @csrf
        @if ($editing)
            @method('PUT')
        @endif

        <x-card :title="__('Season details')" :description="$editing ? __('Code :code', ['code' => $season->code]) : __('A code like CS-2026-10 is assigned from the start date, and the standard stages are created automatically.')">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <x-form.input :label="__('Season name')" name="name" required maxlength="80" :placeholder="__('e.g. October 2026')" :value="old('name', $season->name)" />
                </div>
                <x-form.select :label="__('Crop')" name="crop_id" required :options="$crops->pluck('name', 'id')->all()" :selected="old('crop_id', $season->crop_id)" />
                <div class="hidden sm:block"></div>
                <x-form.input :label="__('Start date')" name="start_date" type="date" required :value="old('start_date', $season->start_date?->toDateString())" />
                <x-form.input :label="__('Expected end date')" name="expected_end_date" type="date" :value="old('expected_end_date', $season->expected_end_date?->toDateString())" />
                <div class="sm:col-span-2">
                    <x-form.textarea :label="__('Notes')" name="notes" :placeholder="__('Variety, seed rate, anything worth remembering')">{{ old('notes', $season->notes) }}</x-form.textarea>
                </div>
            </div>
        </x-card>

        <x-card :title="__('Fields')" :description="__('Tick the fields this season covers. The acres can be less than the whole field.')" flush>
            @if ($fields->isEmpty())
                <x-empty-state icon="map" :title="__('No fields yet')" :description="__('Add your fields under Settings → Fields first.')">
                    <x-button variant="secondary" :href="route('settings.fields.index')">{{ __('Add fields') }}</x-button>
                </x-empty-state>
            @else
                <ul class="divide-y divide-stone-100" x-data="{ total: 0, recalc() { this.total = [...$el.querySelectorAll('input[data-area]')].reduce((sum, i) => sum + (i.disabled ? 0 : (parseFloat(i.value) || 0)), 0) } }" x-init="recalc()" x-on:input="recalc()" x-on:change="recalc()">
                    @foreach ($fields as $field)
                        @php $area = $selected[$field->id] ?? null; @endphp
                        <li class="flex flex-wrap items-center gap-3 px-5 py-3" x-data="{ on: @js(filled($area)) }">
                            <label class="flex min-w-0 flex-1 items-center gap-3">
                                <input type="checkbox" x-model="on" x-on:change="if (on && !$refs.area.value) $refs.area.value = @js((float) $field->area_acres)"
                                    class="size-4 rounded border-stone-300 text-brand-600 focus:ring-brand-500">
                                <span class="min-w-0">
                                    <span class="block truncate text-sm font-medium text-stone-900">{{ $field->name }}</span>
                                    <span class="block text-xs text-stone-500">{{ __(':acres acres in total', ['acres' => \App\Support\Money::number($field->area_acres, 2)]) }}</span>
                                </span>
                            </label>
                            <div class="relative w-36">
                                <input type="number" name="fields[{{ $field->id }}]" x-ref="area" data-area step="0.01" min="0.01" max="{{ $field->area_acres }}" :disabled="!on" :required="on"
                                    value="{{ $area }}" aria-label="{{ __('Acres for :field', ['field' => $field->name]) }}" class="form-control pe-14 disabled:bg-stone-50 disabled:text-stone-400">
                                <span class="pointer-events-none absolute inset-y-0 end-3 flex items-center text-sm text-stone-400">{{ __('acres') }}</span>
                            </div>
                        </li>
                    @endforeach
                    <li class="flex items-center justify-between bg-stone-50/70 px-5 py-3 text-sm">
                        <span class="font-medium text-stone-700">{{ __('Total area') }}</span>
                        <span class="font-semibold text-stone-900 tabular-nums"><span x-text="total.toFixed(2)"></span> {{ __('acres') }}</span>
                    </li>
                </ul>
            @endif
            @error('fields')
                <p class="px-5 pb-4 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </x-card>

        <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
            <x-button variant="secondary" :href="$editing ? route('seasons.show', $season) : route('seasons.index')">{{ __('Cancel') }}</x-button>
            <x-button type="submit" icon="check" x-bind:disabled="busy">{{ $editing ? __('Save changes') : __('Create season') }}</x-button>
        </div>
    </form>
</x-layouts.app>
