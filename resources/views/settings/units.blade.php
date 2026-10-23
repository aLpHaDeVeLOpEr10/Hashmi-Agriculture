@use('App\Models\Unit')

<x-settings-layout :title="__('Units')" :description="__('Units used for quantities on expenses, harvests and machine logs.')"
    :record="['code' => '', 'name' => '', 'kind' => 'count']">
    @can('manage-settings')
        <x-slot:actions>
            <x-button icon="plus" size="sm" x-on:click="record = { ...blank }; $dispatch('open-modal', 'record')">{{ __('Add unit') }}</x-button>
        </x-slot:actions>
    @endcan

    <x-table min-width="min-w-[420px]">
        <x-slot:head>
            <tr><th>{{ __('Code') }}</th><th>{{ __('Name') }}</th><th>{{ __('Kind') }}</th><th></th></tr>
        </x-slot:head>
        @foreach ($units as $unit)
            <tr>
                <td class="font-mono text-xs">{{ $unit->code }}</td>
                <td class="font-medium text-stone-900">{{ __($unit->name) }}</td>
                <td>{{ __(ucfirst($unit->kind)) }}</td>
                <td class="text-end">
                    @can('manage-settings')
                            <x-button variant="ghost" size="sm" icon="pencil" x-on:click="record = {{ Js::from(['id' => $unit->code] + $unit->only('code', 'name', 'kind')) }}; $dispatch('open-modal', 'record')">{{ __('Edit') }}</x-button>
                        @endcan
                </td>
            </tr>
        @endforeach
    </x-table>

    <x-slot:modals>
        <x-modal name="record" :title="__('Add unit')" title-bind="record.id ? {{ Js::from(__('Edit unit')) }} : {{ Js::from(__('Add unit')) }}" size="md"
            bind-action="record.id ? {{ Js::from(url('/settings/units')) }} + '/' + record.id : {{ Js::from(url('/settings/units')) }}">
        <x-slot:hidden>
            <input type="hidden" name="_method" :value="record.id ? 'PUT' : 'POST'">
            <input type="hidden" name="_record" :value="JSON.stringify({ id: record.id ?? null })">
        </x-slot:hidden>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <x-form.input :label="__('Code')" name="code" maxlength="10" x-model="record.code" x-bind:disabled="!!record.id" x-bind:required="!record.id" :hint="__('Short, e.g. kg')" />
                <x-form.input :label="__('Name')" name="name" required maxlength="40" x-model="record.name" />
                <x-form.select :label="__('Kind')" name="kind" required :placeholder="false" x-model="record.kind">
                    @foreach (Unit::KINDS as $kind)
                        <option value="{{ $kind }}">{{ __(ucfirst($kind)) }}</option>
                    @endforeach
                </x-form.select>
            </div>

        </x-modal>
    </x-slot:modals>
</x-settings-layout>
