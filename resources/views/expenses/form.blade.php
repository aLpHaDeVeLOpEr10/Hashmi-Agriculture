@php
    $editing = $expense->exists;
    $stageId = (int) old('season_stage_id', $expense->season_stage_id);
    $categoryId = old('category_id', $expense->category_id);
    $stage = $season->stages->firstWhere('id', $stageId);
    $stageTemplateId = ($stage ?? $season->stages->first())?->stage_template_id;
    // Stage template ids a category is limited to, as "1,3"; "" means every stage.
    $stagesAttr = fn (?array $ids) => $ids === null ? '' : implode(',', $ids ?: [0]);
    // A group is listed in a stage when any of its options is.
    $groupStages = function ($parent) {
        $sets = $parent->children->isEmpty() ? [$parent->effectiveStageIds()] : $parent->children->map->effectiveStageIds()->all();

        return in_array(null, $sets, true) ? null : array_values(array_unique(array_merge(...$sets)));
    };
    $initial = [
        'quantity' => old('quantity', $expense->quantity !== null ? (float) $expense->quantity : ''),
        'price' => old('unit_price', $expense->unit_price !== null ? (float) $expense->unit_price : ''),
        'amount' => old('amount', $expense->amount !== null ? (float) $expense->amount : ''),
        'unit' => old('unit_code', $expense->unit_code ?? ''),
    ];
    $initial['manual'] = $initial['amount'] !== '' && ! ($initial['quantity'] !== '' && $initial['price'] !== '' && abs((float) $initial['quantity'] * (float) $initial['price'] - (float) $initial['amount']) < 0.01);
@endphp

