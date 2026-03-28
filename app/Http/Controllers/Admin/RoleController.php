<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class RoleController extends Controller
{
    /**
     * The core system roles (seeded). These cannot be modified or deleted
     * because policies & enum comparisons depend on their exact level values.
     */
    private const PROTECTED_LEVELS = [10, 50, 100];



    public function index(Request $request): Response
    {

        $roles = Role::withCount('users')
            ->orderBy('level', 'asc')
            ->paginate(20);

        return Inertia::render('Admin/Roles/Index', [
            'roles'            => $roles,
            'protectedLevels'  => self::PROTECTED_LEVELS,
        ]);
    }

    public function create(Request $request): Response
    {

        return Inertia::render('Admin/Roles/Create', [
            'protectedLevels'  => self::PROTECTED_LEVELS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {

        $validated = $request->validate([
            'name'  => 'required|string|max:100|unique:roles,name',
            'level' => [
                'required',
                'integer',
                'min:1',
                'max:99',          // Hard cap: custom roles cannot reach Admin level (100)
                'unique:roles,level',
                // Explicitly block the three reserved levels
                Rule::notIn(self::PROTECTED_LEVELS),
            ],
        ], [
            'level.not_in' => 'That level value is reserved for a system role and cannot be reused.',
            'level.max'    => 'Custom roles cannot have a level of 100 (Admin) or above.',
        ]);

        Role::create($validated);

        return redirect()->route('admin.roles.index')->with('success', 'Role created successfully.');
    }

    public function edit(Request $request, Role $role): Response|RedirectResponse
    {

        if (in_array($role->level, self::PROTECTED_LEVELS)) {
            return redirect()->route('admin.roles.index')
                ->withErrors(['error' => "The \"{$role->name}\" role is a core system role and cannot be edited."]);
        }

        return Inertia::render('Admin/Roles/Edit', [
            'role'            => $role,
            'protectedLevels' => self::PROTECTED_LEVELS,
        ]);
    }

    public function update(Request $request, Role $role): RedirectResponse
    {

        if (in_array($role->level, self::PROTECTED_LEVELS)) {
            return back()->withErrors(['error' => 'Core system roles cannot be modified.']);
        }

        $validated = $request->validate([
            'name'  => ['required', 'string', 'max:100', Rule::unique('roles', 'name')->ignore($role->id)],
            'level' => [
                'required',
                'integer',
                'min:1',
                'max:99',
                Rule::unique('roles', 'level')->ignore($role->id),
                Rule::notIn(self::PROTECTED_LEVELS),
            ],
        ], [
            'level.not_in' => 'That level value is reserved for a system role.',
            'level.max'    => 'Custom roles cannot reach level 100 (Admin).',
        ]);

        $role->update($validated);

        return redirect()->route('admin.roles.index')->with('success', 'Role updated successfully.');
    }

    public function destroy(Request $request, Role $role): RedirectResponse
    {

        if (in_array($role->level, self::PROTECTED_LEVELS)) {
            return back()->withErrors(['error' => "Core system role \"{$role->name}\" cannot be deleted."]);
        }

        // Prevent deletion if users are currently assigned to this role
        if ($role->users()->count() > 0) {
            return back()->withErrors([
                'error' => "Cannot delete \"{$role->name}\" — {$role->users()->count()} user(s) are currently assigned to it. Reassign them first.",
            ]);
        }

        $role->delete();

        return redirect()->route('admin.roles.index')->with('success', 'Role deleted.');
    }
}
