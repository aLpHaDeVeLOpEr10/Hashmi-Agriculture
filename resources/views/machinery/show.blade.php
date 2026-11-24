@use('App\Support\Money')

@php
    $filtered = array_filter($filters);
    $canEditAny = auth()->user()->can('edit-expenses');
    $basisLabels = ['hours' => __('By machine hours'), 'acres' => __('By acres worked'), 'equal' => __('Equally'), 'manual' => __('Manual percentages')];
@endphp

<x-layouts.app :title="$machine->name" :breadcrumbs="[__('Machinery') => route('machinery.index'), $machine->name]">
    <section class="rounded-xl border border-stone-200 bg-white p-5 shadow-card sm:p-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <span class="grid size-12 place-items-center rounded-xl bg-stone-100 text-stone-600"><x-icon :name="$machine->type === 'pump' ? 'droplets' : 'tractor'" class="size-6" /></span>
                <div>
                    <h1 class="text-xl font-semibold text-stone-900">{{ $machine->name }}</h1>
                    <p class="text-sm text-stone-500">
                        {{ __(ucfirst($machine->type)) }} · {{ __(ucfirst($machine->ownership)) }}@if ($machine->internal_rate_per_hour) · {{ __(':rate/h internal', ['rate' => Money::number($machine->internal_rate_per_hour)]) }}@endif
                        @unless ($machine->is_active) · <span class="font-medium text-stone-700">{{ __('Inactive') }}</span>@endunless
                    </p>
                </div>
            </div>
            <x-button variant="secondary" icon="arrow-left" :href="route('machinery.index')">{{ __('All machines') }}</x-button>
        </div>
    </section>

    <div id="machine-summary" class="mt-6 grid grid-cols-2 gap-3 lg:grid-cols-5">
        <x-kpi-tile :label="__('Running cost')" :value="Money::format($summary->amount)" icon="wallet" />
        <x-kpi-tile :label="__('Bills')" :value="$summary->count" icon="receipt" tone="stone"
            :caption="$summary->first ? $summary->first->translatedFormat('d M Y').' – '.$summary->last->translatedFormat('d M Y') : null" />
        <x-kpi-tile :label="__('Paid')" :value="Money::format($summary->paid)" icon="circle-check" tone="sky" />
        <x-kpi-tile :label="__('Outstanding')" :value="Money::format($summary->balance)" icon="clock" :tone="$summary->balance > 0 ? 'rose' : 'stone'" />
        <x-kpi-tile :label="__('Fuel bought')" :value="$summary->fuel > 0 ? Money::number($summary->fuel).' L' : '—'" icon="fuel" tone="amber" class="col-span-2 lg:col-span-1" />
    </div>

    <x-card id="bills" :title="__('Bills')" :description="__('Every expense tagged with this machine, newest first')" flush class="mt-6 scroll-mt-20">
        <form method="GET" action="{{ route('machinery.machines.show', $machine) }}#bills" data-pager-form data-pager-targets="machine-summary bills by-category" class="flex flex-wrap items-center gap-2 border-b border-stone-100 px-5 py-3">
            <select name="season" onchange="this.form.requestSubmit()" class="form-control h-9 w-full py-0 pe-8 sm:w-auto" aria-label="{{ __('Season') }}">
                <option value="">{{ __('All seasons') }}</option>
                @foreach ($seasons as $s)
                    <option value="{{ $s->id }}" @selected($filters['season'] === $s->id)>{{ $s->name }}</option>
                @endforeach
            </select>
            <select name="category" onchange="this.form.requestSubmit()" class="form-control h-9 w-full py-0 pe-8 sm:w-auto" aria-label="{{ __('Category') }}">
                <option value="">{{ __('All categories') }}</option>
                @foreach ($categories as $c)
                    <option value="{{ $c->id }}" @selected($filters['category'] === $c->id)>{{ $c->parent ? __($c->parent->name).' → ' : '' }}{{ __($c->name) }}</option>
                @endforeach
            </select>
            <select name="status" onchange="this.form.requestSubmit()" class="form-control h-9 w-full py-0 pe-8 sm:w-auto" aria-label="{{ __('Payment') }}">
                <option value="">{{ __('Any payment status') }}</option>
                @foreach (['unpaid' => __('Unpaid'), 'partial' => __('Partial'), 'paid' => __('Paid')] as $value => $label)
                    <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <noscript><x-button type="submit" variant="secondary" size="sm">{{ __('Filter') }}</x-button></noscript>
            @if ($filtered)
                <a href="{{ route('machinery.machines.show', $machine) }}#bills" data-pager-link data-pager-targets="machine-summary bills by-category" class="text-sm font-medium text-brand-700 hover:text-brand-800">{{ __('Clear filters') }}</a>
            @endif
        </form>

        @if ($bills->isEmpty())
            <x-empty-state icon="receipt" :title="$filtered ? __('No bills match these filters') : __('No bills for this machine yet')"
                :description="$filtered ? __('Try another season, category or payment status.') : __('When you add an expense, choose this machine in the Machine field and it will appear here.')" />
        @else
            <x-table min-width="min-w-[980px]">
                <x-slot:head>
                    <tr>
                        <th class="w-12 text-end">#</th>
                        <th>{{ __('Date') }}</th>
                        <th>{{ __('Season · Stage') }}</th>
                        <th>{{ __('Category') }}</th>
                        <th>{{ __('Description') }}</th>
                        <th class="text-end">{{ __('Quantity') }}</th>
                        <th class="text-end">{{ __('Amount') }}</th>
                        <th>{{ __('Payment') }}</th>
                        <th class="text-end"><span class="sr-only">{{ __('Actions') }}</span></th>
                    </tr>
                </x-slot:head>
                @foreach ($bills as $expense)
                    <tr>
                        <td class="w-12 text-end text-stone-400 tabular-nums">{{ $bills->firstItem() + $loop->index }}</td>
                        <td class="whitespace-nowrap">{{ $expense->expense_date->translatedFormat('d M Y') }}</td>
                        <td class="whitespace-nowrap">
                            @if ($expense->season)
                                <a href="{{ route('seasons.show', $expense->season) }}" class="block text-stone-800 hover:text-brand-700">{{ $expense->season->name }}</a>
                            @endif
                            @if ($expense->stage)
                                <a href="{{ route('stages.show', [$expense->season, $expense->stage]) }}" class="block text-xs text-stone-500 hover:text-brand-700">{{ $expense->stage->name() }}</a>
                            @endif
                        </td>
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
                            @if ($canEditAny && $expense->season && ! $expense->season->isClosed())
                                <x-button variant="ghost" size="icon" :href="route('expenses.edit', $expense)" :aria-label="__('Edit')"><x-icon name="pencil" class="size-4" /></x-button>
                            @endif
                        </td>
                    </tr>
                @endforeach
                <tr class="bg-stone-50 font-semibold">
                    <td colspan="6" class="text-stone-700">{{ trans_choice(':count bill|:count bills', $summary->count, ['count' => $summary->count]) }}@if ($filtered) · {{ __('filtered') }}@endif</td>
                    <td class="text-end whitespace-nowrap text-stone-900 tabular-nums">{{ Money::format($summary->amount) }}</td>
                    <td colspan="2" class="text-xs font-normal whitespace-nowrap text-stone-500 tabular-nums">
                        @if ($summary->balance > 0){{ __(':amount due', ['amount' => Money::number($summary->balance)]) }}@endif
                    </td>
                </tr>
            </x-table>
            <x-pagination :paginator="$bills" />
        @endif
    </x-card>

    <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-2">
        <x-card id="by-category" :title="__('Cost by category')" :description="$filtered ? __('For the filtered bills') : __('Where this machine\'s money goes')" flush>
            @if ($byCategory->isEmpty())
                <x-empty-state icon="chart-pie" :title="__('Nothing to break down yet')" />
            @else
                <ul class="divide-y divide-stone-100">
                    @foreach ($byCategory as $row)
                        @php $share = $summary->amount > 0 ? $row->amount / $summary->amount * 100 : 0; @endphp
                        <li class="px-5 py-3">
                            <div class="flex items-baseline justify-between gap-3 text-sm">
                                <span class="min-w-0 truncate text-stone-800">
                                    {{ __($row->category->name) }}
                                    @if ($row->category->parent)<span class="text-xs text-stone-500">· {{ __($row->category->parent->name) }}</span>@endif
                                </span>
                                <span class="shrink-0 font-semibold text-stone-900 tabular-nums">{{ Money::format($row->amount) }}</span>
                            </div>
                            <div class="mt-1.5 flex items-center gap-3">
                                <x-progress :value="$share" class="flex-1" />
                                <span class="w-24 shrink-0 text-end text-xs text-stone-500 tabular-nums">{{ trans_choice(':count bill|:count bills', $row->count, ['count' => $row->count]) }} · {{ Money::number($share, 0) }}%</span>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-card>

        <x-card :title="__('Hours logged')" :description="__('Work done by this machine')" flush>
            @if ($usage->isEmpty())
                <x-empty-state icon="clock" :title="__('No hours logged')" :description="__('Use Log hours on the Machinery page to record hours, acres and fuel used.')" />
            @else
                <x-table min-width="min-w-[520px]">
                    <x-slot:head>
                        <tr>
                            <th>{{ __('Date') }}</th>
                            <th>{{ __('Season · Stage') }}</th>
                            <th class="text-end">{{ __('Hours') }}</th>
                            <th class="text-end">{{ __('Acres') }}</th>
                            <th class="text-end">{{ __('Fuel') }}</th>
                        </tr>
                    </x-slot:head>
                    @foreach ($usage as $log)
                        <tr>
                            <td class="whitespace-nowrap">{{ $log->usage_date->translatedFormat('d M Y') }}</td>
                            <td>
                                <span class="block text-stone-800">{{ $log->stage?->season?->name }}</span>
                                <span class="block text-xs text-stone-500">{{ $log->stage?->name() }}@if ($log->operator) · {{ $log->operator }}@endif</span>
                            </td>
                            <td class="text-end tabular-nums">{{ Money::quantity($log->hours ?? 0) }}</td>
                            <td class="text-end tabular-nums">{{ Money::quantity($log->acres ?? 0) }}</td>
                            <td class="text-end tabular-nums">{{ $log->fuel_litres ? Money::number($log->fuel_litres).' L' : '—' }}</td>
                        </tr>
                    @endforeach
                </x-table>
            @endif
        </x-card>
    </div>

    @if ($sharedCosts->isNotEmpty())
        <x-card :title="__('Shared costs')" :description="__('Repairs and parts for this machine, split across seasons')" flush class="mt-6">
            <x-table min-width="min-w-[640px]">
                <x-slot:head>
                    <tr>
                        <th>{{ __('Date') }}</th>
                        <th>{{ __('Description') }}</th>
                        <th>{{ __('Split') }}</th>
                        <th class="text-end">{{ __('Amount') }}</th>
                        <th>{{ __('Status') }}</th>
                    </tr>
                </x-slot:head>
                @foreach ($sharedCosts as $cost)
                    <tr>
                        <td class="whitespace-nowrap">{{ $cost->cost_date->translatedFormat('d M Y') }}</td>
                        <td>
                            <a href="{{ route('machinery.shared.show', $cost) }}" class="block font-medium text-stone-900 hover:text-brand-700">{{ $cost->description }}</a>
                            <span class="block text-xs text-stone-500">{{ $cost->category->fullName() }}@if ($cost->vendor) · {{ $cost->vendor->name }}@endif</span>
                        </td>
                        <td class="whitespace-nowrap">{{ $basisLabels[$cost->basis] ?? $cost->basis }}</td>
                        <td class="text-end font-semibold whitespace-nowrap tabular-nums">{{ Money::format($cost->total_amount) }}</td>
                        <td><x-badge :tone="$cost->status === 'allocated' ? 'green' : 'amber'">{{ $cost->status === 'allocated' ? __('Split') : __('Draft') }}</x-badge></td>
                    </tr>
                @endforeach
            </x-table>
        </x-card>
    @endif
</x-layouts.app>
