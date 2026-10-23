<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UnitController extends Controller
{
    public function index(): View
    {
        return view('settings.units', ['units' => Unit::query()->orderBy('kind')->orderBy('name')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('manage-settings');
        $data = $request->validate([
            'code' => ['required', 'string', 'max:10', 'alpha_dash', 'unique:units,code'],
            'name' => ['required', 'string', 'max:40'],
            'kind' => ['required', Rule::in(Unit::KINDS)],
        ]);
        Unit::create(['code' => strtolower($data['code'])] + $data);

        return back()->with('status', __('Unit “:name” added.', ['name' => $data['name']]));
    }

    public function update(Request $request, Unit $unit): RedirectResponse
    {
        $this->authorize('manage-settings');
        $unit->update($request->validate([
            'name' => ['required', 'string', 'max:40'],
            'kind' => ['required', Rule::in(Unit::KINDS)],
        ]));

        return back()->with('status', __('Unit updated.'));
    }
}
