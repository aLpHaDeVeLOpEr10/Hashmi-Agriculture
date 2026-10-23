<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Phone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        return view('settings.users', ['users' => User::query()->where('farm_id', $request->user()->farm_id)->orderBy('name')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('manage-users');
        $request->merge(['phone' => Phone::normalize((string) $request->input('phone'))]);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'digits_between:10,13', 'unique:users,phone'],
            'role' => ['required', Rule::in(User::ROLES)],
            'password' => ['required', Password::min(8)],
        ]);

        $user = new User($data);
        $user->farm_id = $request->user()->farm_id;
        $user->save();

        return back()->with('status', __('User :name can now sign in with :phone.', ['name' => $user->name, 'phone' => Phone::display($user->phone)]));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->authorize('manage-users');
        abort_unless($user->farm_id === $request->user()->farm_id, 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'role' => ['required', Rule::in(User::ROLES)],
            'password' => ['nullable', Password::min(8)],
        ]);
        $data['is_active'] = $request->boolean('is_active');

        if ($user->is($request->user()) && ($data['role'] !== 'owner' || ! $data['is_active'])) {
            return back()->withErrors(['role' => __('You cannot remove your own owner access.')]);
        }

        if (blank($data['password'])) {
            unset($data['password']);
        }
        $user->update($data);

        return back()->with('status', __('User updated.'));
    }
}
