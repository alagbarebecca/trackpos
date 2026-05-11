<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::with('permissions')->get();
        return view('roles.index', compact('roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:roles,name',
            'description' => 'nullable|string',
        ]);

        $role = Role::create($request->all());

        return redirect()->route('roles.index')->with('success', 'Role created successfully!');
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|string|unique:roles,name,' . $role->id,
            'description' => 'nullable|string',
        ]);

        $role->update($request->all());

        return redirect()->route('roles.index')->with('success', 'Role updated successfully!');
    }

    public function destroy(Role $role)
    {
        if ($role->users()->count() > 0) {
            return redirect()->route('roles.index')->with('error', 'Cannot delete role with assigned users!');
        }
        
        $role->delete();
        
        return redirect()->route('roles.index')->with('success', 'Role deleted successfully!');
    }

    public function assignPermissions(Request $request, Role $role)
    {
        $request->validate([
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role->permissions()->sync($request->permissions ?? []);

        return redirect()->route('roles.index')->with('success', 'Permissions assigned successfully!');
    }

    public function assignToUser(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'roles' => 'array',
            'roles.*' => 'exists:roles,id',
        ]);

        $user = \App\Models\User::find($request->user_id);
        $user->roles()->sync($request->roles ?? []);

        return redirect()->back()->with('success', 'Roles assigned to user successfully!');
    }
}