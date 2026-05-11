<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::with('roles')->orderBy('name')->paginate(20);
        return view('users.index', compact('users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:255|unique:users,username',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role_id' => 'required|integer|exists:roles,id',
        ]);

        try {
            // Check if role exists
            $role = Role::find($validated['role_id']);
            if (!$role) {
                return back()->withErrors(['role_id' => 'Selected role does not exist'])->withInput();
            }

            // Create user - ensure status is boolean
            $user = User::create([
                'username' => $validated['username'],
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'status' => true,
            ]);

            // Attach role
            $user->roles()->attach($validated['role_id']);

            return redirect()->route('users.index')->with('success', 'User created successfully with role');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to create user: ' . $e->getMessage()])->withInput();
        }
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role_id' => 'required|integer|exists:roles,id',
        ]);

        $data = [
            'username' => $validated['username'],
            'name' => $validated['name'],
            'email' => $validated['email'],
            'status' => $request->has('status') ? 1 : 0,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);
        $user->roles()->sync([$validated['role_id']]);

        return redirect()->route('users.index')->with('success', 'User updated successfully');
    }

    public function destroy(User $user)
    {
        $user->roles()->detach();
        $user->delete();
        return redirect()->route('users.index')->with('success', 'User deleted successfully');
    }
}