<x-layouts.app :title="$editing ? __('Edit expense') : __('Add expense')"
    :breadcrumbs="[__('Crop Seasons') => route('seasons.index'), $season->name => route('seasons.show', $season), $editing ? __('Edit expense') : __('Add expense')]">

    <form method="POST" action="{{ $editing ? route('expenses.update', $expense) : route('expenses.store') }}" enctype="multipart/form-data" novalidate
        x-data="validatedForm" x-on:submit="submit($event)" class="mx-auto max-w-3xl space-y-6"
        @unless ($editing) data-offline-form data-offline-label=":description · {{ $season->code }}" data-offline-message="{{ __('No connection. Saved on this phone and will sync automatically.') }}" @endunless>
        @csrf
        @if ($editing)
            @method('PUT')
        @endif
        <input type="hidden" name="action" value="save" x-ref="action">

        <x-card :title="$season->name.' · '.$season->code" :description="__('Every expense belongs to one stage of this season.')">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <x-form.select :label="__('Stage')" name="season_stage_id" required :placeholder="false"
                    x-on:change="$dispatch('stage-picked', Number($event.target.selectedOptions[0]?.dataset.template))">
                    @foreach ($season->stages as $option)
                        <option value="{{ $option->id }}" data-template="{{ $option->stage_template_id }}" @selected($option->id === $stageId)>{{ $loop->iteration }}. {{ $option->name() }}</option>
                    @endforeach
                </x-form.select>
                <x-form.input :label="__('Date')" name="expense_date" type="date" required :max="now()->addDays(30)->toDateString()" :value="old('expense_date', $expense->expense_date?->toDateString())" />
            </div>
        </x-card>

        <x-card :title="__('What was it for?')"
            x-data="{
                quantity: {{ Js::from($initial['quantity']) }},
                price: {{ Js::from($initial['price']) }},
                amount: {{ Js::from($initial['amount']) }},
                unit: {{ Js::from($initial['unit']) }},
                manual: {{ Js::from($initial['manual']) }},
                get computed() {
                    const q = parseFloat(this.quantity), p = parseFloat(this.price);
                    return Number.isFinite(q) && Number.isFinite(p) ? Math.round(q * p * 100) / 100 : null;
                },
                sync() { if (!this.manual && this.computed !== null) this.amount = this.computed; },
                pickCategory(event) { const unit = event.target.selectedOptions[0]?.dataset.unit; if (unit && !this.unit) this.unit = unit; },
                stage: {{ Js::from($stageTemplateId) }},
                keep: {{ Js::from($expense->category_id) }},
                inStage(stages, id = null) { return stages === '' || id === this.keep || stages.split(',').map(Number).includes(this.stage); },
                pickStage(stage) {
                    this.stage = stage;
                    const option = this.$refs.category.selectedOptions[0];
                    if (option?.value && !this.inStage(option.dataset.stages, Number(option.value))) this.$refs.category.value = '';
                },
            }"
            x-init="$watch('quantity', () => sync()); $watch('price', () => sync())"
            x-on:stage-picked.window="pickStage($event.detail)">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <x-form.select :label="__('Category')" name="category_id" required x-ref="category" x-on:change="pickCategory($event)"
                        :hint="__('Only categories used in the selected stage are listed.')">
                        @foreach ($categories as $parent)
                            <optgroup label="{{ __($parent->name) }}" x-show="inStage(@js($stagesAttr($groupStages($parent)))) || @js($parent->children->pluck('id')->push($parent->id)->all()).includes(keep)">
                                @foreach ($parent->children as $child)
                                    <option value="{{ $child->id }}" data-unit="{{ $child->default_unit }}" data-stages="{{ $stagesAttr($child->effectiveStageIds()) }}"
                                        x-bind:hidden="!inStage($el.dataset.stages, {{ $child->id }})" x-bind:disabled="!inStage($el.dataset.stages, {{ $child->id }})"
                                        @selected((string) $categoryId === (string) $child->id)>{{ __($child->name) }}</option>
                                @endforeach
                                @if ($parent->children->isEmpty())
                                    <option value="{{ $parent->id }}" data-unit="{{ $parent->default_unit }}" data-stages="{{ $stagesAttr($parent->effectiveStageIds()) }}"
                                        x-bind:hidden="!inStage($el.dataset.stages, {{ $parent->id }})" x-bind:disabled="!inStage($el.dataset.stages, {{ $parent->id }})"
                                        @selected((string) $categoryId === (string) $parent->id)>{{ __($parent->name) }}</option>
                                @endif
                            </optgroup>
                        @endforeach
                    </x-form.select>
                </div>
                <div class="sm:col-span-2">
                    <x-form.input :label="__('Description')" name="description" required maxlength="200" :placeholder="__('e.g. Diesel for ploughing')" :value="old('description', $expense->description)" />
                </div>

                <div class="grid grid-cols-2 gap-4 sm:col-span-2 sm:grid-cols-3">
                    <x-form.input :label="__('Quantity')" name="quantity" type="number" step="0.001" min="0" inputmode="decimal" x-model="quantity" />
                    <x-form.select :label="__('Unit')" name="unit_code" :placeholder="'—'" x-model="unit">
                        @foreach ($units as $unit)
                            <option value="{{ $unit->code }}">{{ __($unit->name) }}</option>
                        @endforeach
                    </x-form.select>
                    <div class="col-span-2 sm:col-span-1">
                        <x-form.input :label="__('Unit price')" name="unit_price" type="number" step="0.01" min="0" inputmode="decimal" x-model="price" :suffix="config('agri.currency')" />
                    </div>
                </div>

                <div class="sm:col-span-2">
                    <x-form.input :label="__('Amount')" name="amount" type="number" step="0.01" min="0.01" inputmode="decimal" required
                        x-model="amount" x-on:input="manual = true" :suffix="config('agri.currency')"
                        :hint="__('Fills itself from quantity × unit price. Type a different figure for discounts or rounding.')"
                        :error="__('Enter an amount, or a quantity and unit price.')" />
                    <button type="button" x-cloak x-show="manual && computed !== null && Number(amount) !== computed" x-on:click="manual = false; sync()"
                        class="mt-1.5 text-xs font-medium text-brand-700 hover:text-brand-800">
                        {{ __('Use calculated amount') }} (<span x-text="computed?.toLocaleString()"></span>)
                    </button>
                </div>
            </div>
        </x-card>

        <x-card :title="__('Details')" :description="__('Optional, but they make reports and payables more useful.')">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <x-form.select :label="__('Vendor')" name="vendor_id" :placeholder="'—'" :options="$vendors->pluck('name', 'id')->all()" :selected="old('vendor_id', $expense->vendor_id)" />
                <x-form.select :label="__('Machine used')" name="resource_id" :placeholder="'—'" :options="$machines->pluck('name', 'id')->all()" :selected="old('resource_id', $expense->resource_id)" />
                <x-form.select :label="__('Field')" name="field_id" :placeholder="'—'" :options="$fields->pluck('name', 'id')->all()" :selected="old('field_id', $expense->field_id)" />
            </div>

            <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
                <x-form.select :label="__('Documentation')" name="completeness" :placeholder="false"
                    :options="['complete' => __('Complete'), 'partial' => __('Partial – some details missing'), 'incomplete' => __('Incomplete – lump sum, no breakdown')]"
                    :selected="old('completeness', $expense->completeness ?? 'complete')"
                    :hint="__('Incomplete lines count towards the documentation gap in reports.')" />
                <div class="sm:col-span-2">
                    <x-toggle name="is_rework" :checked="(bool) old('is_rework', $expense->is_rework)" :label="__('Re-work')"
                        :description="__('Spent redoing work, e.g. re-ploughing or re-planting. Reported as avoidable re-work.')" />
                </div>
            </div>

            <div class="mt-4">
                <span class="mb-1.5 block text-sm font-medium text-stone-700">{{ __('Receipt photo') }}</span>
                @if ($editing && $expense->attachments->isNotEmpty())
                    <div class="mb-2 flex flex-wrap gap-2">
                        @foreach ($expense->attachments as $file)
                            <a href="{{ $file->url() }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 rounded-lg border border-stone-200 px-2.5 py-1.5 text-sm text-brand-700 hover:bg-stone-50">
                                <x-icon name="paperclip" class="size-4" /> {{ __('Receipt :n', ['n' => $loop->iteration]) }}
                            </a>
                        @endforeach
                    </div>
                @endif
                <label x-data="{ name: '' }" class="flex cursor-pointer items-center gap-3 rounded-lg border border-dashed border-stone-300 px-4 py-3 text-sm text-stone-600 hover:border-brand-400 hover:bg-brand-50/40">
                    <x-icon name="camera" class="size-5 text-stone-400" />
                    <span x-text="name || @js(__('Take a photo or choose a file (JPG, PNG, PDF · max 8 MB)'))"></span>
                    <input type="file" name="receipt" accept="image/*,application/pdf" capture="environment" class="sr-only" x-on:change="name = $event.target.files[0]?.name ?? ''">
                </label>
                @error('receipt')
                    <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </x-card>

        @if (! $editing && auth()->user()->can('record-payments'))
            <x-card :title="__('Payment')" :description="__('Unpaid bills wait in Payables until they are settled.')" x-data="{ option: {{ Js::from(old('payment_option', 'unpaid')) }} }">
                <div class="grid grid-cols-1 gap-2 sm:grid-cols-3" role="radiogroup">
                    @foreach (['unpaid' => __('Unpaid'), 'full' => __('Paid in full'), 'partial' => __('Paid partly')] as $value => $label)
                        <label class="flex cursor-pointer items-center gap-2.5 rounded-lg border px-3 py-2.5 text-sm transition-colors" :class="option === @js($value) ? 'border-brand-500 bg-brand-50 text-brand-900' : 'border-stone-200 text-stone-700 hover:bg-stone-50'">
                            <input type="radio" name="payment_option" value="{{ $value }}" x-model="option" class="size-4 border-stone-300 text-brand-600 focus:ring-brand-500">
                            {{ $label }}
                        </label>
                    @endforeach
                </div>
                <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-3" x-show="option !== 'unpaid'" x-cloak>
                    <div x-show="option === 'partial'">
                        <x-form.input :label="__('Amount paid')" name="paid_amount" type="number" step="0.01" min="0.01" x-bind:required="option === 'partial'" x-bind:disabled="option !== 'partial'" :value="old('paid_amount')" :suffix="config('agri.currency')" />
                    </div>
                    <x-form.select :label="__('Method')" name="payment_method" :placeholder="false" x-bind:disabled="option === 'unpaid'" :options="collect(\App\Models\Payment::METHODS)->mapWithKeys(fn ($m) => [$m => __(ucfirst($m))])->all()" :selected="old('payment_method', 'cash')" />
                    <x-form.input :label="__('Reference')" name="payment_reference" maxlength="60" x-bind:disabled="option === 'unpaid'" :value="old('payment_reference')" :placeholder="__('Optional')" />
                </div>
            </x-card>
        @elseif ($editing && $expense->status)
            <x-card :title="__('Payment')">
                <div class="flex flex-wrap items-center gap-3 text-sm">
                    <x-status kind="payment" :value="$expense->status->payment_status" />
                    <span class="text-stone-600">{{ __('Paid :paid of :amount', ['paid' => \App\Support\Money::format($expense->status->paid), 'amount' => \App\Support\Money::format($expense->status->amount)]) }}</span>
                    <a href="{{ route('payables.index') }}" class="font-medium text-brand-700 hover:text-brand-800">{{ __('Open payables') }}</a>
                </div>
                @if ($expense->allocations->isNotEmpty())
                    <p class="mt-2 text-xs text-stone-500">{{ __('If you lower the amount below what has been paid, remove the payment in Payables first.') }}</p>
                @endif
            </x-card>
        @endif

        <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
            <x-button variant="secondary" :href="$stage ? route('stages.show', [$season, $stage]) : route('seasons.show', $season)">{{ __('Cancel') }}</x-button>
            @unless ($editing)
                <x-button type="submit" variant="secondary" icon="copy-plus" x-on:click="$refs.action.value = 'another'" x-bind:disabled="busy">{{ __('Save & add another') }}</x-button>
            @endunless
            <x-button type="submit" icon="check" x-on:click="$refs.action.value = 'save'" x-bind:disabled="busy">{{ $editing ? __('Save changes') : __('Save expense') }}</x-button>
        </div>
    </form>
</x-layouts.app>
