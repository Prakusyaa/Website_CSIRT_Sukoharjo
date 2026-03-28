<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        $users = User::with('role')->orderBy('id', 'desc')->paginate(15);

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
        ]);
    }

    public function create(Request $request): Response
    {
        return Inertia::render('Admin/Users/Create', [
            'roles' => app(\App\Services\ReferenceDataService::class)->getRoles(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {

        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|string|email|max:255|unique:users',
            'username'  => 'required|string|max:50|unique:users',
            'role_id'   => 'required|integer|exists:roles,id',
            'is_active' => 'boolean',
            'password'  => 'required|string|min:8',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['is_active'] = $validated['is_active'] ?? true;

        User::create($validated);

        return redirect()->route('admin.users.index')->with('success', 'User account successfully provisioned.');
    }

    public function edit(Request $request, User $user): Response
    {
        return Inertia::render('Admin/Users/Edit', [
            'user'  => $user,
            'roles' => app(\App\Services\ReferenceDataService::class)->getRoles(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {

        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'username'  => ['required', 'string', 'max:50', Rule::unique('users')->ignore($user->id)],
            'role_id'   => 'required|integer|exists:roles,id',
            'is_active' => 'boolean',
            'password'  => 'nullable|string|min:8',
        ]);

        // Self-lockout protection: admins cannot demote or deactivate themselves
        if ($request->user()->id === $user->id) {
            $adminRole = Role::where('level', 100)->firstOrFail();
            if ((int) $validated['role_id'] !== $adminRole->id) {
                return back()->withErrors(['role_id' => 'You cannot demote your own Admin account.']);
            }
            $validated['is_active'] = true;
        }

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()->route('admin.users.index')->with('success', 'User profile updated.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {

        if ($request->user()->id === $user->id) {
            return back()->withErrors(['error' => 'You cannot delete your own account.']);
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'Account deleted.');
    }
}
