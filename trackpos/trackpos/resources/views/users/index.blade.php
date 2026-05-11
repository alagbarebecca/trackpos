@extends('layouts.app')
@section('title', 'Users')
@section('page-title', 'User Management')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Users</h4>
    <div>
        <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-user-tag me-2"></i>Roles
        </a>
        <a href="{{ route('permissions.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-key me-2"></i>Permissions
        </a>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#userModal">
            <i class="fas fa-plus me-2"></i>Add User
        </button>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Permissions</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>
                        @if($user->roles->count() > 0)
                        @foreach($user->roles as $role)
                        <span class="badge bg-{{ $role->name == 'admin' ? 'danger' : ($role->name == 'manager' ? 'warning' : 'info') }}">{{ ucfirst($role->name) }}</span>
                        @endforeach
                        @else
                        <span class="text-muted">No role</span>
                        @endif
                    </td>
                    <td>
                        <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#assignRolesModal{{ $user->id }}">
                            <i class="fas fa-user-plus"></i> Assign
                        </button>
                    </td>
                    <td>
                        <span class="badge bg-{{ $user->status ? 'success' : 'secondary' }}">
                            {{ $user->status ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editModal{{ $user->id }}"><i class="fas fa-edit"></i></button>
                        @if($user->id !== auth()->id())
                        <form method="POST" action="{{ route('users.destroy', $user) }}" class="d-inline">@csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this user?')"><i class="fas fa-trash"></i></button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted">No users found</td></tr>
                @endforelse
            </tbody>
        </table>
        {{ $users->links() }}
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="userModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('users.store') }}" id="addUserForm">
            @csrf
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Add User</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <!-- Error Display -->
                    @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                    
                    <div class="mb-3"><label class="form-label">Username</label><input type="text" class="form-control" name="username" value="{{ old('username') }}" required></div>
                    <div class="mb-3"><label class="form-label">Full Name</label><input type="text" class="form-control" name="name" value="{{ old('name') }}" required></div>
                    <div class="mb-3"><label class="form-label">Email</label><input type="email" class="form-control" name="email" value="{{ old('email') }}" required></div>
                    <div class="mb-3"><label class="form-label">Password</label><input type="password" class="form-control" name="password" required></div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select class="form-select @error('role_id') is-invalid @enderror" name="role_id" required>
                            <option value="">Select Role...</option>
                            @forelse(\App\Models\Role::all() as $role)
                            <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>{{ ucfirst($role->name) }}</option>
                            @empty
                            <option value="">No roles available</option>
                            @endforelse
                        </select>
                        @if(\App\Models\Role::count() == 0)
                        <div class="text-danger small mt-1">No roles found. <a href="{{ route('roles.index') }}">Create roles first</a></div>
                        @endif
                    </div>
                </div>
                <div class="modal-footer"><button type="submit" class="btn btn-primary">Save</button></div>
            </div>
        </form>
    </div>
</div>

@section('scripts')
<script>
document.getElementById('addUserForm')?.addEventListener('submit', function(e) {
    console.log('Add User form submitting...');
    var formData = new FormData(this);
    console.log('Form data:', Object.fromEntries(formData));
});
</script>
@endsection

@foreach($users as $user)
<!-- Edit Modal -->
<div class="modal fade" id="editModal{{ $user->id }}" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('users.update', $user) }}">
            @csrf @method('PUT')
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Edit User</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">Username</label><input type="text" class="form-control" name="username" value="{{ $user->username }}" required></div>
                    <div class="mb-3"><label class="form-label">Full Name</label><input type="text" class="form-control" name="name" value="{{ $user->name }}" required></div>
                    <div class="mb-3"><label class="form-label">Email</label><input type="email" class="form-control" name="email" value="{{ $user->email }}" required></div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select class="form-select" name="role_id" required>
                            @foreach(\App\Models\Role::all() as $role)
                            <option value="{{ $role->id }}" {{ $user->roles->contains('id', $role->id) ? 'selected' : '' }}>{{ ucfirst($role->name) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3"><label class="form-label">New Password (leave blank)</label><input type="password" class="form-control" name="password"></div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="status" value="1" id="status{{ $user->id }}" {{ $user->status ? 'checked' : '' }}>
                            <label class="form-check-label" for="status{{ $user->id }}">Active</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer"><button type="submit" class="btn btn-primary">Update</button></div>
            </div>
        </form>
    </div>
</div>

<!-- Assign Roles Modal -->
<div class="modal fade" id="assignRolesModal{{ $user->id }}" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('roles.assign-user') }}">
            @csrf
            <input type="hidden" name="user_id" value="{{ $user->id }}">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Assign Roles to {{ $user->name }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @php
                    $allRoles = \App\Models\Role::all();
                    $userRoles = $user->roles->pluck('id')->toArray();
                    @endphp
                    @if($allRoles->count() > 0)
                    <div class="row">
                        @foreach($allRoles as $role)
                        <div class="col-md-6 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="roles[]" value="{{ $role->id }}" id="role{{ $user->id }}_{{ $role->id }}" {{ in_array($role->id, $userRoles) ? 'checked' : '' }}>
                                <label class="form-check-label" for="role{{ $user->id }}_{{ $role->id }}">
                                    {{ ucfirst($role->name) }}
                                </label>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <p class="text-muted">No roles available. Create roles first.</p>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save Roles</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endforeach
@endsection
