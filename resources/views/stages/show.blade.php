@use('App\Support\Money')
@use('App\Models\SeasonStage')

@php
    $open = ! $season->isClosed();
    $canEdit = $open && auth()->user()->can('edit-expenses');
    $canPay = auth()->user()->can('record-payments');
    $index = $season->stages->search(fn ($s) => $s->id === $stage->id);
    $prev = $season->stages->get($index - 1);
    $next = $season->stages->get($index + 1);
@endphp

<x-layouts.app :title="$stage->name()" :breadcrumbs="[__('Crop Seasons') => route('seasons.index'), $season->name => route('seasons.show', $season), $stage->name()]">
    <section class="rounded-xl border border-stone-200 bg-white p-5 shadow-card sm:p-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <p class="text-sm text-stone-500">{{ __('Stage :n of :total', ['n' => $index + 1, 'total' => $season->stages->count()]) }} · <a href="{{ route('seasons.show', $season) }}" class="hover:text-brand-700">{{ $season->name }} ({{ $season->code }})</a></p>
                <div class="mt-1 flex flex-wrap items-center gap-2">
                    <h2 class="text-xl font-semibold text-stone-900">{{ $stage->name() }}</h2>
                    <x-status kind="stage" :value="$stage->status" />
                </div>
                @if ($stage->start_date)
                    <p class="mt-1 text-sm text-stone-500">{{ $stage->start_date->translatedFormat('d M Y') }} @if ($stage->end_date) → {{ $stage->end_date->translatedFormat('d M Y') }} @endif</p>
                @endif
            </div>
            <div class="flex flex-wrap items-center gap-2">
                @if ($open)
                    @can('manage-seasons')
                        <form method="POST" action="{{ route('stages.status', [$season, $stage]) }}" class="flex items-center gap-2">
                            @csrf @method('PATCH')
                            <select name="status" onchange="this.form.submit()" class="form-control h-9 w-auto py-0 pe-8" aria-label="{{ __('Stage status') }}">
                                @foreach (SeasonStage::STATUSES as $status)
                                    <option value="{{ $status }}" @selected($stage->status === $status)>{{ SeasonStage::statusLabel($status) }}</option>
                                @endforeach
                            </select>
                        </form>
                    @endcan
                    @can('enter-expenses')
                        <x-button icon="plus" :href="route('expenses.create', [$season, 'stage' => $stage->id])">{{ __('Expense') }}</x-button>
                    @endcan
                @endif
            </div>
        </div>

        <dl class="mt-6 grid grid-cols-2 gap-4 border-t border-stone-100 pt-5 md:grid-cols-4">
            <div>
                <dt class="text-xs text-stone-500">{{ __('Stage total') }}</dt>
                <dd class="mt-1 text-2xl font-semibold text-stone-900 tabular-nums">{{ Money::format($stage->totals?->total) }}</dd>
            </div>
            <div>
                <dt class="text-xs text-stone-500">{{ __('Share of season') }}</dt>
                <dd class="mt-1 text-lg font-semibold text-stone-900 tabular-nums">
                    {{ $season->summary?->total_cost > 0 ? round($stage->totals?->total / $season->summary->total_cost * 100, 1) : 0 }}%
                </dd>
            </div>
            <div>
                <dt class="text-xs text-stone-500">{{ __('Paid') }}</dt>
                <dd class="mt-1 text-lg font-semibold text-stone-900 tabular-nums">{{ Money::format($stage->totals?->paid) }}</dd>
            </div>
            <div>
                <dt class="text-xs text-stone-500">{{ __('Balance') }}</dt>
                <dd @class(['mt-1 text-lg font-semibold tabular-nums', 'text-red-700' => $stage->totals?->balance > 0, 'text-stone-900' => ! ($stage->totals?->balance > 0)])>{{ Money::format($stage->totals?->balance) }}</dd>
            </div>
        </dl>
    </section>

    <div class="mt-6 grid grid-cols-1 gap-6 2xl:grid-cols-4">
        <x-card :title="__('Expenses')" :description="trans_choice(':count line|:count lines', $expenses->count())" flush class="2xl:col-span-3">
            @if ($expenses->isEmpty())
                <x-empty-state icon="receipt" :title="__('No expenses in this stage yet')" :description="__('Each expense you add here counts towards the stage and season totals.')">
                    @if ($open)
                        @can('enter-expenses')
                            <x-button icon="plus" :href="route('expenses.create', [$season, 'stage' => $stage->id])">{{ __('Add expense') }}</x-button>
                        @endcan
                    @endif
                </x-empty-state>
            @else
                <x-table min-width="min-w-[960px]">
                    <x-slot:head>
                        <tr>
                            <th>{{ __('Date') }}</th>
                            <th>{{ __('Category') }}</th>
                            <th>{{ __('Description') }}</th>
                            <th class="text-end">{{ __('Qty × rate') }}</th>
                            <th class="text-end">{{ __('Amount') }}</th>
                            <th>{{ __('Payment') }}</th>
                            <th class="text-end"><span class="sr-only">{{ __('Actions') }}</span></th>
                        </tr>
                    </x-slot:head>
                    @foreach ($expenses as $expense)
                        <tr>
                            <td class="whitespace-nowrap">{{ $expense->expense_date->translatedFormat('d M Y') }}</td>
                            <td class="whitespace-nowrap">
                                <span class="block text-stone-800">{{ __($expense->category->name) }}</span>
                                @if ($expense->category->parent)
                                    <span class="block text-xs text-stone-500">{{ __($expense->category->parent->name) }}</span>
                                @endif
                            </td>
                            <td class="min-w-64">
                                <span class="block font-medium text-stone-900">{{ $expense->description }}</span>
                                <span class="mt-0.5 flex flex-wrap gap-x-3 text-xs text-stone-500">
                                    @if ($expense->vendor)<span class="inline-flex items-center gap-1"><x-icon name="store" class="size-3.5" /> {{ $expense->vendor->name }}</span>@endif
                                    @if ($expense->machine)<span class="inline-flex items-center gap-1"><x-icon name="tractor" class="size-3.5" /> {{ $expense->machine->name }}</span>@endif
                                    @if ($expense->field)<span class="inline-flex items-center gap-1"><x-icon name="map-pin" class="size-3.5" /> {{ $expense->field->name }}</span>@endif
                                    @if ($expense->shared_cost_id)<span class="inline-flex items-center gap-1"><x-icon name="split" class="size-3.5" /> {{ __('Shared cost') }}</span>@endif
                                    @foreach ($expense->attachments as $file)
                                        <a href="{{ $file->url() }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1 text-brand-700 hover:text-brand-800"><x-icon name="paperclip" class="size-3.5" /> {{ __('Receipt') }}</a>
                                    @endforeach
                                </span>
                            </td>
                            <td class="text-end whitespace-nowrap text-stone-600 tabular-nums">
                                @if ($expense->quantity !== null && $expense->unit_price !== null)
                                    {{ Money::quantity($expense->quantity) }} {{ __($expense->unit?->name ?? '') }} × {{ Money::quantity($expense->unit_price) }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="text-end font-semibold whitespace-nowrap text-stone-900 tabular-nums">{{ Money::format($expense->amount) }}</td>
                            <td>
                                <x-status kind="payment" :value="$expense->status?->payment_status" />
                                @if ($expense->status?->payment_status === 'partial')
                                    <span class="mt-0.5 block text-xs text-stone-500 tabular-nums">{{ __(':amount due', ['amount' => Money::number($expense->status->balance)]) }}</span>
                                @endif
                            </td>
                            <td class="text-end whitespace-nowrap">
                                @if ($canPay && $expense->status?->balance > 0)
                                    <x-button variant="soft" size="sm" x-on:click="$dispatch('pay-expense', { url: {{ Js::from(route('expenses.pay', $expense)) }}, label: {{ Js::from($expense->description) }}, balance: {{ (float) $expense->status->balance }} })">{{ __('Pay') }}</x-button>
                                @endif
                                @if ($canEdit)
                                    <x-button variant="ghost" size="icon" :href="route('expenses.edit', $expense)" :aria-label="__('Edit')"><x-icon name="pencil" class="size-4" /></x-button>
                                    <x-button variant="ghost" size="icon" :aria-label="__('Delete')"
                                        x-on:click="$dispatch('confirm-delete', { url: {{ Js::from(route('expenses.destroy', $expense)) }}, label: {{ Js::from($expense->description.' · '.Money::format($expense->amount)) }} })">
                                        <x-icon name="trash-2" class="size-4 text-red-500" />
                                    </x-button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </x-table>
            @endif
        </x-card>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 2xl:grid-cols-1 2xl:content-start">
            <x-card :title="__('By category')">
                @forelse ($subtotals as $row)
                    <div @class(['flex items-baseline justify-between gap-3 text-sm', 'mt-3' => ! $loop->first])>
                        <span class="min-w-0">
                            <span class="block truncate text-stone-800">{{ __($row->name) }}</span>
                            <span class="block text-xs text-stone-500">{{ trans_choice(':count line|:count lines', $row->lines) }}</span>
                        </span>
                        <span class="font-medium text-stone-900 tabular-nums">{{ Money::number($row->total) }}</span>
                    </div>
                @empty
                    <p class="text-sm text-stone-500">{{ __('Nothing recorded yet.') }}</p>
                @endforelse
            </x-card>

            <nav class="flex flex-col gap-2 text-sm" aria-label="{{ __('Stage navigation') }}">
                @if ($prev)
                    <a href="{{ route('stages.show', [$season, $prev]) }}" class="flex items-center gap-2 rounded-lg border border-stone-200 bg-white px-3 py-2.5 text-stone-600 hover:bg-stone-50 hover:text-stone-900">
                        <x-icon name="arrow-left" class="size-4 rtl:-scale-x-100" /> <span class="truncate">{{ $prev->name() }}</span>
                    </a>
                @endif
                @if ($next)
                    <a href="{{ route('stages.show', [$season, $next]) }}" class="flex items-center justify-between gap-2 rounded-lg border border-stone-200 bg-white px-3 py-2.5 text-stone-600 hover:bg-stone-50 hover:text-stone-900">
                        <span class="truncate">{{ $next->name() }}</span> <x-icon name="arrow-right" class="size-4 rtl:-scale-x-100" />
                    </a>
                @endif
            </nav>
        </div>
    </div>

    @if ($canPay)
        <div x-data="{ url: '', label: '', balance: 0 }" x-on:pay-expense.window="url = $event.detail.url; label = $event.detail.label; balance = $event.detail.balance; $dispatch('open-modal', 'pay-expense')">
            <x-modal name="pay-expense" :title="__('Record payment')" size="sm" bind-action="url" :submit="__('Save payment')">
                <p class="mb-4 text-sm text-stone-600"><span class="font-medium text-stone-900" x-text="label"></span> · {{ __('due') }} <span class="font-semibold tabular-nums" x-text="balance.toLocaleString()"></span></p>
                <div class="grid grid-cols-1 gap-4">
                    <x-form.input :label="__('Amount paid')" name="amount" type="number" step="0.01" min="0.01" required x-bind:max="balance" x-bind:value="balance" :suffix="config('agri.currency')" />
                    <div class="grid grid-cols-2 gap-4">
                        <x-form.input :label="__('Date')" name="payment_date" type="date" required :value="now()->toDateString()" :max="now()->toDateString()" />
                        <x-form.select :label="__('Method')" name="method" required :placeholder="false" :options="collect(\App\Models\Payment::METHODS)->mapWithKeys(fn ($m) => [$m => __(ucfirst($m))])->all()" />
                    </div>
                    <x-form.input :label="__('Reference')" name="reference" maxlength="60" :placeholder="__('Cheque no., transaction ID…')" />
                </div>
            </x-modal>
        </div>
    @endif

    @if ($canEdit)
        <div x-data="{ url: '', label: '' }" x-on:confirm-delete.window="url = $event.detail.url; label = $event.detail.label; $dispatch('open-modal', 'delete-expense')">
            <x-modal name="delete-expense" :title="__('Delete expense?')" size="sm" bind-action="url" method="DELETE" :submit="__('Delete')">
                <p class="text-sm text-stone-600">{{ __('This removes the line from all totals. It stays in the audit history.') }}</p>
                <p class="mt-3 rounded-lg bg-stone-50 px-3 py-2 text-sm font-medium text-stone-800" x-text="label"></p>
            </x-modal>
        </div>
    @endif
</x-layouts.app>
