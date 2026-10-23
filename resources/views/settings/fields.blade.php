<x-settings-layout :title="__('Fields')" :description="__('Plots of land. Seasons pick fields and the acres they use.')"
    :record="['name' => '', 'area_acres' => '', 'is_active' => true]">
    @can('manage-settings')
        <x-slot:actions>
            <x-button icon="plus" size="sm" x-on:click="record = { ...blank }; $dispatch('open-modal', 'record')">{{ __('Add field') }}</x-button>
        </x-slot:actions>
    @endcan

    @if ($fields->isEmpty())
        <x-empty-state icon="map" :title="__('No fields yet')" :description="__('Add the plots you cultivate.')" />
    @else
        <x-table min-width="min-w-[420px]">
            <x-slot:head>
                <tr><th>{{ __('Name') }}</th><th class="text-end">{{ __('Area') }}</th><th></th></tr>
            </x-slot:head>
            @foreach ($fields as $field)
                <tr @class(['opacity-60' => ! $field->is_active])>
                    <td class="font-medium text-stone-900">{{ $field->name }} @unless ($field->is_active)<x-badge status="Inactive" :dot="false" />@endunless</td>
                    <td class="text-end tabular-nums">{{ \App\Support\Money::number($field->area_acres, 2) }} {{ __('acres') }}</td>
                    <td class="text-end">
                        @can('manage-settings')
                            <x-button variant="ghost" size="sm" icon="pencil" x-on:click="record = {{ Js::from(['id' => $field->id, 'name' => $field->name, 'area_acres' => (float) $field->area_acres, 'is_active' => $field->is_active]) }}; $dispatch('open-modal', 'record')">{{ __('Edit') }}</x-button>
                        @endcan
                    </td>
                </tr>
            @endforeach
        </x-table>
    @endif

    <x-slot:modals>
        <x-modal name="record" :title="__('Add field')" title-bind="record.id ? {{ Js::from(__('Edit field')) }} : {{ Js::from(__('Add field')) }}" size="md"
            bind-action="record.id ? {{ Js::from(url('/settings/fields')) }} + '/' + record.id : {{ Js::from(url('/settings/fields')) }}">
        <x-slot:hidden>
            <input type="hidden" name="_method" :value="record.id ? 'PUT' : 'POST'">
            <input type="hidden" name="_record" :value="JSON.stringify({ id: record.id ?? null })">
        </x-slot:hidden>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <x-form.input :label="__('Name')" name="name" required maxlength="80" x-model="record.name" />
                <x-form.input :label="__('Area')" name="area_acres" type="number" step="0.01" min="0.01" required x-model="record.area_acres" :suffix="__('acres')" />
            </div>
            <label class="mt-4 flex items-center gap-2 text-sm text-stone-700" x-show="record.id">
                <input type="checkbox" name="is_active" value="1" x-model="record.is_active" class="size-4 rounded border-stone-300 text-brand-600 focus:ring-brand-500">
                {{ __('Active (shown in forms)') }}
            </label>
        </x-modal>
    </x-slot:modals>
</x-settings-layout>
