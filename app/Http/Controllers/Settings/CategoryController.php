<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\ExpenseCategory;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        return view('settings.categories', [
            'categories' => ExpenseCategory::query()->whereNull('parent_id')->with('children')->orderBy('name')->get(),
            'units' => Unit::query()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('manage-settings');
        $category = new ExpenseCategory($this->validated($request));
        $category->farm_id = $request->user()->farm_id;
        $category->save();

        return back()->with('status', __('Category “:name” added.', ['name' => $category->name]));
    }

    public function update(Request $request, ExpenseCategory $category): RedirectResponse
    {
        $this->authorize('manage-settings');
        abort_if($category->isSystem(), 403, __('System categories cannot be changed.'));

        $category->update($this->validated($request, $category) + ['is_active' => $request->boolean('is_active')]);

        return back()->with('status', __('Category updated.'));
    }

    private function validated(Request $request, ?ExpenseCategory $category = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'parent_id' => ['nullable', Rule::exists('expense_categories', 'id')->whereNull('parent_id')->where(fn ($q) => $q->whereNull('farm_id')->orWhere('farm_id', $request->user()->farm_id)), Rule::notIn([$category?->id])],
            'cost_type' => ['required', Rule::in(ExpenseCategory::COST_TYPES)],
            'default_unit' => ['nullable', Rule::exists('units', 'code')],
        ]);
    }
}
