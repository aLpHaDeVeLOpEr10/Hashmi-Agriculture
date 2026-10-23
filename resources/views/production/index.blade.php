@use('App\Support\Money')
@use('App\Models\HarvestRecord')
@use('App\Models\SilageBatch')
@use('App\Models\StorageUnit')

@php
    $open = $season && ! $season->isClosed();
    $canEnter = $open && auth()->user()->can('enter-production');
    $canManage = auth()->user()->can('manage-production');
    $summary = $season?->summary;
    $harvestBlank = ['harvest_date' => now()->toDateString(), 'field_id' => '', 'method' => 'weighbridge', 'quantity_kg' => '', 'trolley_count' => '', 'avg_trolley_kg' => '', 'moisture_pct' => ''];
    $batchBlank = ['storage_unit_id' => '', 'fill_start' => now()->toDateString(), 'sealed_date' => '', 'fresh_weight_kg' => '', 'method' => 'weighbridge', 'expected_loss_pct' => 10];
    $unitBlank = ['name' => '', 'type' => 'pit', 'capacity_tonnes' => '', 'is_active' => true];
@endphp

<x-layouts.app :title="__('Production')" :breadcrumbs="[__('Production')]">
    <div x-data="{ harvest: {{ Js::from($harvestBlank) }}, batch: {{ Js::from($batchBlank) }}, unit: {{ Js::from($unitBlank) }}, advance: { url: '', status: '', label: '' } }">
        <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <form method="GET" class="flex items-center gap-2">
                <label for="season-picker" class="text-sm text-stone-500">{{ __('Season') }}</label>
                <select id="season-picker" name="season" onchange="this.form.submit()" class="form-control h-9 w-auto py-0 pe-8">
                    @foreach ($seasons as $option)
                        <option value="{{ $option->code }}" @selected($season?->is($option))>{{ $option->name }} · {{ $option->crop->name }} ({{ $option->code }})</option>
                    @endforeach
                </select>
                @if ($season)
                    <x-status kind="season" :value="$season->status" />
                @endif
            </form>
            <div class="flex flex-wrap gap-2">
                @if ($canEnter)
                    <x-button variant="secondary" icon="wheat" x-on:click="harvest = {{ Js::from($harvestBlank) }}; $dispatch('open-modal', 'harvest')">{{ __('Harvest') }}</x-button>
                    <x-button icon="plus" x-on:click="batch = {{ Js::from($batchBlank) }}; $dispatch('open-modal', 'batch')">{{ __('Silage batch') }}</x-button>
                @endif
            </div>
        </div>

        @if ($season)
            <section class="grid grid-cols-2 gap-px overflow-hidden rounded-xl border border-stone-200 bg-stone-200 shadow-card md:grid-cols-3 xl:grid-cols-6">
                @foreach ([
                    [__('Harvested'), Money::number(($summary->harvested_kg ?? 0) / 1000, 1).' t', __(':kg kg per acre', ['kg' => Money::number($summary->yield_kg_per_acre)])],
                    [__('Cost per tonne harvested'), $summary->cost_per_tonne_harvested ? Money::format($summary->cost_per_tonne_harvested) : '—', __('Season total ÷ tonnes harvested')],
                    [__('Silage packed'), Money::number(($summary->silage_fresh_kg ?? 0) / 1000, 1).' t', __('Fresh weight')],
                    [__('Usable silage'), Money::number(($summary->silage_usable_kg ?? 0) / 1000, 1).' t', __('After expected storage loss')],
                    [__('Silage cost per tonne'), $summary->silage_cost_per_tonne ? Money::format($summary->silage_cost_per_tonne) : '—', $summary->silage_cost_per_kg ? __(':cost per kg', ['cost' => Money::format($summary->silage_cost_per_kg, 2)]) : __('Needs a silage batch')],
                    [__('Season total'), Money::format($summary->total_cost), __(':amount per acre', ['amount' => Money::format($summary->cost_per_acre)])],
                ] as [$label, $value, $hint])
                    <div class="bg-white p-4">
                        <p class="text-xs text-stone-500">{{ $label }}</p>
                        <p class="mt-1 text-lg font-semibold text-stone-900 tabular-nums">{{ $value }}</p>
                        <p class="text-xs text-stone-500">{{ $hint }}</p>
                    </div>
                @endforeach
            </section>
            @if ($season->isClosed())
                <p class="mt-3 flex items-center gap-2 text-sm text-stone-600"><x-icon name="lock" class="size-4 text-stone-400" /> {{ __('This season is closed. Its silage cost per kg is frozen onto each batch below.') }}</p>
            @endif
        @endif

        <x-card :title="__('Pit status')" :description="__('Silage pits, bunkers and bale stacks')" class="mt-6" flush>
            @if ($canManage)
                <x-slot:actions>
                    <x-button variant="secondary" size="sm" icon="plus" x-on:click="unit = {{ Js::from($unitBlank) }}; $dispatch('open-modal', 'unit')">{{ __('Storage unit') }}</x-button>
                </x-slot:actions>
            @endif
            @if ($units->isEmpty())
                <x-empty-state icon="warehouse" :title="__('No storage units yet')" :description="__('Add your silage pits and bunkers before recording batches.')" />
            @else
                <div class="grid grid-cols-1 gap-3 p-5 sm:grid-cols-2 xl:grid-cols-4">
                    @foreach ($units as $unit)
                        @php
                            $current = $unit->batches;
                            $filledT = $current->sum(fn ($b) => $b->usableKg()) / 1000;
                            $pct = $unit->capacity_tonnes > 0 ? min(100, round($filledT / $unit->capacity_tonnes * 100)) : 0;
                        @endphp
                        <div @class(['rounded-lg border p-4', 'border-stone-200' => $unit->is_active, 'border-dashed border-stone-200 opacity-60' => ! $unit->is_active])>
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <p class="font-medium text-stone-900">{{ $unit->name }}</p>
                                    <p class="text-xs text-stone-500">{{ StorageUnit::typeLabel($unit->type) }} · {{ Money::number($unit->capacity_tonnes) }} t</p>
                                </div>
                                @if ($current->isNotEmpty())
                                    <x-status kind="batch" :value="$current->first()->status" />
                                @else
                                    <x-badge tone="stone">{{ __('Empty') }}</x-badge>
                                @endif
                            </div>
                            <div class="mt-3 flex justify-between text-xs text-stone-500">
                                <span>{{ Money::number($filledT, 1) }} t {{ __('usable') }}</span>
                                <span class="tabular-nums">{{ $pct }}%</span>
                            </div>
                            <x-progress :value="$pct" :tone="$pct >= 95 ? 'amber' : 'brand'" class="mt-1.5" />
                            <div class="mt-3 flex items-center justify-between text-xs">
                                <span class="text-stone-500">{{ $current->map(fn ($b) => $b->code().' · '.$b->season->code)->join(', ') ?: __('No active batch') }}</span>
                                @if ($canManage)
                                    <button type="button" x-on:click="unit = {{ Js::from(['id' => $unit->id, 'name' => $unit->name, 'type' => $unit->type, 'capacity_tonnes' => (float) $unit->capacity_tonnes, 'is_active' => $unit->is_active]) }}; $dispatch('open-modal', 'unit')" class="font-medium text-brand-700 hover:text-brand-800">{{ __('Edit') }}</button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-card>

        @if ($season)
            <x-card :title="__('Harvest records')" :description="__('Green fodder harvested for :season', ['season' => $season->name])" class="mt-6" flush>
                @if ($season->harvests->isEmpty())
                    <x-empty-state icon="wheat" :title="__('No harvest recorded')" :description="__('Record weights from weighbridge slips, or trolley count × average trolley weight.')" />
                @else
                    <x-table min-width="min-w-[760px]">
                        <x-slot:head>
                            <tr>
                                <th>{{ __('Date') }}</th>
                                <th>{{ __('Field') }}</th>
                                <th>{{ __('Method') }}</th>
                                <th class="text-end">{{ __('Quantity') }}</th>
                                <th class="text-end">{{ __('Moisture') }}</th>
                                <th></th>
                            </tr>
                        </x-slot:head>
                        @foreach ($season->harvests as $harvest)
                            <tr>
                                <td class="whitespace-nowrap">{{ $harvest->harvest_date->translatedFormat('d M Y') }}</td>
                                <td>{{ $harvest->field?->name ?? '—' }}</td>
                                <td>
                                    {{ HarvestRecord::methodLabel($harvest->method) }}
                                    @if ($harvest->method === 'trolley_count')
                                        <span class="block text-xs text-stone-500">{{ $harvest->trolley_count }} × {{ Money::number($harvest->avg_trolley_kg) }} kg</span>
                                    @endif
                                </td>
                                <td class="text-end font-semibold whitespace-nowrap text-stone-900 tabular-nums">{{ Money::number($harvest->quantity_kg) }} kg</td>
                                <td class="text-end tabular-nums">{{ $harvest->moisture_pct !== null ? Money::quantity($harvest->moisture_pct).'%' : '—' }}</td>
                                <td class="text-end whitespace-nowrap">
                                    @if ($open && $canManage)
                                        <x-button variant="ghost" size="icon" :aria-label="__('Edit')"
                                            x-on:click="harvest = {{ Js::from(['id' => $harvest->id, 'harvest_date' => $harvest->harvest_date->toDateString(), 'field_id' => $harvest->field_id ?? '', 'method' => $harvest->method, 'quantity_kg' => (float) $harvest->quantity_kg, 'trolley_count' => $harvest->trolley_count ?? '', 'avg_trolley_kg' => $harvest->avg_trolley_kg !== null ? (float) $harvest->avg_trolley_kg : '', 'moisture_pct' => $harvest->moisture_pct !== null ? (float) $harvest->moisture_pct : '']) }}; $dispatch('open-modal', 'harvest')">
                                            <x-icon name="pencil" class="size-4" />
                                        </x-button>
                                        <form method="POST" action="{{ route('harvests.destroy', [$season, $harvest]) }}" class="inline" x-data x-on:submit="if (!confirm(@js(__('Delete this harvest record?')))) $event.preventDefault()">
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

            <x-card :title="__('Silage batches')" :description="__('Each pit or bunker filled is one batch. Cost per kg uses usable silage, after the expected storage loss.')" class="mt-6" flush>
                @if ($season->batches->isEmpty())
                    <x-empty-state icon="warehouse" :title="__('No silage batches')" :description="__('Create a batch when you start filling a pit or bunker.')" />
                @else
                    <x-table min-width="min-w-[1040px]">
                        <x-slot:head>
                            <tr>
                                <th>{{ __('Batch') }}</th>
                                <th>{{ __('Filled') }}</th>
                                <th>{{ __('Sealed / opened') }}</th>
                                <th class="text-end">{{ __('Fresh weight') }}</th>
                                <th class="text-end">{{ __('Loss') }}</th>
                                <th class="text-end">{{ __('Usable') }}</th>
                                <th class="text-end">{{ __('Cost / kg') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th></th>
                            </tr>
                        </x-slot:head>
                        @foreach ($season->batches as $batch)
                            @php $next = ['filling' => ['sealed', __('Seal')], 'sealed' => ['feeding', __('Open for feeding')], 'feeding' => ['finished', __('Mark finished')]][$batch->status] ?? null; @endphp
                            <tr>
                                <td class="whitespace-nowrap">
                                    <span class="block font-medium text-stone-900">{{ $batch->code() }}</span>
                                    <span class="block text-xs text-stone-500">{{ $batch->storageUnit->name }}</span>
                                </td>
                                <td class="whitespace-nowrap">{{ $batch->fill_start->translatedFormat('d M Y') }}</td>
                                <td class="whitespace-nowrap text-sm">
                                    {{ $batch->sealed_date?->translatedFormat('d M Y') ?? '—' }}
                                    <span class="block text-xs text-stone-500">{{ $batch->opened_date ? __('opened :date', ['date' => $batch->opened_date->translatedFormat('d M Y')]) : '' }}</span>
                                </td>
                                <td class="text-end whitespace-nowrap tabular-nums">
                                    {{ Money::number($batch->fresh_weight_kg) }} kg
                                    <span class="block text-xs text-stone-500">{{ HarvestRecord::methodLabel($batch->method) }}</span>
                                </td>
                                <td class="text-end tabular-nums">{{ Money::quantity($batch->expected_loss_pct) }}%</td>
                                <td class="text-end font-semibold whitespace-nowrap text-stone-900 tabular-nums">{{ Money::number($batch->usableKg()) }} kg</td>
                                <td class="text-end whitespace-nowrap tabular-nums">
                                    @if ($batch->cost_per_kg !== null)
                                        <span class="inline-flex items-center gap-1 font-semibold text-stone-900"><x-icon name="lock" class="size-3.5 text-stone-400" /> {{ Money::number($batch->cost_per_kg, 2) }}</span>
                                    @elseif ($summary?->silage_cost_per_kg)
                                        <span class="text-stone-600">{{ Money::number($summary->silage_cost_per_kg, 2) }}</span>
                                        <span class="block text-xs text-stone-400">{{ __('live') }}</span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td><x-status kind="batch" :value="$batch->status" /></td>
                                <td class="text-end whitespace-nowrap">
                                    @if ($canManage && $next)
                                        <x-button variant="soft" size="sm" x-on:click="advance = { url: {{ Js::from(route('batches.advance', [$season, $batch])) }}, status: {{ Js::from($next[0]) }}, label: {{ Js::from($next[1].' · '.$batch->code()) }} }; $dispatch('open-modal', 'advance')">{{ $next[1] }}</x-button>
                                    @endif
                                    @if ($open && $canManage)
                                        <x-button variant="ghost" size="icon" :aria-label="__('Edit')"
                                            x-on:click="batch = {{ Js::from(['id' => $batch->id, 'storage_unit_id' => $batch->storage_unit_id, 'fill_start' => $batch->fill_start->toDateString(), 'sealed_date' => $batch->sealed_date?->toDateString() ?? '', 'fresh_weight_kg' => (float) $batch->fresh_weight_kg, 'method' => $batch->method, 'expected_loss_pct' => (float) $batch->expected_loss_pct]) }}; $dispatch('open-modal', 'batch')">
                                            <x-icon name="pencil" class="size-4" />
                                        </x-button>
                                        <form method="POST" action="{{ route('batches.destroy', [$season, $batch]) }}" class="inline" x-data x-on:submit="if (!confirm(@js(__('Delete this silage batch?')))) $event.preventDefault()">
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

            @if ($canEnter || $canManage)
                <x-modal name="harvest" :title="__('Record harvest')" title-bind="harvest.id ? {{ Js::from(__('Edit harvest')) }} : {{ Js::from(__('Record harvest')) }}" size="md"
                    bind-action="harvest.id ? {{ Js::from(url('/seasons/'.$season->code.'/harvests')) }} + '/' + harvest.id : {{ Js::from(url('/seasons/'.$season->code.'/harvests')) }}">
                    <x-slot:hidden>
                        <input type="hidden" name="_method" :value="harvest.id ? 'PUT' : 'POST'">
                    </x-slot:hidden>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <x-form.input :label="__('Harvest date')" name="harvest_date" type="date" required :max="now()->toDateString()" x-model="harvest.harvest_date" />
                        <x-form.select :label="__('Field')" name="field_id" :placeholder="'—'" :options="$season->fields->pluck('name', 'id')->all()" x-model="harvest.field_id" />
                        <div class="sm:col-span-2">
                            <x-form.select :label="__('How was it weighed?')" name="method" required :placeholder="false" x-model="harvest.method">
                                @foreach (HarvestRecord::METHODS as $method)
                                    <option value="{{ $method }}">{{ HarvestRecord::methodLabel($method) }}</option>
                                @endforeach
                            </x-form.select>
                        </div>
                        <template x-if="harvest.method === 'trolley_count'">
                            <div class="grid grid-cols-2 gap-4 sm:col-span-2">
                                <x-form.input :label="__('Trolleys')" name="trolley_count" type="number" min="1" step="1" required x-model="harvest.trolley_count" />
                                <x-form.input :label="__('Average trolley weight')" name="avg_trolley_kg" type="number" min="1" step="0.01" required x-model="harvest.avg_trolley_kg" suffix="kg" />
                                <p class="col-span-2 text-sm text-stone-600">{{ __('Total') }}: <span class="font-semibold tabular-nums" x-text="((parseFloat(harvest.trolley_count) || 0) * (parseFloat(harvest.avg_trolley_kg) || 0)).toLocaleString()"></span> kg</p>
                            </div>
                        </template>
                        <template x-if="harvest.method !== 'trolley_count'">
                            <div class="sm:col-span-2">
                                <x-form.input :label="__('Quantity')" name="quantity_kg" type="number" min="1" step="0.001" required x-model="harvest.quantity_kg" suffix="kg" />
                            </div>
                        </template>
                        <x-form.input :label="__('Moisture')" name="moisture_pct" type="number" min="0" max="100" step="0.1" x-model="harvest.moisture_pct" suffix="%" :hint="__('Optional. Allows a dry-matter cost later.')" />
                    </div>
                </x-modal>

                <x-modal name="batch" :title="__('New silage batch')" title-bind="batch.id ? {{ Js::from(__('Edit silage batch')) }} : {{ Js::from(__('New silage batch')) }}" size="md"
                    bind-action="batch.id ? {{ Js::from(url('/seasons/'.$season->code.'/batches')) }} + '/' + batch.id : {{ Js::from(url('/seasons/'.$season->code.'/batches')) }}">
                    <x-slot:hidden>
                        <input type="hidden" name="_method" :value="batch.id ? 'PUT' : 'POST'">
                    </x-slot:hidden>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <x-form.select :label="__('Pit / bunker')" name="storage_unit_id" required :options="$units->where('is_active', true)->pluck('name', 'id')->all()" x-model="batch.storage_unit_id" />
                        </div>
                        <x-form.input :label="__('Filling started')" name="fill_start" type="date" required :max="now()->toDateString()" x-model="batch.fill_start" />
                        <x-form.input :label="__('Sealed on')" name="sealed_date" type="date" :max="now()->toDateString()" x-model="batch.sealed_date" :hint="__('Leave empty while still filling.')" />
                        <x-form.input :label="__('Fresh weight packed')" name="fresh_weight_kg" type="number" min="1" step="0.001" required x-model="batch.fresh_weight_kg" suffix="kg" />
                        <x-form.select :label="__('Measured by')" name="method" required :placeholder="false" x-model="batch.method">
                            @foreach (SilageBatch::METHODS as $method)
                                <option value="{{ $method }}">{{ HarvestRecord::methodLabel($method) }}</option>
                            @endforeach
                        </x-form.select>
                        <x-form.input :label="__('Expected storage loss')" name="expected_loss_pct" type="number" min="0" max="60" step="0.1" required x-model="batch.expected_loss_pct" suffix="%" :hint="__('Shrink and spoilage. 10% is typical.')" />
                        <p class="self-end pb-2 text-sm text-stone-600">{{ __('Usable') }}: <span class="font-semibold tabular-nums" x-text="Math.round((parseFloat(batch.fresh_weight_kg) || 0) * (100 - (parseFloat(batch.expected_loss_pct) || 0)) / 100).toLocaleString()"></span> kg</p>
                    </div>
                </x-modal>
            @endif

            @if ($canManage)
                <x-modal name="advance" :title="__('Update batch')" title-bind="advance.label" size="sm" bind-action="advance.url" :submit="__('Confirm')">
                    <x-slot:hidden>
                        <input type="hidden" name="status" :value="advance.status">
                    </x-slot:hidden>
                    <x-form.input :label="__('Date')" name="date" type="date" required :value="now()->toDateString()" :max="now()->toDateString()" />
                </x-modal>
            @endif
        @endif

        @if ($canManage)
            <x-modal name="unit" :title="__('Add storage unit')" title-bind="unit.id ? {{ Js::from(__('Edit storage unit')) }} : {{ Js::from(__('Add storage unit')) }}" size="sm"
                bind-action="unit.id ? {{ Js::from(url('/storage-units')) }} + '/' + unit.id : {{ Js::from(url('/storage-units')) }}">
                <x-slot:hidden>
                    <input type="hidden" name="_method" :value="unit.id ? 'PUT' : 'POST'">
                </x-slot:hidden>
                <div class="grid grid-cols-1 gap-4">
                    <x-form.input :label="__('Name')" name="name" required maxlength="60" x-model="unit.name" :placeholder="__('e.g. Pit 2')" />
                    <div class="grid grid-cols-2 gap-4">
                        <x-form.select :label="__('Type')" name="type" required :placeholder="false" x-model="unit.type">
                            @foreach (StorageUnit::TYPES as $type)
                                <option value="{{ $type }}">{{ StorageUnit::typeLabel($type) }}</option>
                            @endforeach
                        </x-form.select>
                        <x-form.input :label="__('Capacity')" name="capacity_tonnes" type="number" min="0.1" step="0.1" required x-model="unit.capacity_tonnes" suffix="t" />
                    </div>
                    <label class="flex items-center gap-2 text-sm text-stone-700" x-show="unit.id">
                        <input type="checkbox" name="is_active" value="1" x-model="unit.is_active" class="size-4 rounded border-stone-300 text-brand-600 focus:ring-brand-500"> {{ __('In use') }}
                    </label>
                </div>
            </x-modal>
        @endif
    </div>
</x-layouts.app>
