@use('App\Support\Money')
@use('App\Models\Machine')
@use('App\Models\SharedCost')

@php
    $canManage = auth()->user()->can('manage-machinery');
    $canLog = auth()->user()->can('enter-expenses') && $seasons->isNotEmpty();
    $machineBlank = ['name' => '', 'type' => 'tractor', 'ownership' => 'owned', 'internal_rate_per_hour' => '', 'is_active' => true];
    $basisLabels = ['hours' => __('By machine hours'), 'acres' => __('By acres worked'), 'equal' => __('Equally'), 'manual' => __('Manual percentages')];
@endphp

<x-layouts.app :title="__('Machinery')" :breadcrumbs="[__('Machinery')]">
    <div x-data="{ machine: {{ Js::from($machineBlank) }}, usageMachine: '' }">
        <x-page-header :description="__('Log machine hours as work is done. Shared bills such as a tractor repair are entered once, then split across the stages that used the machine.')">
            <x-slot:actions>
                @if ($canManage)
                    <x-button variant="secondary" icon="split" x-on:click="$dispatch('open-modal', 'shared')">{{ __('Shared cost') }}</x-button>
                @endif
                @if ($canLog)
                    <x-button icon="clock" x-on:click="$dispatch('open-modal', 'usage')">{{ __('Log hours') }}</x-button>
                @endif
            </x-slot:actions>
        </x-page-header>

        <x-card :title="__('Machines')" :description="__('Owned and rented machinery')" flush>
            @if ($canManage)
                <x-slot:actions>
                    <x-button variant="secondary" size="sm" icon="plus" x-on:click="machine = {{ Js::from($machineBlank) }}; $dispatch('open-modal', 'machine')">{{ __('Add machine') }}</x-button>
                </x-slot:actions>
            @endif
            @if ($machines->isEmpty())
                <x-empty-state icon="tractor" :title="__('No machines yet')" :description="__('Add your tractors, pumps and choppers to log their hours.')" />
            @else
                <div class="grid grid-cols-1 gap-3 p-5 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach ($machines as $item)
                        @php $t = $totals[$item->id] ?? null; @endphp
                        <div @class(['rounded-lg border p-4', 'border-stone-200' => $item->is_active, 'border-dashed border-stone-200 opacity-60' => ! $item->is_active])>
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex items-center gap-3">
                                    <span class="grid size-9 place-items-center rounded-lg bg-stone-100 text-stone-600"><x-icon :name="$item->type === 'pump' ? 'droplets' : 'tractor'" class="size-4" /></span>
                                    <div>
                                        <p class="font-medium text-stone-900">{{ $item->name }}</p>
                                        <p class="text-xs text-stone-500">{{ __(ucfirst($item->type)) }} · {{ __(ucfirst($item->ownership)) }}@if ($item->internal_rate_per_hour) · {{ __(':rate/h internal', ['rate' => Money::number($item->internal_rate_per_hour)]) }}@endif</p>
                                    </div>
                                </div>
                                @if ($canManage)
                                    <button type="button" class="text-xs font-medium text-brand-700 hover:text-brand-800"
                                        x-on:click="machine = {{ Js::from(['id' => $item->id, 'name' => $item->name, 'type' => $item->type, 'ownership' => $item->ownership, 'internal_rate_per_hour' => $item->internal_rate_per_hour !== null ? (float) $item->internal_rate_per_hour : '', 'is_active' => $item->is_active]) }}; $dispatch('open-modal', 'machine')">{{ __('Edit') }}</button>
                                @endif
                            </div>
                            <dl class="mt-4 grid grid-cols-3 gap-2 text-sm">
                                <div><dt class="text-xs text-stone-500">{{ __('Hours') }}</dt><dd class="font-semibold text-stone-900 tabular-nums">{{ Money::quantity($t->hours ?? 0) }}</dd></div>
                                <div><dt class="text-xs text-stone-500">{{ __('Acres') }}</dt><dd class="font-semibold text-stone-900 tabular-nums">{{ Money::quantity($t->acres ?? 0) }}</dd></div>
                                <div><dt class="text-xs text-stone-500">{{ __('Litres / acre') }}</dt><dd class="font-semibold text-stone-900 tabular-nums">{{ ($t->acres ?? 0) > 0 ? Money::number($t->litres / $t->acres, 1) : '—' }}</dd></div>
                            </dl>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-card>

        <x-card :title="__('Shared costs')" :description="__('Entered once, split across seasons and stages')" flush class="mt-6">
            @if ($sharedCosts->isEmpty())
                <x-empty-state icon="split" :title="__('No shared costs')" :description="__('Use this for repairs, tyres or batteries that serve several seasons.')" />
            @else
                <x-table min-width="min-w-[820px]">
                    <x-slot:head>
                        <tr>
                            <th>{{ __('Date') }}</th>
                            <th>{{ __('Description') }}</th>
                            <th>{{ __('Machine') }}</th>
                            <th>{{ __('Split') }}</th>
                            <th class="text-end">{{ __('Amount') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th></th>
                        </tr>
                    </x-slot:head>
                    @foreach ($sharedCosts as $cost)
                        <tr>
                            <td class="whitespace-nowrap">{{ $cost->cost_date->translatedFormat('d M Y') }}</td>
                            <td>
                                <span class="block font-medium text-stone-900">{{ $cost->description }}</span>
                                <span class="block text-xs text-stone-500">{{ $cost->category->fullName() }}@if ($cost->vendor) · {{ $cost->vendor->name }}@endif</span>
                            </td>
                            <td class="whitespace-nowrap">{{ $cost->machine?->name ?? '—' }}</td>
                            <td class="text-sm">
                                {{ $basisLabels[$cost->basis] }}
                                @if ($cost->status === 'allocated')
                                    <span class="block text-xs text-stone-500">{{ $cost->expenses->map(fn ($e) => $e->season->code)->unique()->join(', ') }}</span>
                                @endif
                            </td>
                            <td class="text-end font-semibold whitespace-nowrap tabular-nums">{{ Money::format($cost->total_amount) }}</td>
                            <td><x-badge :tone="$cost->status === 'allocated' ? 'green' : 'amber'">{{ $cost->status === 'allocated' ? __('Split') : __('Draft') }}</x-badge></td>
                            <td class="text-end">
                                <x-button :variant="$cost->status === 'draft' ? 'soft' : 'ghost'" size="sm" :href="route('machinery.shared.show', $cost)">{{ $cost->status === 'draft' ? __('Split now') : __('View') }}</x-button>
                            </td>
                        </tr>
                    @endforeach
                </x-table>
            @endif
        </x-card>

        <x-card :title="__('Machine-hour log')" :description="__('Latest 25 entries')" flush class="mt-6">
            @if ($usage->isEmpty())
                <x-empty-state icon="clock" :title="__('No hours logged yet')" :description="__('Log hours, acres and fuel each time a machine works a field.')" />
            @else
                <x-table min-width="min-w-[860px]">
                    <x-slot:head>
                        <tr>
                            <th>{{ __('Date') }}</th>
                            <th>{{ __('Machine') }}</th>
                            <th>{{ __('Season / stage') }}</th>
                            <th class="text-end">{{ __('Hours') }}</th>
                            <th class="text-end">{{ __('Acres') }}</th>
                            <th class="text-end">{{ __('Fuel') }}</th>
                            <th>{{ __('Operator') }}</th>
                            <th></th>
                        </tr>
                    </x-slot:head>
                    @foreach ($usage as $log)
                        <tr>
                            <td class="whitespace-nowrap">{{ $log->usage_date->translatedFormat('d M Y') }}</td>
                            <td class="whitespace-nowrap font-medium text-stone-900">{{ $log->machine->name }}</td>
                            <td class="whitespace-nowrap">{{ $log->stage->season->code }} <span class="block text-xs text-stone-500">{{ $log->stage->name() }}</span></td>
                            <td class="text-end tabular-nums">{{ Money::quantity($log->hours) }}</td>
                            <td class="text-end tabular-nums">{{ $log->acres !== null ? Money::quantity($log->acres) : '—' }}</td>
                            <td class="text-end whitespace-nowrap tabular-nums">{{ $log->fuel_litres !== null ? Money::quantity($log->fuel_litres).' L' : '—' }}</td>
                            <td>{{ $log->operator ?? '—' }}</td>
                            <td class="text-end">
                                @if ($canManage && ! $log->stage->season->isClosed())
                                    <form method="POST" action="{{ route('machinery.usage.destroy', $log) }}" x-data x-on:submit="if (!confirm(@js(__('Delete this hour log entry?')))) $event.preventDefault()">
                                        @csrf @method('DELETE')
                                        <x-button type="submit" variant="ghost" size="icon" :aria-label="__('Delete')"><x-icon name="trash-2" class="size-4 text-red-500" /></x-button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </x-table>
            @endif
        </x-card>

        @if ($canLog)
            <x-modal name="usage" :title="__('Log machine hours')" size="md" :action="route('machinery.usage.store')" :submit="__('Save hours')">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <x-form.select :label="__('Machine')" name="resource_id" required x-model="usageMachine">
                            @foreach ($machines->where('is_active', true) as $item)
                                <option value="{{ $item->id }}" data-rate="{{ $item->ownership === 'owned' ? (float) $item->internal_rate_per_hour : 0 }}">{{ $item->name }}</option>
                            @endforeach
                        </x-form.select>
                    </div>
                    <div class="sm:col-span-2">
                        <x-stage-select :seasons="$seasons" />
                    </div>
                    <x-form.input :label="__('Date')" name="usage_date" type="date" required :value="now()->toDateString()" :max="now()->toDateString()" />
                    <x-form.input :label="__('Hours')" name="hours" type="number" min="0.1" step="0.1" required suffix="h" />
                    <x-form.input :label="__('Acres worked')" name="acres" type="number" min="0" step="0.01" :suffix="__('acres')" />
                    <x-form.input :label="__('Fuel used')" name="fuel_litres" type="number" min="0" step="0.1" suffix="L" />
                    <div class="sm:col-span-2">
                        <x-form.input :label="__('Operator')" name="operator" maxlength="80" />
                    </div>
                    @if ($canManage)
                        <label class="flex items-start gap-3 rounded-lg border border-stone-200 p-3 text-sm sm:col-span-2"
                            x-show="usageMachine && Number($root.querySelector(`option[value='${usageMachine}']`)?.dataset.rate) > 0">
                            <input type="checkbox" name="add_notional" value="1" class="mt-0.5 size-4 rounded border-stone-300 text-brand-600 focus:ring-brand-500">
                            <span>
                                <span class="font-medium text-stone-800">{{ __('Add notional machine cost') }}</span>
                                <span class="block text-stone-500">{{ __('Hours × internal rate, shown as economic cost only. Not a cash bill.') }}</span>
                            </span>
                        </label>
                    @endif
                </div>
            </x-modal>
        @endif

        @if ($canManage)
            <x-modal name="machine" :title="__('Add machine')" title-bind="machine.id ? {{ Js::from(__('Edit machine')) }} : {{ Js::from(__('Add machine')) }}" size="md"
                bind-action="machine.id ? {{ Js::from(url('/machinery/machines')) }} + '/' + machine.id : {{ Js::from(url('/machinery/machines')) }}">
                <x-slot:hidden>
                    <input type="hidden" name="_method" :value="machine.id ? 'PUT' : 'POST'">
                </x-slot:hidden>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <x-form.input :label="__('Name')" name="name" required maxlength="100" x-model="machine.name" :placeholder="__('e.g. Massey Ferguson 385')" />
                    </div>
                    <x-form.select :label="__('Type')" name="type" required :placeholder="false" x-model="machine.type">
                        @foreach (Machine::TYPES as $type)
                            <option value="{{ $type }}">{{ __(ucfirst($type)) }}</option>
                        @endforeach
                    </x-form.select>
                    <x-form.select :label="__('Ownership')" name="ownership" required :placeholder="false" x-model="machine.ownership" :options="['owned' => __('Owned'), 'rented' => __('Rented')]" />
                    <div class="sm:col-span-2" x-show="machine.ownership === 'owned'">
                        <x-form.input :label="__('Internal rate per hour')" name="internal_rate_per_hour" type="number" min="0" step="1" x-model="machine.internal_rate_per_hour" :suffix="config('agri.currency')"
                            :hint="__('Optional. Used only for notional (economic) cost: depreciation and wear.')" />
                    </div>
                    <label class="flex items-center gap-2 text-sm text-stone-700" x-show="machine.id">
                        <input type="checkbox" name="is_active" value="1" x-model="machine.is_active" class="size-4 rounded border-stone-300 text-brand-600 focus:ring-brand-500"> {{ __('In use') }}
                    </label>
                </div>
            </x-modal>

            <x-modal name="shared" :title="__('New shared cost')" :description="__('Saved as a draft. Next you choose the stages to split it across.')" size="md" :action="route('machinery.shared.store')" :submit="__('Save & choose stages')">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2" x-data="{ basis: 'hours' }">
                    <div class="sm:col-span-2">
                        <x-form.input :label="__('Description')" name="description" required maxlength="200" :placeholder="__('e.g. Tractor hydraulic pump repair')" />
                    </div>
                    <x-form.input :label="__('Date')" name="cost_date" type="date" required :value="now()->toDateString()" :max="now()->toDateString()" />
                    <x-form.input :label="__('Total amount')" name="total_amount" type="number" min="0.01" step="0.01" required :suffix="config('agri.currency')" />
                    <div class="sm:col-span-2">
                        <x-form.select :label="__('Category')" name="category_id" required>
                            @foreach ($categories as $parent)
                                <optgroup label="{{ __($parent->name) }}">
                                    @foreach ($parent->children as $child)
                                        <option value="{{ $child->id }}" @selected($child->name === 'Tractor repair')>{{ __($child->name) }}</option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </x-form.select>
                    </div>
                    <x-form.select :label="__('Split')" name="basis" required :placeholder="false" x-model="basis" :options="$basisLabels" />
                    <x-form.select :label="__('Machine')" name="resource_id" :placeholder="'—'" :options="$machines->pluck('name', 'id')->all()" x-bind:required="basis === 'hours' || basis === 'acres'" />
                    <div class="sm:col-span-2">
                        <x-form.select :label="__('Vendor')" name="vendor_id" :placeholder="'—'" :options="$vendors->pluck('name', 'id')->all()" />
                    </div>
                </div>
            </x-modal>
        @endif
    </div>
</x-layouts.app>
