<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    public function index()
    {
        $permissions = Permission::orderBy('name')->get();
        return view('permissions.index', compact('permissions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:permissions,name',
            'slug' => 'required|string|unique:permissions,slug',
            'description' => 'nullable|string',
        ]);

        Permission::create($request->all());

        return redirect()->route('permissions.index')->with('success', 'Permission created successfully!');
    }

    public function update(Request $request, Permission $permission)
    {
        $request->validate([
            'name' => 'required|string|unique:permissions,name,' . $permission->id,
            'slug' => 'required|string|unique:permissions,slug,' . $permission->id,
            'description' => 'nullable|string',
        ]);

        $permission->update($request->all());

        return redirect()->route('permissions.index')->with('success', 'Permission updated successfully!');
    }

    public function destroy(Permission $permission)
    {
        $permission->delete();
        
        return redirect()->route('permissions.index')->with('success', 'Permission deleted successfully!');
    }
}