<?php

namespace App\Http\Controllers;

use App\Models\CropSeason;
use App\Models\ExpenseCategory;
use App\Models\RecurringRule;
use App\Models\Vendor;
use App\Services\RecurringService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RecurringController extends Controller
{
    public function __construct(private RecurringService $recurring) {}

    public function index(): View
    {
        return view('recurring.index', [
            'due' => $this->recurring->due(),
            'rules' => $this->rules()->with(['stage.season', 'stage.template', 'category.parent', 'vendor'])->orderByDesc('is_active')->orderBy('next_run_date')->get(),
            'seasons' => CropSeason::query()->where('status', '!=', 'closed')->with(['crop', 'stages.template'])->orderByDesc('start_date')->get(),
            'categories' => ExpenseCategory::tree(),
            'vendors' => Vendor::query()->where('is_active', true)->orderBy('name')->get(),
            'service' => $this->recurring,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('manage-seasons');
        $data = $this->validated($request);
        RecurringRule::create($data + ['next_run_date' => $data['start_date'], 'is_active' => true]);

        return back()->with('status', __('Recurring cost set up. A draft will appear each period for you to confirm.'));
    }

    public function update(Request $request, RecurringRule $rule): RedirectResponse
    {
        $this->authorize('manage-seasons');
        $this->ensureFarm($rule);
        $data = $this->validated($request);
        $rule->update($data + ['is_active' => $request->boolean('is_active')]);

        return back()->with('status', __('Recurring cost updated.'));
    }

    public function confirm(Request $request, RecurringRule $rule): RedirectResponse
    {
        $this->authorize('edit-expenses');
        $this->ensureFarm($rule);
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'expense_date' => ['required', 'date'],
            'description' => ['nullable', 'string', 'max:200'],
        ]);
        $this->recurring->confirm($rule, $data['amount'], $data['expense_date'], $data['description'] ?? null);

        return back()->with('status', __('Draft confirmed and added to :stage.', ['stage' => $rule->stage->name()]));
    }

    public function skip(RecurringRule $rule): RedirectResponse
    {
        $this->authorize('edit-expenses');
        $this->ensureFarm($rule);
        $this->recurring->skip($rule);

        return back()->with('status', __('Period skipped.'));
    }

    private function rules()
    {
        return RecurringRule::query()->whereHas('stage', fn ($q) => $q->whereIn('season_id', CropSeason::query()->select('id')));
    }

    private function ensureFarm(RecurringRule $rule): void
    {
        abort_unless($this->rules()->whereKey($rule->id)->exists(), 404);
    }

    private function validated(Request $request): array
    {
        $farm = $request->user()->farm_id;

        return $request->validate([
            'season_stage_id' => ['required', 'integer', Rule::exists('season_stages', 'id')->whereIn('season_id', CropSeason::query()->where('status', '!=', 'closed')->pluck('id'))],
            'category_id' => ['required', Rule::exists('expense_categories', 'id')->where(fn ($q) => $q->whereNull('farm_id')->orWhere('farm_id', $farm))],
            'vendor_id' => ['nullable', Rule::exists('vendors', 'id')->where('farm_id', $farm)],
            'description' => ['required', 'string', 'max:200'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:999999999'],
            'frequency' => ['required', Rule::in(['weekly', 'monthly'])],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);
    }
}
