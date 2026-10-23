<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Field;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FieldController extends Controller
{
    public function index(): View
    {
        return view('settings.fields', ['fields' => Field::query()->orderBy('name')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('manage-settings');
        $field = Field::create($this->validated($request));

        return back()->with('status', __('Field “:name” added.', ['name' => $field->name]));
    }

    public function update(Request $request, Field $field): RedirectResponse
    {
        $this->authorize('manage-settings');
        $field->update($this->validated($request) + ['is_active' => $request->boolean('is_active')]);

        return back()->with('status', __('Field updated.'));
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'area_acres' => ['required', 'numeric', 'min:0.01', 'max:99999'],
        ]);
    }
}
