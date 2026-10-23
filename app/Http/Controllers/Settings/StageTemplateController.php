<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\StageTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class StageTemplateController extends Controller
{
    public function index(): View
    {
        return view('settings.stages', ['templates' => StageTemplate::query()->where('is_system', true)->orderBy('sequence')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('manage-settings');
        $data = $this->validated($request);
        StageTemplate::create($data + ['code' => 'S-'.Str::upper(Str::random(8)), 'is_system' => true]);

        return back()->with('status', __('Stage “:name” added. New seasons will include it.', ['name' => $data['name']]));
    }

    public function update(Request $request, StageTemplate $stage): RedirectResponse
    {
        $this->authorize('manage-settings');
        $stage->update($this->validated($request) + ['is_active' => $request->boolean('is_active')]);

        return back()->with('status', __('Stage template updated.'));
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:60'],
            'sequence' => ['required', 'integer', 'min:1', 'max:99'],
        ]);
    }
}
