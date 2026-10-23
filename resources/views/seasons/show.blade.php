@use('App\Support\Money')
@use('App\Models\CropSeason')

@php
    $total = (float) ($season->summary?->total_cost ?? 0);
    $canEdit = auth()->user()->can('manage-seasons') && ! $season->isClosed();
@endphp

<x-layouts.app :title="$season->name" :breadcrumbs="[__('Crop Seasons') => route('seasons.index'), $season->name]">
    <section class="rounded-xl border border-stone-200 bg-white p-5 shadow-card sm:p-6">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="rounded bg-stone-100 px-2 py-0.5 font-mono text-xs text-stone-600">{{ $season->code }}</span>
                    <x-status kind="season" :value="$season->status" />
                </div>
                <h2 class="mt-2 text-xl font-semibold text-stone-900">{{ $season->name }} · {{ $season->crop->name }}</h2>
                <p class="mt-1 text-sm text-stone-500">
                    {{ $season->start_date->translatedFormat('d M Y') }}
                    @if ($season->expected_end_date) → {{ $season->expected_end_date->translatedFormat('d M Y') }} @endif
                    · {{ $season->fields->map(fn ($f) => $f->name.' ('.Money::number($f->pivot->area_acres, 2).' ac)')->join(', ') }}
                </p>
                @if ($season->notes)
                    <p class="mt-2 max-w-2xl text-sm text-stone-600">{{ $season->notes }}</p>
                @endif
            </div>
            <div class="flex flex-wrap items-center gap-2">
                @if ($canEdit)
                    <x-dropdown width="w-52">
                        <x-slot:trigger>
                            <x-button variant="secondary" icon="ellipsis">{{ __('More') }}</x-button>
                        </x-slot:trigger>
                        <x-dropdown-item icon="pencil" :href="route('seasons.edit', $season)">{{ __('Edit season') }}</x-dropdown-item>
                        <x-dropdown-item icon="list-plus" x-on:click="$dispatch('open-modal', 'add-stage')">{{ __('Add custom stage') }}</x-dropdown-item>
                        <div class="my-1 border-t border-stone-100"></div>
                        <p class="px-2.5 pt-1 pb-1 text-xs font-medium text-stone-400">{{ __('Set status') }}</p>
                        @foreach (['planned', 'active', 'harvested', 'ensiled'] as $status)
                            <form method="POST" action="{{ route('seasons.status', $season) }}">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="{{ $status }}">
                                <x-dropdown-item type="submit" :icon="$season->status === $status ? 'check' : 'circle'">{{ CropSeason::statusLabel($status) }}</x-dropdown-item>
                            </form>
                        @endforeach
                    </x-dropdown>
                @endif
                @if ($season->isClosed())
                    @can('reopen-seasons')
                        <x-button variant="secondary" icon="lock-open" x-on:click="$dispatch('open-modal', 'reopen-season')">{{ __('Reopen season') }}</x-button>
                    @endcan
                @else
                    @can('manage-seasons')
                        <x-button variant="secondary" icon="lock" x-on:click="$dispatch('open-modal', 'close-season')">{{ __('Close season') }}</x-button>
                    @endcan
                    @can('enter-expenses')
                        <x-button icon="plus" :href="route('expenses.create', $season)">{{ __('Expense') }}</x-button>
                    @endcan
                @endif
            </div>
        </div>

        <dl class="mt-6 grid grid-cols-2 gap-4 border-t border-stone-100 pt-5 md:grid-cols-4">
            <div>
                <dt class="text-xs text-stone-500">{{ __('Season total') }}</dt>
                <dd class="mt-1 text-2xl font-semibold text-stone-900 tabular-nums">{{ Money::format($total) }}</dd>
            </div>
            <div>
                <dt class="text-xs text-stone-500">{{ __('Cost per acre') }}</dt>
                <dd class="mt-1 text-lg font-semibold text-stone-900 tabular-nums">{{ Money::format($season->summary?->cost_per_acre) }}</dd>
                <dd class="text-xs text-stone-500">{{ Money::number($season->total_area_acres, 2) }} {{ __('acres') }}</dd>
            </div>
            <div>
                <dt class="text-xs text-stone-500">{{ __('Paid') }}</dt>
                <dd class="mt-1 text-lg font-semibold text-stone-900 tabular-nums">{{ Money::format($season->summary?->paid) }}</dd>
            </div>
            <div>
                <dt class="text-xs text-stone-500">{{ __('Outstanding') }}</dt>
                <dd @class(['mt-1 text-lg font-semibold tabular-nums', 'text-red-700' => $season->summary?->outstanding > 0, 'text-stone-900' => ! ($season->summary?->outstanding > 0)])>{{ Money::format($season->summary?->outstanding) }}</dd>
            </div>
        </dl>
        @if ((float) $season->summary?->economic_cost > $total)
            <p class="mt-3 text-xs text-stone-500">{{ __('Full economic cost including notional machine costs: :amount', ['amount' => Money::format($season->summary->economic_cost)]) }}</p>
        @endif
    </section>

    @php $summary = $season->summary; @endphp
    <a href="{{ route('production.index', ['season' => $season->code]) }}" class="mt-4 grid grid-cols-2 gap-px overflow-hidden rounded-xl border border-stone-200 bg-stone-200 shadow-card transition-shadow hover:shadow-pop md:grid-cols-4">
        @foreach ([
            [__('Harvested'), $summary?->harvested_kg > 0 ? Money::number($summary->harvested_kg / 1000, 1).' t' : '—', $summary?->yield_kg_per_acre > 0 ? __(':kg kg per acre', ['kg' => Money::number($summary->yield_kg_per_acre)]) : __('No harvest recorded')],
            [__('Usable silage'), $summary?->silage_usable_kg > 0 ? Money::number($summary->silage_usable_kg / 1000, 1).' t' : '—', trans_choice(':count batch|:count batches', $season->batches->count())],
            [__('Cost per tonne harvested'), $summary?->cost_per_tonne_harvested ? Money::format($summary->cost_per_tonne_harvested) : '—', __('Season total ÷ harvest')],
            [__('Silage cost per tonne'), $summary?->silage_cost_per_tonne ? Money::format($summary->silage_cost_per_tonne) : '—', $season->isClosed() && $season->batches->isNotEmpty() ? __('Frozen at close') : __('Updates with every expense')],
        ] as [$label, $value, $hint])
            <div class="bg-white px-5 py-4">
                <p class="text-xs text-stone-500">{{ $label }}</p>
                <p class="mt-1 text-lg font-semibold text-stone-900 tabular-nums">{{ $value }}</p>
                <p class="text-xs text-stone-500">{{ $hint }}</p>
            </div>
        @endforeach
    </a>

    <h3 class="mt-8 mb-3 text-sm font-semibold tracking-wider text-stone-500 uppercase">{{ __('Stages') }}</h3>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-5">
        @foreach ($season->stages as $stage)
            @php $stageTotal = (float) ($stage->totals?->total ?? 0); $share = $total > 0 ? round($stageTotal / $total * 100, 1) : 0; @endphp
            <a href="{{ route('stages.show', [$season, $stage]) }}" class="group flex flex-col rounded-xl border border-stone-200 bg-white p-4 shadow-card transition-shadow hover:shadow-pop focus-visible:outline-2 focus-visible:outline-brand-500">
                <div class="flex items-start justify-between gap-2">
                    <span class="grid size-7 shrink-0 place-items-center rounded-full bg-stone-100 text-xs font-semibold text-stone-600">{{ $loop->iteration }}</span>
                    <x-status kind="stage" :value="$stage->status" />
                </div>
                <p class="mt-3 text-sm font-semibold text-stone-900 group-hover:text-brand-800">{{ $stage->name() }}</p>
                <p class="mt-1 text-lg font-semibold text-stone-900 tabular-nums">{{ Money::format($stageTotal) }}</p>
                <div class="mt-auto pt-3">
                    <div class="mb-1 flex justify-between text-xs text-stone-500">
                        <span>{{ trans_choice(':count line|:count lines', $stage->totals?->line_count ?? 0) }}</span>
                        <span class="tabular-nums">{{ $share }}%</span>
                    </div>
                    <x-progress :value="$share" />
                </div>
            </a>
        @endforeach
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-5">
        <x-card :title="__('Cost by category')" :description="__('Share of the season total')" class="xl:col-span-2">
            @forelse ($categories as $category)
                <div @class(['mt-4' => ! $loop->first])>
                    <div class="mb-1.5 flex items-baseline justify-between gap-3 text-sm">
                        <span class="truncate font-medium text-stone-800">{{ __($category->name) }}</span>
                        <span class="shrink-0 text-stone-500 tabular-nums"><span class="font-medium text-stone-800">{{ Money::format($category->total) }}</span> · {{ $category->share }}%</span>
                    </div>
                    <x-progress :value="$category->share" />
                </div>
            @empty
                <p class="text-sm text-stone-500">{{ __('No expenses recorded yet.') }}</p>
            @endforelse
        </x-card>

        <x-card :title="__('Recent expenses')" flush class="xl:col-span-3">
            @if ($recent->isEmpty())
                <x-empty-state icon="receipt" :title="__('No expenses yet')" :description="__('Add the first expense to start this season.')">
                    @if (! $season->isClosed())
                        @can('enter-expenses')
                            <x-button icon="plus" :href="route('expenses.create', $season)">{{ __('Add expense') }}</x-button>
                        @endcan
                    @endif
                </x-empty-state>
            @else
                <ul class="divide-y divide-stone-100">
                    @foreach ($recent as $expense)
                        <li class="flex items-center gap-3 px-5 py-3">
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-stone-900">{{ $expense->description }}</p>
                                <p class="truncate text-xs text-stone-500">{{ $expense->expense_date->translatedFormat('d M') }} · {{ $expense->stage->name() }} · {{ $expense->category->fullName() }}</p>
                            </div>
                            <div class="text-end">
                                <p class="text-sm font-semibold text-stone-900 tabular-nums">{{ Money::format($expense->amount) }}</p>
                                <x-status kind="payment" :value="$expense->status?->payment_status" class="mt-0.5" />
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-card>
    </div>

    @if (! $season->isClosed() && auth()->user()->can('manage-seasons'))
        <x-modal name="close-season" :title="__('Close :name?', ['name' => $season->name])" size="sm" :action="route('seasons.close', $season)" :submit="__('Close season')">
            <ul class="list-disc space-y-1.5 ps-5 text-sm text-stone-600">
                <li>{{ __('Expenses can no longer be added, edited or deleted.') }}</li>
                <li>{{ __('The silage cost per kg (:cost) is frozen onto each silage batch.', ['cost' => $summary?->silage_cost_per_kg ? Money::format($summary->silage_cost_per_kg, 2) : __('none yet')]) }}</li>
                <li>{{ __('Only an owner can reopen it for late bills.') }}</li>
            </ul>
            @if ($summary?->outstanding > 0)
                <p class="mt-4 rounded-lg bg-amber-50 px-3 py-2 text-sm text-amber-800">{{ __(':amount is still unpaid. Payments can still be recorded after closing.', ['amount' => Money::format($summary->outstanding)]) }}</p>
            @endif
        </x-modal>
    @endif

    @if ($season->isClosed() && auth()->user()->can('reopen-seasons'))
        <x-modal name="reopen-season" :title="__('Reopen :name?', ['name' => $season->name])" size="sm" :action="route('seasons.reopen', $season)" :submit="__('Reopen season')">
            <p class="text-sm text-stone-600">{{ __('Use this for late bills. The frozen silage cost is cleared and recalculated, then frozen again when the season is closed. The change is recorded in the audit log.') }}</p>
        </x-modal>
    @endif

    @if ($canEdit)
        <x-modal name="add-stage" :title="__('Add custom stage')" :description="__('Adds a stage to this season only, after the standard stages.')" size="sm" :action="route('seasons.stages.store', $season)" :submit="__('Add stage')" :show="$errors->has('name')">
            <x-form.input :label="__('Stage name')" name="name" required maxlength="60" :placeholder="__('e.g. Second cutting')" :value="old('name')" />
        </x-modal>
    @endif
</x-layouts.app>
