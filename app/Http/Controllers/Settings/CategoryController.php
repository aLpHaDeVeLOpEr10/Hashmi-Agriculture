<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\ExpenseCategory;
use App\Models\StageTemplate;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        return view('settings.categories', [
            'categories' => ExpenseCategory::query()->whereNull('parent_id')->with(['stages', 'children.stages'])->orderBy('name')->get(),
            'units' => Unit::query()->orderBy('name')->get(),
            'stages' => StageTemplate::query()->where('is_system', true)->where('is_active', true)->orderBy('sequence')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('manage-settings');
        [$data, $stageIds] = $this->validated($request);

        $category = DB::transaction(function () use ($data, $stageIds, $request) {
            $category = new ExpenseCategory($data);
            $category->farm_id = $request->user()->farm_id;
            $category->save();
            $category->stages()->sync($stageIds);

            return $category;
        });

        return back()->with('status', __('Category “:name” added.', ['name' => $category->name]));
    }

    public function update(Request $request, ExpenseCategory $category): RedirectResponse
    {
        $this->authorize('manage-settings');
        abort_if($category->isSystem(), 403, __('System categories cannot be changed.'));
        [$data, $stageIds] = $this->validated($request, $category);

        DB::transaction(function () use ($category, $data, $stageIds, $request) {
            $category->update($data + ['is_active' => $request->boolean('is_active')]);
            $category->stages()->sync($stageIds);
        });

        return back()->with('status', __('Category updated.'));
    }

    /** @return array{0: array, 1: list<int>} validated attributes and stage ids ([] = all stages) */
    private function validated(Request $request, ?ExpenseCategory $category = null): array
    {
        // Callers that don't mention stages get a category usable in every stage.
        $request->merge(['all_stages' => $request->boolean('all_stages', true)]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'parent_id' => ['nullable', Rule::exists('expense_categories', 'id')->whereNull('parent_id')->where(fn ($q) => $q->whereNull('farm_id')->orWhere('farm_id', $request->user()->farm_id)), Rule::notIn([$category?->id])],
            'cost_type' => ['required', Rule::in(ExpenseCategory::COST_TYPES)],
            'default_unit' => ['nullable', Rule::exists('units', 'code')],
            'all_stages' => ['boolean'],
            'stage_ids' => ['array', Rule::requiredIf(! $request->boolean('all_stages'))],
            'stage_ids.*' => ['integer', Rule::exists('stage_templates', 'id')],
        ], ['stage_ids.required' => __('Pick at least one stage, or choose all stages.')]);

        $stageIds = $request->boolean('all_stages') ? [] : array_map('intval', $data['stage_ids'] ?? []);
        unset($data['all_stages'], $data['stage_ids']);

        return [$data, $stageIds];
    }
}
