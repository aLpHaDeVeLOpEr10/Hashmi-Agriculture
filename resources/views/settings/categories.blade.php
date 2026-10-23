@use('App\Models\ExpenseCategory')

<x-settings-layout :title="__('Expense categories')" :description="__('Two levels, e.g. Fuel → Diesel. System categories are shared; add your own beneath them or as new groups.')"
    :record="['name' => '', 'parent_id' => '', 'cost_type' => 'other', 'default_unit' => '', 'is_active' => true]">
    @can('manage-settings')
        <x-slot:actions>
            <x-button icon="plus" size="sm" x-on:click="record = { ...blank }; $dispatch('open-modal', 'record')">{{ __('Add category') }}</x-button>
        </x-slot:actions>
    @endcan

    <ul class="divide-y divide-stone-100">
        @foreach ($categories as $parent)
            <li class="px-5 py-4">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="font-semibold text-stone-900">{{ __($parent->name) }}</span>
                    <x-badge tone="stone" :dot="false">{{ __(ucfirst($parent->cost_type)) }}</x-badge>
                    @if ($parent->isSystem())
                        <span class="text-xs text-stone-400">{{ __('System') }}</span>
                    @elseif (auth()->user()->can('manage-settings'))
                        <x-button variant="ghost" size="sm" icon="pencil" class="ms-auto" x-on:click="record = {{ Js::from($parent->only('id', 'name', 'parent_id', 'cost_type', 'default_unit', 'is_active')) }}; $dispatch('open-modal', 'record')">{{ __('Edit') }}</x-button>
                    @endif
                </div>
                <div class="mt-2 flex flex-wrap gap-1.5">
                    @foreach ($parent->children as $child)
                        @if ($child->isSystem() || ! auth()->user()->can('manage-settings'))
                            <span @class(['rounded-full border px-2.5 py-1 text-xs', 'border-stone-200 text-stone-700' => $child->is_active, 'border-dashed border-stone-200 text-stone-400 line-through' => ! $child->is_active])>
                                {{ __($child->name) }}@if ($child->default_unit) <span class="text-stone-400">· {{ $child->default_unit }}</span>@endif
                            </span>
                        @else
                            <button type="button" x-on:click="record = {{ Js::from($child->only('id', 'name', 'parent_id', 'cost_type', 'default_unit', 'is_active')) }}; $dispatch('open-modal', 'record')"
                                @class(['inline-flex items-center gap-1 rounded-full border px-2.5 py-1 text-xs hover:border-brand-300 hover:bg-brand-50', 'border-brand-200 bg-brand-50/50 text-brand-800' => $child->is_active, 'border-dashed border-stone-200 text-stone-400 line-through' => ! $child->is_active])>
                                {{ $child->name }} <x-icon name="pencil" class="size-3" />
                            </button>
                        @endif
                    @endforeach
                    @can('manage-settings')
                        <button type="button" x-on:click="record = { ...blank, parent_id: {{ $parent->id }}, cost_type: @js($parent->cost_type), default_unit: @js($parent->default_unit ?? '') }; $dispatch('open-modal', 'record')"
                            class="inline-flex items-center gap-1 rounded-full border border-dashed border-stone-300 px-2.5 py-1 text-xs text-stone-500 hover:border-brand-400 hover:text-brand-700">
                            <x-icon name="plus" class="size-3" /> {{ __('Add') }}
                        </button>
                    @endcan
                </div>
            </li>
        @endforeach
    </ul>

    <x-slot:modals>
        <x-modal name="record" :title="__('Add category')" title-bind="record.id ? {{ Js::from(__('Edit category')) }} : {{ Js::from(__('Add category')) }}" size="md"
            bind-action="record.id ? {{ Js::from(url('/settings/categories')) }} + '/' + record.id : {{ Js::from(url('/settings/categories')) }}">
        <x-slot:hidden>
            <input type="hidden" name="_method" :value="record.id ? 'PUT' : 'POST'">
            <input type="hidden" name="_record" :value="JSON.stringify({ id: record.id ?? null })">
        </x-slot:hidden>
            <div class="grid grid-cols-1 gap-4">
                <x-form.input :label="__('Name')" name="name" required maxlength="80" x-model="record.name" />
                <x-form.select :label="__('Group')" name="parent_id" :placeholder="__('— None (this is a new group) —')" x-model="record.parent_id">
                    @foreach ($categories as $parent)
                        <option value="{{ $parent->id }}">{{ __($parent->name) }}</option>
                    @endforeach
                </x-form.select>
                <div class="grid grid-cols-2 gap-4">
                    <x-form.select :label="__('Cost type')" name="cost_type" required :placeholder="false" x-model="record.cost_type">
                        @foreach (ExpenseCategory::COST_TYPES as $type)
                            <option value="{{ $type }}">{{ __(ucfirst($type)) }}</option>
                        @endforeach
                    </x-form.select>
                    <x-form.select :label="__('Default unit')" name="default_unit" :placeholder="'—'" x-model="record.default_unit">
                        @foreach ($units as $unit)
                            <option value="{{ $unit->code }}">{{ __($unit->name) }}</option>
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
