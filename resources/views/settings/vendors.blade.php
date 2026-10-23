@use('App\Models\Vendor')

<x-settings-layout :title="__('Vendors')" :description="__('Seed dealers, fuel stations, tractor owners, labour contractors and utilities you pay.')"
    :record="['name' => '', 'phone' => '', 'type' => 'supplier', 'is_active' => true]">
    @can('manage-settings')
        <x-slot:actions>
            <x-button icon="plus" size="sm" x-on:click="record = { ...blank }; $dispatch('open-modal', 'record')">{{ __('Add vendor') }}</x-button>
        </x-slot:actions>
    @endcan

    @if ($vendors->isEmpty())
        <x-empty-state icon="store" :title="__('No vendors yet')" :description="__('Add the people and businesses you buy from.')" />
    @else
        <x-table min-width="min-w-[560px]">
            <x-slot:head>
                <tr><th>{{ __('Name') }}</th><th>{{ __('Type') }}</th><th>{{ __('Phone') }}</th><th class="text-end">{{ __('Expenses') }}</th><th></th></tr>
            </x-slot:head>
            @foreach ($vendors as $vendor)
                <tr @class(['opacity-60' => ! $vendor->is_active])>
                    <td class="font-medium text-stone-900">{{ $vendor->name }} @unless ($vendor->is_active)<x-badge status="Inactive" :dot="false" />@endunless</td>
                    <td>{{ __(ucfirst($vendor->type)) }}</td>
                    <td class="whitespace-nowrap tabular-nums" dir="ltr">{{ \App\Support\Phone::display($vendor->phone) ?: '—' }}</td>
                    <td class="text-end tabular-nums">{{ $vendor->expenses_count }}</td>
                    <td class="text-end">
                        @can('manage-settings')
                            <x-button variant="ghost" size="sm" icon="pencil" x-on:click="record = {{ Js::from($vendor->only('id', 'name', 'phone', 'type', 'is_active')) }}; $dispatch('open-modal', 'record')">{{ __('Edit') }}</x-button>
                        @endcan
                    </td>
                </tr>
            @endforeach
        </x-table>
    @endif

    <x-slot:modals>
        <x-modal name="record" :title="__('Add vendor')" title-bind="record.id ? {{ Js::from(__('Edit vendor')) }} : {{ Js::from(__('Add vendor')) }}" size="md"
            bind-action="record.id ? {{ Js::from(url('/settings/vendors')) }} + '/' + record.id : {{ Js::from(url('/settings/vendors')) }}">
        <x-slot:hidden>
            <input type="hidden" name="_method" :value="record.id ? 'PUT' : 'POST'">
            <input type="hidden" name="_record" :value="JSON.stringify({ id: record.id ?? null })">
        </x-slot:hidden>
            <div class="grid grid-cols-1 gap-4">
                <x-form.input :label="__('Name')" name="name" required maxlength="120" x-model="record.name" />
                <div class="grid grid-cols-2 gap-4">
                    <x-form.input :label="__('Phone')" name="phone" type="tel" maxlength="20" x-model="record.phone" dir="ltr" />
                    <x-form.select :label="__('Type')" name="type" required :placeholder="false" x-model="record.type">
                        @foreach (Vendor::TYPES as $type)
                            <option value="{{ $type }}">{{ __(ucfirst($type)) }}</option>
                        @endforeach
                    </x-form.select>
                </div>
            </div>
            <label class="mt-4 flex items-center gap-2 text-sm text-stone-700" x-show="record.id">
                <input type="checkbox" name="is_active" value="1" x-model="record.is_active" class="size-4 rounded border-stone-300 text-brand-600 focus:ring-brand-500">
                {{ __('Active (shown in forms)') }}
            </label>
        </x-modal>
    </x-slot:modals>
</x-settings-layout>
