<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Payment;
use App\Services\PaymentService;
use App\Support\CostReport;
use App\Support\Money;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PayableController extends Controller
{
    public function __construct(private PaymentService $payments) {}

    public function index(CostReport $report): View
    {
        $bills = $report->outstandingBills();

        return view('payables.index', [
            'groups' => $bills->groupBy(fn ($bill) => $bill->vendor_id ?? 0)
                ->map(fn ($items, $vendorId) => (object) [
                    'vendor_id' => $vendorId ?: null,
                    'name' => $items->first()->vendor_name ?? __('No vendor'),
                    'phone' => $items->first()->vendor_phone,
                    'balance' => (float) $items->sum('balance'),
                    'oldest' => $items->min('expense_date'),
                    'bills' => $items->values(),
                ])
                ->sortBy('oldest')
                ->values(),
            'ageing' => $report->ageingBuckets($bills),
            'payments' => Payment::query()->with(['vendor', 'allocations.expense'])->latest('payment_date')->latest('id')->limit(15)->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('record-payments');
        $data = $request->validate([
            'vendor_id' => ['nullable', Rule::exists('vendors', 'id')->where('farm_id', $request->user()->farm_id)],
            'payment_date' => ['required', 'date', 'before_or_equal:today'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:999999999999'],
            'method' => ['required', Rule::in(Payment::METHODS)],
            'reference' => ['nullable', 'string', 'max:60'],
            'allocations' => ['required', 'array'],
            'allocations.*' => ['nullable', 'numeric', 'min:0'],
        ]);

        $payment = $this->payments->record($data, $data['allocations']);

        return redirect()->route('payables.index')->with('status', __('Payment of :amount recorded against :count bills.', [
            'amount' => Money::format($payment->amount), 'count' => $payment->allocations()->count(),
        ]));
    }

    public function payExpense(Request $request, Expense $expense): RedirectResponse
    {
        $this->authorize('record-payments');
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_date' => ['required', 'date', 'before_or_equal:today'],
            'method' => ['required', Rule::in(Payment::METHODS)],
            'reference' => ['nullable', 'string', 'max:60'],
        ]);

        $this->payments->payExpense($expense, $data['amount'], $data['payment_date'], $data['method'], $data['reference'] ?? null);

        return back()->with('status', __('Payment recorded.'));
    }

    public function destroy(Payment $payment): RedirectResponse
    {
        $this->authorize('record-payments');
        $this->payments->delete($payment);

        return back()->with('status', __('Payment removed. The bills it covered are unpaid again.'));
    }
}
