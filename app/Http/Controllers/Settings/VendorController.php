<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use App\Support\Phone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class VendorController extends Controller
{
    public function index(): View
    {
        return view('settings.vendors', ['vendors' => Vendor::query()->withCount('expenses')->orderBy('name')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('manage-settings');
        $vendor = Vendor::create($this->validated($request));

        return back()->with('status', __('Vendor “:name” added.', ['name' => $vendor->name]));
    }

    public function update(Request $request, Vendor $vendor): RedirectResponse
    {
        $this->authorize('manage-settings');
        $vendor->update($this->validated($request) + ['is_active' => $request->boolean('is_active')]);

        return back()->with('status', __('Vendor updated.'));
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:20'],
            'type' => ['required', Rule::in(Vendor::TYPES)],
        ]);
        $data['phone'] = $data['phone'] ? Phone::normalize($data['phone']) : null;

        return $data;
    }
}
