@use('App\Support\Money')

@php
    $canManage = auth()->user()->can('manage-seasons');
    $canConfirm = auth()->user()->can('edit-expenses');
    $blank = ['season_stage_id' => '', 'category_id' => '', 'vendor_id' => '', 'description' => '', 'amount' => '', 'frequency' => 'monthly', 'start_date' => now()->toDateString(), 'end_date' => '', 'is_active' => true];
@endphp

<x-layouts.app :title="__('Recurring costs')" :breadcrumbs="[__('Recurring costs')]">
    <div x-data="{ rule: {{ Js::from($blank) }} }">
        <x-page-header :description="__('Monthly or weekly costs such as tubewell electricity or a permanent labourer’s wage. Each period a draft appears here; nothing is posted until someone confirms it, because real bills vary.')">
            <x-slot:actions>
                @if ($canManage && $seasons->isNotEmpty())
                    <x-button icon="plus" x-on:click="rule = {{ Js::from($blank) }}; $dispatch('open-modal', 'rule')">{{ __('New recurring cost') }}</x-button>
                @endif
            </x-slot:actions>
        </x-page-header>

        <x-card :title="__('Drafts to confirm')" :description="__('Check the amount on this period’s bill, then confirm or skip.')" flush>
            @forelse ($due as $item)
                <form method="POST" action="{{ route('recurring.confirm', $item) }}" class="grid grid-cols-1 gap-4 border-b border-stone-100 px-5 py-4 last:border-0 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-end" x-data="validatedForm" x-on:submit="submit($event)" novalidate>
                    @csrf
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-[minmax(0,2fr)_minmax(0,1fr)_minmax(0,1fr)]">
                        <div>
                            <x-form.input :label="__('Description')" name="description" :value="$service->periodDescription($item)" maxlength="200" />
                            <p class="mt-1 text-xs text-stone-500">{{ $item->stage->season->code }} · {{ $item->stage->name() }} · {{ __($item->category->name) }}@if ($item->vendor) · {{ $item->vendor->name }}@endif</p>
                        </div>
                        <x-form.input :label="__('Amount')" name="amount" type="number" step="0.01" min="0.01" required :value="(float) $item->amount" :suffix="config('agri.currency')" />
                        <x-form.input :label="__('Bill date')" name="expense_date" type="date" required :value="$item->next_run_date->toDateString()" />
                    </div>
                    @if ($canConfirm)
                        <div class="flex gap-2 lg:pb-6">
                            <x-button type="submit" variant="secondary" formaction="{{ route('recurring.skip', $item) }}" formnovalidate>{{ __('Skip') }}</x-button>
                            <x-button type="submit" icon="check">{{ __('Confirm') }}</x-button>
                        </div>
                    @endif
                </form>
            @empty
                <x-empty-state icon="circle-check" :title="__('Nothing to confirm')" :description="__('Drafts appear here when a recurring cost’s next period arrives.')" />
            @endforelse
        </x-card>

        <x-card :title="__('Recurring cost rules')" flush class="mt-6">
            @if ($rules->isEmpty())
                <x-empty-state icon="repeat" :title="__('No recurring costs')" :description="__('Set one up on a stage, for example monthly electricity during crop growing.')" />
            @else
                <x-table min-width="min-w-[900px]">
                    <x-slot:head>
                        <tr>
                            <th>{{ __('Description') }}</th>
                            <th>{{ __('Season / stage') }}</th>
                            <th>{{ __('Every') }}</th>
                            <th class="text-end">{{ __('Amount') }}</th>
                            <th>{{ __('Next draft') }}</th>
                            <th>{{ __('Ends') }}</th>
                            <th></th>
                        </tr>
                    </x-slot:head>
                    @foreach ($rules as $item)
                        <tr @class(['opacity-60' => ! $item->is_active])>
                            <td>
                                <span class="block font-medium text-stone-900">{{ $item->description }}</span>
                                <span class="block text-xs text-stone-500">{{ __($item->category->name) }}@if ($item->vendor) · {{ $item->vendor->name }}@endif</span>
                            </td>
                            <td class="whitespace-nowrap">{{ $item->stage->season->code }} <span class="block text-xs text-stone-500">{{ $item->stage->name() }}</span></td>
                            <td>{{ $item->frequency === 'monthly' ? __('Month') : __('Week') }}</td>
                            <td class="text-end font-semibold whitespace-nowrap tabular-nums">{{ Money::format($item->amount) }}</td>
                            <td class="whitespace-nowrap">
                                @if ($item->is_active)
                                    {{ $item->next_run_date->translatedFormat('d M Y') }}
                                @else
                                    <x-badge tone="stone">{{ __('Inactive') }}</x-badge>
                                @endif
                            </td>
                            <td class="whitespace-nowrap">{{ $item->end_date?->translatedFormat('d M Y') ?? '—' }}</td>
                            <td class="text-end">
                                @if ($canManage && ! $item->stage->season->isClosed())
                                    <x-button variant="ghost" size="sm" icon="pencil"
                                        x-on:click="rule = {{ Js::from(['id' => $item->id, 'season_stage_id' => $item->season_stage_id, 'category_id' => $item->category_id, 'vendor_id' => $item->vendor_id ?? '', 'description' => $item->description, 'amount' => (float) $item->amount, 'frequency' => $item->frequency, 'start_date' => $item->start_date->toDateString(), 'end_date' => $item->end_date?->toDateString() ?? '', 'is_active' => $item->is_active]) }}; $dispatch('open-modal', 'rule')">{{ __('Edit') }}</x-button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </x-table>
            @endif
        </x-card>

        @if ($canManage)
            <x-modal name="rule" :title="__('New recurring cost')" title-bind="rule.id ? {{ Js::from(__('Edit recurring cost')) }} : {{ Js::from(__('New recurring cost')) }}" size="md"
                bind-action="rule.id ? {{ Js::from(url('/recurring')) }} + '/' + rule.id : {{ Js::from(url('/recurring')) }}">
                <x-slot:hidden>
                    <input type="hidden" name="_method" :value="rule.id ? 'PUT' : 'POST'">
                </x-slot:hidden>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <x-form.input :label="__('Description')" name="description" required maxlength="200" x-model="rule.description" :placeholder="__('e.g. Tubewell electricity')" />
                    </div>
                    <div class="sm:col-span-2">
                        <x-stage-select :seasons="$seasons" model="rule.season_stage_id" />
                    </div>
                    <x-form.select :label="__('Category')" name="category_id" required x-model="rule.category_id">
                        @foreach ($categories as $parent)
                            <optgroup label="{{ __($parent->name) }}">
                                @foreach ($parent->children as $child)
                                    <option value="{{ $child->id }}">{{ __($child->name) }}</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </x-form.select>
                    <x-form.select :label="__('Vendor')" name="vendor_id" :placeholder="'—'" :options="$vendors->pluck('name', 'id')->all()" x-model="rule.vendor_id" />
                    <x-form.input :label="__('Usual amount')" name="amount" type="number" step="0.01" min="0.01" required x-model="rule.amount" :suffix="config('agri.currency')" />
                    <x-form.select :label="__('Every')" name="frequency" required :placeholder="false" x-model="rule.frequency" :options="['monthly' => __('Month'), 'weekly' => __('Week')]" />
                    <x-form.input :label="__('First draft on')" name="start_date" type="date" required x-model="rule.start_date" />
                    <x-form.input :label="__('Ends on')" name="end_date" type="date" x-model="rule.end_date" :hint="__('Optional')" />
                    <label class="flex items-center gap-2 text-sm text-stone-700 sm:col-span-2" x-show="rule.id">
                        <input type="checkbox" name="is_active" value="1" x-model="rule.is_active" class="size-4 rounded border-stone-300 text-brand-600 focus:ring-brand-500"> {{ __('Active') }}
                    </label>
                </div>
            </x-modal>
        @endif
    </div>
</x-layouts.app>
