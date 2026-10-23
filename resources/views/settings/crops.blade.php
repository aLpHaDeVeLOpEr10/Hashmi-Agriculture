<x-settings-layout :title="__('Crops')" :description="__('Crops available when creating a season.')"
    :record="['name' => '', 'is_active' => true]">
    @can('manage-settings')
        <x-slot:actions>
            <x-button icon="plus" size="sm" x-on:click="record = { ...blank }; $dispatch('open-modal', 'record')">{{ __('Add crop') }}</x-button>
        </x-slot:actions>
    @endcan

    <x-table min-width="min-w-[360px]">
        <x-slot:head>
            <tr><th>{{ __('Name') }}</th><th></th></tr>
        </x-slot:head>
        @foreach ($crops as $crop)
            <tr @class(['opacity-60' => ! $crop->is_active])>
                <td class="font-medium text-stone-900">{{ __($crop->name) }} @unless ($crop->is_active)<x-badge status="Inactive" :dot="false" />@endunless</td>
                <td class="text-end">
                    @can('manage-settings')
                            <x-button variant="ghost" size="sm" icon="pencil" x-on:click="record = {{ Js::from($crop->only('id', 'name', 'is_active')) }}; $dispatch('open-modal', 'record')">{{ __('Edit') }}</x-button>
                        @endcan
                </td>
            </tr>
        @endforeach
    </x-table>

    <x-slot:modals>
        <x-modal name="record" :title="__('Add crop')" title-bind="record.id ? {{ Js::from(__('Edit crop')) }} : {{ Js::from(__('Add crop')) }}" size="sm"
            bind-action="record.id ? {{ Js::from(url('/settings/crops')) }} + '/' + record.id : {{ Js::from(url('/settings/crops')) }}">
        <x-slot:hidden>
            <input type="hidden" name="_method" :value="record.id ? 'PUT' : 'POST'">
            <input type="hidden" name="_record" :value="JSON.stringify({ id: record.id ?? null })">
        </x-slot:hidden>
            <x-form.input :label="__('Name')" name="name" required maxlength="60" x-model="record.name" />
            <label class="mt-4 flex items-center gap-2 text-sm text-stone-700" x-show="record.id">
                <input type="checkbox" name="is_active" value="1" x-model="record.is_active" class="size-4 rounded border-stone-300 text-brand-600 focus:ring-brand-500">
                {{ __('Active (shown in forms)') }}
            </label>
        </x-modal>
    </x-slot:modals>
</x-settings-layout>
