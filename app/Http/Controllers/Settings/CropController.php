<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Crop;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CropController extends Controller
{
    public function index(): View
    {
        return view('settings.crops', ['crops' => Crop::query()->orderBy('name')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('manage-settings');
        $data = $request->validate(['name' => ['required', 'string', 'max:60', 'unique:crops,name']]);
        Crop::create($data);

        return back()->with('status', __('Crop “:name” added.', ['name' => $data['name']]));
    }

    public function update(Request $request, Crop $crop): RedirectResponse
    {
        $this->authorize('manage-settings');
        $data = $request->validate(['name' => ['required', 'string', 'max:60', Rule::unique('crops', 'name')->ignore($crop)]]);
        $crop->update($data + ['is_active' => $request->boolean('is_active')]);

        return back()->with('status', __('Crop updated.'));
    }
}
