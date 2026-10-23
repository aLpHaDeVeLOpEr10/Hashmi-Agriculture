<x-settings-layout :title="__('Stage templates')" :description="__('New seasons are created with these stages, in this order. Renaming a stage updates every season that uses it.')"
    :record="['name' => '', 'sequence' => $templates->max('sequence') + 1, 'is_active' => true]">
    @can('manage-settings')
        <x-slot:actions>
            <x-button icon="plus" size="sm" x-on:click="record = { ...blank }; $dispatch('open-modal', 'record')">{{ __('Add stage') }}</x-button>
        </x-slot:actions>
    @endcan

    <ol class="divide-y divide-stone-100">
        @foreach ($templates as $template)
            <li @class(['flex items-center gap-4 px-5 py-3.5', 'opacity-60' => ! $template->is_active])>
                <span class="grid size-8 shrink-0 place-items-center rounded-full bg-brand-50 text-sm font-semibold text-brand-700">{{ $template->sequence }}</span>
                <span class="min-w-0 flex-1">
                    <span class="block font-medium text-stone-900">{{ __($template->name) }}</span>
                    <span class="block font-mono text-xs text-stone-400">{{ $template->code }}</span>
                </span>
                @unless ($template->is_active)<x-badge status="Inactive" :dot="false" />@endunless
                @can('manage-settings')
                            <x-button variant="ghost" size="sm" icon="pencil" x-on:click="record = {{ Js::from($template->only('id', 'name', 'sequence', 'is_active')) }}; $dispatch('open-modal', 'record')">{{ __('Edit') }}</x-button>
                        @endcan
            </li>
        @endforeach
    </ol>

    <x-slot:modals>
        <x-modal name="record" :title="__('Add stage')" title-bind="record.id ? {{ Js::from(__('Edit stage')) }} : {{ Js::from(__('Add stage')) }}" size="sm"
            bind-action="record.id ? {{ Js::from(url('/settings/stages')) }} + '/' + record.id : {{ Js::from(url('/settings/stages')) }}">
        <x-slot:hidden>
            <input type="hidden" name="_method" :value="record.id ? 'PUT' : 'POST'">
            <input type="hidden" name="_record" :value="JSON.stringify({ id: record.id ?? null })">
        </x-slot:hidden>
            <div class="grid grid-cols-3 gap-4">
                <div class="col-span-2">
                    <x-form.input :label="__('Name')" name="name" required maxlength="60" x-model="record.name" />
                </div>
                <x-form.input :label="__('Order')" name="sequence" type="number" min="1" max="99" required x-model="record.sequence" />
            </div>
            <label class="mt-4 flex items-center gap-2 text-sm text-stone-700" x-show="record.id">
                <input type="checkbox" name="is_active" value="1" x-model="record.is_active" class="size-4 rounded border-stone-300 text-brand-600 focus:ring-brand-500">
                {{ __('Include in new seasons') }}
            </label>
        </x-modal>
    </x-slot:modals>
</x-settings-layout>
