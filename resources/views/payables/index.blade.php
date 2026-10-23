@use('App\Support\Money')
@use('App\Models\Payment')

@php
    $total = $groups->sum('balance');
    $billCount = $groups->sum(fn ($g) => $g->bills->count());
    $oldest = $groups->flatMap->bills->max('age_days');
    $canPay = auth()->user()->can('record-payments');
@endphp

<x-layouts.app :title="__('Payables')" :breadcrumbs="[__('Payables')]">
    <x-page-header :description="__('Unpaid and partly paid bills, grouped by vendor with the oldest first. One payment can settle several bills.')">
        <x-slot:actions>
            <x-button variant="secondary" icon="file-chart-column" :href="route('reports.ageing')">{{ __('Ageing report') }}</x-button>
        </x-slot:actions>
    </x-page-header>

    @if ($errors->any())
        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
            <p class="font-semibold">{{ __('The payment was not saved.') }}</p>
            <ul class="mt-1 list-disc ps-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-stat-card :label="__('Outstanding')" :value="Money::format($total)" icon="receipt" :hint="trans_choice(':count bill|:count bills', $billCount)" tone="rose" />
        <div class="rounded-xl border border-stone-200 bg-white p-5 shadow-card sm:col-span-1 xl:col-span-3">
            <p class="text-sm font-medium text-stone-500">{{ __('Age of unpaid balances') }}</p>
            <div class="mt-3 grid grid-cols-2 gap-3 md:grid-cols-4">
                @foreach ($ageing as $bucket => $row)
                    <div @class(['rounded-lg border p-3', 'border-red-200 bg-red-50/60' => $bucket === '90+' && $row['amount'] > 0, 'border-stone-200' => ! ($bucket === '90+' && $row['amount'] > 0)])>
                        <p class="text-xs text-stone-500">{{ __(':range days', ['range' => $bucket]) }}</p>
                        <p class="mt-1 font-semibold text-stone-900 tabular-nums">{{ Money::format($row['amount']) }}</p>
                        <p class="text-xs text-stone-500">{{ trans_choice(':count bill|:count bills', $row['count']) }}</p>
                    </div>
                @endforeach
            </div>
            @if ($oldest)
                <p class="mt-3 text-xs text-stone-500">{{ __('Oldest unpaid bill is :days days old.', ['days' => $oldest]) }}</p>
            @endif
        </div>
    </div>

    <div x-data="paymentAllocator" x-on:pay-vendor.window="load($event.detail); $dispatch('open-modal', 'record-payment')">
        <div class="mt-6 space-y-4">
            @forelse ($groups as $group)
                <x-card :title="$group->name" :description="($group->phone ? \App\Support\Phone::display($group->phone).' · ' : '').trans_choice(':count bill|:count bills', $group->bills->count()).' · '.__('oldest :date', ['date' => \Illuminate\Support\Carbon::parse($group->oldest)->translatedFormat('d M Y')])" flush>
                    <x-slot:actions>
                        <span class="text-sm text-stone-500">{{ __('Balance') }} <span class="font-semibold text-red-700 tabular-nums">{{ Money::format($group->balance) }}</span></span>
                        @if ($canPay)
                            <x-button size="sm" icon="banknote"
                                x-on:click="$dispatch('pay-vendor', {{ Js::from(['vendor_id' => $group->vendor_id, 'name' => $group->name, 'bills' => $group->bills->map(fn ($b) => ['id' => $b->expense_id, 'description' => $b->description, 'date' => \Illuminate\Support\Carbon::parse($b->expense_date)->translatedFormat('d M Y'), 'season' => $b->season_code, 'balance' => (float) $b->balance])]) }})">
                                {{ __('Record payment') }}
                            </x-button>
                        @endif
                    </x-slot:actions>
                    <x-table min-width="min-w-[760px]">
                        <x-slot:head>
                            <tr>
                                <th>{{ __('Date') }}</th>
                                <th>{{ __('Description') }}</th>
                                <th>{{ __('Season / stage') }}</th>
                                <th class="text-end">{{ __('Amount') }}</th>
                                <th class="text-end">{{ __('Paid') }}</th>
                                <th class="text-end">{{ __('Balance') }}</th>
                                <th class="text-end">{{ __('Age') }}</th>
                            </tr>
                        </x-slot:head>
                        @foreach ($group->bills as $bill)
                            <tr>
                                <td class="whitespace-nowrap">{{ \Illuminate\Support\Carbon::parse($bill->expense_date)->translatedFormat('d M Y') }}</td>
                                <td class="font-medium text-stone-900">{{ $bill->description }}</td>
                                <td class="whitespace-nowrap">
                                    <a href="{{ route('seasons.show', $bill->season_code) }}" class="hover:text-brand-700">{{ $bill->season_code }}</a>
                                    <span class="block text-xs text-stone-500">{{ __($bill->stage_name) }}</span>
                                </td>
                                <td class="text-end tabular-nums">{{ Money::number($bill->amount) }}</td>
                                <td class="text-end tabular-nums">{{ Money::number($bill->paid) }}</td>
                                <td class="text-end font-semibold text-stone-900 tabular-nums">{{ Money::number($bill->balance) }}</td>
                                <td @class(['text-end whitespace-nowrap tabular-nums', 'font-medium text-red-700' => $bill->age_days > 90, 'text-stone-500' => $bill->age_days <= 90])>{{ trans_choice(':count day|:count days', $bill->age_days) }}</td>
                            </tr>
                        @endforeach
                    </x-table>
                </x-card>
            @empty
                <div class="rounded-xl border border-dashed border-stone-300 bg-white">
                    <x-empty-state icon="circle-check" :title="__('Nothing to pay')" :description="__('Every bill has been paid in full.')" />
                </div>
            @endforelse
        </div>

        @if ($canPay)
            <x-modal name="record-payment" :title="__('Record payment')" size="lg" :action="route('payments.store')" :submit="__('Save payment')">
                <x-slot:hidden>
                    <input type="hidden" name="vendor_id" :value="vendorId">
                </x-slot:hidden>
                <p class="mb-4 text-sm text-stone-600">{{ __('Paying') }} <span class="font-semibold text-stone-900" x-text="vendorName"></span></p>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <x-form.input :label="__('Amount paid')" name="amount" type="number" step="0.01" min="0.01" required x-model="amount" x-on:input="autoAllocate()" :suffix="config('agri.currency')" />
                    <x-form.input :label="__('Payment date')" name="payment_date" type="date" required :value="now()->toDateString()" :max="now()->toDateString()" />
                    <x-form.select :label="__('Method')" name="method" required :placeholder="false">
                        @foreach (Payment::METHODS as $method)
                            <option value="{{ $method }}">{{ __(ucfirst($method)) }}</option>
                        @endforeach
                    </x-form.select>
                    <div class="sm:col-span-3">
                        <x-form.input :label="__('Reference')" name="reference" maxlength="60" :placeholder="__('Cheque no., transaction ID…')" />
                    </div>
                </div>

                <p class="mt-5 mb-2 text-sm font-medium text-stone-700">{{ __('Bills this payment settles') }}</p>
                <ul class="divide-y divide-stone-100 rounded-lg border border-stone-200">
                    <template x-for="bill in bills" :key="bill.id">
                        <li class="flex items-center gap-3 px-3 py-2.5">
                            <input type="checkbox" :checked="!!bill.alloc" x-on:change="toggle(bill)" class="size-4 rounded border-stone-300 text-brand-600 focus:ring-brand-500" :aria-label="bill.description">
                            <span class="min-w-0 flex-1">
                                <span class="block truncate text-sm font-medium text-stone-800" x-text="bill.description"></span>
                                <span class="block text-xs text-stone-500"><span x-text="bill.date"></span> · <span x-text="bill.season"></span> · {{ __('due') }} <span x-text="bill.balance.toLocaleString()"></span></span>
                            </span>
                            <input type="number" step="0.01" min="0" :max="bill.balance" :name="'allocations[' + bill.id + ']'" x-model="bill.alloc"
                                class="form-control w-32 text-end tabular-nums" :aria-label="'{{ __('Amount for') }} ' + bill.description">
                        </li>
                    </template>
                </ul>
                <p class="mt-3 text-sm" :class="difference === 0 ? 'text-brand-700' : 'text-amber-700'">
                    {{ __('Allocated') }} <span class="font-semibold tabular-nums" x-text="allocated.toLocaleString()"></span>
                    <span x-show="difference !== 0">· {{ __('difference') }} <span class="font-semibold tabular-nums" x-text="difference.toLocaleString()"></span></span>
                </p>
            </x-modal>
        @endif
    </div>

    <x-card :title="__('Recent payments')" flush class="mt-6">
        @if ($payments->isEmpty())
            <x-empty-state icon="banknote" :title="__('No payments yet')" :description="__('Payments you record will be listed here.')" />
        @else
            <x-table min-width="min-w-[720px]">
                <x-slot:head>
                    <tr>
                        <th>{{ __('Date') }}</th>
                        <th>{{ __('Vendor') }}</th>
                        <th>{{ __('Method') }}</th>
                        <th>{{ __('Bills') }}</th>
                        <th class="text-end">{{ __('Amount') }}</th>
                        <th></th>
                    </tr>
                </x-slot:head>
                @foreach ($payments as $payment)
                    <tr>
                        <td class="whitespace-nowrap">{{ $payment->payment_date->translatedFormat('d M Y') }}</td>
                        <td class="font-medium text-stone-900">{{ $payment->vendor?->name ?? '—' }}</td>
                        <td class="whitespace-nowrap">{{ __(ucfirst($payment->method)) }} @if ($payment->reference)<span class="text-xs text-stone-500">· {{ $payment->reference }}</span>@endif</td>
                        <td class="text-sm text-stone-600">{{ $payment->allocations->map(fn ($a) => $a->expense?->description)->filter()->join(', ') }}</td>
                        <td class="text-end font-semibold whitespace-nowrap text-stone-900 tabular-nums">{{ Money::format($payment->amount) }}</td>
                        <td class="text-end">
                            @if ($canPay)
                                <form method="POST" action="{{ route('payments.destroy', $payment) }}" x-data x-on:submit="if (!confirm(@js(__('Remove this payment? The bills it covered become unpaid again.')))) $event.preventDefault()">
                                    @csrf @method('DELETE')
                                    <x-button type="submit" variant="ghost" size="icon" :aria-label="__('Remove payment')"><x-icon name="trash-2" class="size-4 text-red-500" /></x-button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </x-table>
        @endif
    </x-card>
</x-layouts.app>
