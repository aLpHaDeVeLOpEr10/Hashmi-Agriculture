@use('App\Support\Money')

@php
    $canManage = auth()->user()->can('manage-machinery');
    $basisText = [
        'hours' => __('Split by the hours :machine worked in each stage.', ['machine' => $cost->machine?->name]),
        'acres' => __('Split by the acres :machine worked in each stage.', ['machine' => $cost->machine?->name]),
        'equal' => __('Split equally across the stages you choose.'),
        'manual' => __('Split by the percentages you enter. They must add up to 100%.'),
    ][$cost->basis];
@endphp

<x-layouts.app :title="__('Split shared cost')" :breadcrumbs="[__('Machinery') => route('machinery.index'), $cost->description]">
    <section class="rounded-xl border border-stone-200 bg-white p-5 shadow-card sm:p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <h2 class="text-lg font-semibold text-stone-900">{{ $cost->description }}</h2>
                    <x-badge :tone="$cost->status === 'allocated' ? 'green' : 'amber'">{{ $cost->status === 'allocated' ? __('Split') : __('Draft') }}</x-badge>
                </div>
                <p class="mt-1 text-sm text-stone-500">{{ $cost->cost_date->translatedFormat('d M Y') }} · {{ $cost->category->fullName() }}@if ($cost->machine) · {{ $cost->machine->name }}@endif @if ($cost->vendor) · {{ $cost->vendor->name }}@endif</p>
                <p class="mt-3 text-sm text-stone-600">{{ $basisText }}</p>
            </div>
            <p class="text-2xl font-semibold text-stone-900 tabular-nums">{{ Money::format($cost->total_amount) }}</p>
        </div>
    </section>

    @if ($errors->any())
        <div class="mt-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">{{ $errors->first() }}</div>
    @endif

    @if ($cost->status === 'allocated')
        <x-card :title="__('Split into these expenses')" flush class="mt-6">
            <x-table min-width="min-w-[640px]">
                <x-slot:head>
                    <tr><th>{{ __('Season') }}</th><th>{{ __('Stage') }}</th><th>{{ __('Description') }}</th><th class="text-end">{{ __('Amount') }}</th></tr>
                </x-slot:head>
                @foreach ($cost->expenses as $line)
                    <tr>
                        <td class="whitespace-nowrap"><a href="{{ route('stages.show', [$line->season, $line->season_stage_id]) }}" class="hover:text-brand-700">{{ $line->season->name }}</a></td>
                        <td class="whitespace-nowrap">{{ $line->stage->name() }}</td>
                        <td>{{ $line->description }}</td>
                        <td class="text-end font-semibold tabular-nums">{{ Money::format($line->amount) }}</td>
                    </tr>
                @endforeach
            </x-table>
            @if ($canManage)
                <x-slot:footer>
                    <form method="POST" action="{{ route('machinery.shared.unallocate', $cost) }}" class="flex flex-wrap items-center justify-between gap-3">
                        @csrf
                        <span class="text-stone-500">{{ __('Undoing removes these expense lines and returns the cost to draft.') }}</span>
                        <x-button type="submit" variant="secondary" size="sm" icon="undo-2">{{ __('Undo split') }}</x-button>
                    </form>
                </x-slot:footer>
            @endif
        </x-card>
    @else
        <form method="GET" action="{{ route('machinery.shared.show', $cost) }}" class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-5" x-data>
            <input type="hidden" name="_token" value="{{ csrf_token() }}" disabled x-ref="token">
            <x-card :title="__('Stages')" :description="__('Open seasons only. Tick the stages this cost should be shared by.')" class="xl:col-span-3" flush>
                @forelse ($seasons as $season)
                    <fieldset class="border-b border-stone-100 px-5 py-4 last:border-0">
                        <legend class="sr-only">{{ $season->name }}</legend>
                        <p class="mb-2 text-sm font-semibold text-stone-800">{{ $season->name }} · {{ $season->crop->name }} <span class="font-mono text-xs font-normal text-stone-400">{{ $season->code }}</span></p>
                        <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                            @foreach ($season->stages as $stage)
                                <label class="flex items-center gap-3 rounded-lg border border-stone-200 px-3 py-2 text-sm has-checked:border-brand-400 has-checked:bg-brand-50/50">
                                    <input type="checkbox" name="stages[]" value="{{ $stage->id }}" @checked(in_array($stage->id, $selected, true)) class="size-4 rounded border-stone-300 text-brand-600 focus:ring-brand-500">
                                    <span class="flex-1">{{ $stage->name() }}</span>
                                    @if ($cost->basis === 'manual')
                                        <span class="relative w-24">
                                            <input type="number" name="manual[{{ $stage->id }}]" value="{{ $manual[$stage->id] ?? '' }}" min="0" max="100" step="0.1" class="form-control h-8 pe-7 text-end" aria-label="{{ __('Share for :stage', ['stage' => $stage->name()]) }}">
                                            <span class="pointer-events-none absolute inset-y-0 end-2.5 flex items-center text-xs text-stone-400">%</span>
                                        </span>
                                    @endif
                                </label>
                            @endforeach
                        </div>
                    </fieldset>
                @empty
                    <x-empty-state icon="sprout" :title="__('No open seasons')" :description="__('A shared cost can only be split into seasons that are not closed.')" />
                @endforelse
            </x-card>

            <div class="xl:col-span-2">
                <x-card :title="__('Preview')" flush>
                    @if ($preview->isEmpty())
                        <p class="px-5 py-6 text-sm text-stone-500">
                            {{ in_array($cost->basis, ['hours', 'acres'], true) && $selected
                                ? __('The selected stages have no :basis logged for this machine.', ['basis' => __($cost->basis)])
                                : __('Tick stages, then update the preview.') }}
                        </p>
                    @else
                        <ul class="divide-y divide-stone-100">
                            @foreach ($preview as $row)
                                <li class="flex items-center gap-3 px-5 py-3 text-sm">
                                    <span class="min-w-0 flex-1">
                                        <span class="block font-medium text-stone-900">{{ $row->stage->name() }}</span>
                                        <span class="block text-xs text-stone-500">{{ $row->stage->season->code }} · @if ($cost->basis === 'hours'){{ Money::quantity($row->weight) }} h · @elseif ($cost->basis === 'acres'){{ Money::quantity($row->weight) }} {{ __('acres') }} · @endif{{ round($row->share * 100, 1) }}%</span>
                                    </span>
                                    <span class="font-semibold text-stone-900 tabular-nums">{{ Money::format($row->amount) }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                    <x-slot:footer>
                        <div class="flex flex-wrap justify-end gap-2">
                            <x-button type="submit" variant="secondary" size="sm" icon="refresh-cw">{{ __('Update preview') }}</x-button>
                            @if ($canManage && $preview->isNotEmpty())
                                <x-button type="submit" size="sm" icon="split" formmethod="POST" formaction="{{ route('machinery.shared.allocate', $cost) }}" x-on:click="$refs.token.disabled = false">{{ __('Split cost') }}</x-button>
                            @endif
                        </div>
                    </x-slot:footer>
                </x-card>
                @if ($canManage)
                    <button type="submit" form="delete-shared" class="mt-3 text-sm text-red-600 hover:text-red-700">{{ __('Delete this draft') }}</button>
                @endif
            </div>
        </form>
        @if ($canManage)
            <form id="delete-shared" method="POST" action="{{ route('machinery.shared.destroy', $cost) }}" x-data x-on:submit="if (!confirm(@js(__('Delete this shared cost?')))) $event.preventDefault()">
                @csrf @method('DELETE')
            </form>
        @endif
    @endif
</x-layouts.app>
