<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExpenseRequest;
use App\Models\CropSeason;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Field;
use App\Models\Machine;
use App\Models\Unit;
use App\Models\Vendor;
use App\Services\ExpenseService;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    public function __construct(private ExpenseService $expenses) {}

    public function create(Request $request, CropSeason $season): View|RedirectResponse
    {
        $this->authorize('enter-expenses');

        if ($season->isClosed()) {
            return redirect()->route('seasons.show', $season)->withErrors(['season' => __('This season is closed. A manager or owner must reopen it before changes can be made.')]);
        }

        $season->load('stages.template');
        $stageId = (int) $request->query('stage', $season->stages->firstWhere('status', 'in_progress')?->id ?? $season->stages->first()?->id);

        return view('expenses.form', $this->formData($season) + [
            'expense' => new Expense(['expense_date' => today(), 'season_stage_id' => $stageId]),
        ]);
    }

    public function store(ExpenseRequest $request, PaymentService $payments): RedirectResponse
    {
        $expense = DB::transaction(function () use ($request, $payments) {
            $expense = $this->expenses->save($request->validated(), receipt: $request->file('receipt'));

            if ($request->paymentRequested()) {
                $amount = $request->input('payment_option') === 'full' ? $expense->amount : $request->input('paid_amount');
                if ((float) $amount > (float) $expense->amount) {
                    throw ValidationException::withMessages(['paid_amount' => __('The amount paid cannot be more than the expense.')]);
                }
                $payments->payExpense($expense, $amount, $expense->expense_date->toDateString(), $request->input('payment_method'), $request->input('payment_reference'));
            }

            return $expense;
        });
        $season = $expense->season;

        if ($request->input('action') === 'another') {
            return redirect()->route('expenses.create', [$season, 'stage' => $expense->season_stage_id])
                ->with('status', __('Expense saved. Add the next one.'));
        }

        return redirect()->route('stages.show', [$season, $expense->season_stage_id])->with('status', __('Expense saved.'));
    }

    public function edit(Expense $expense): View
    {
        $this->authorize('edit-expenses');
        $season = $expense->season->load('stages.template');

        return view('expenses.form', $this->formData($season) + ['expense' => $expense->load(['attachments', 'status', 'allocations.payment'])]);
    }

    public function update(ExpenseRequest $request, Expense $expense): RedirectResponse
    {
        $this->expenses->save($request->validated(), $expense, $request->file('receipt'));

        return redirect()->route('stages.show', [$expense->season, $expense->season_stage_id])->with('status', __('Expense updated.'));
    }

    public function destroy(Expense $expense): RedirectResponse
    {
        $this->authorize('edit-expenses');
        $this->expenses->delete($expense);

        return redirect()->route('stages.show', [$expense->season, $expense->season_stage_id])->with('status', __('Expense deleted.'));
    }

    private function formData(CropSeason $season): array
    {
        return [
            'season' => $season,
            'categories' => ExpenseCategory::tree(),
            'units' => Unit::query()->orderBy('name')->get(),
            'vendors' => Vendor::query()->where('is_active', true)->orderBy('name')->get(),
            'machines' => Machine::query()->where('is_active', true)->orderBy('name')->get(),
            'fields' => Field::query()->whereIn('id', $season->fields()->pluck('fields.id'))->orderBy('name')->get(),
        ];
    }
}
