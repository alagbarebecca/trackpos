@extends('layouts.app')
@section('title', 'Roles')
@section('page-title', 'Role Management')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Roles</h4>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#roleModal">
        <i class="fas fa-plus me-2"></i>Add Role
    </button>
</div>

<div class="row">
    @forelse($roles as $role)
    <div class="col-md-6 col-lg-4 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">{{ $role->name }}</h5>
                <span class="badge bg-primary">{{ $role->users->count() }} users</span>
            </div>
            <div class="card-body">
                <p class="text-muted">{{ $role->description ?? 'No description' }}</p>
                <h6>Permissions:</h6>
                <div class="d-flex flex-wrap gap-1">
                    @forelse($role->permissions as $permission)
                    <span class="badge bg-info">{{ $permission->name }}</span>
                    @empty
                    <span class="text-muted">No permissions</span>
                    @endforelse
                </div>
            </div>
            <div class="card-footer d-flex justify-content-between">
                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editModal{{ $role->id }}">
                    <i class="fas fa-edit"></i> Edit
                </button>
                <button class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#permissionsModal{{ $role->id }}">
                    <i class="fas fa-key"></i> Permissions
                </button>
                @if($role->users->count() == 0)
                <form method="POST" action="{{ route('roles.destroy', $role) }}" class="d-inline">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this role?')">
                        <i class="fas fa-trash"></i>
                    </button>
                </form>
                @endif
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <p class="text-center text-muted py-4">No roles found</p>
    </div>
    @endforelse
</div>

<!-- Add Role Modal -->
<div class="modal fade" id="roleModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('roles.store') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Role</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Edit Role Modals -->
@foreach($roles as $role)
<div class="modal fade" id="editModal{{ $role->id }}" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('roles.update', $role) }}">
            @csrf @method('PUT')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Role</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control" name="name" value="{{ $role->name }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="2">{{ $role->description }}</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="permissionsModal{{ $role->id }}" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('roles.assign-permissions', $role) }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Assign Permissions to {{ $role->name }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @php
                    $allPermissions = \App\Models\Permission::orderBy('name')->get();
                    $rolePermissions = $role->permissions->pluck('id')->toArray();
                    @endphp
                    @if($allPermissions->count() > 0)
                    <div class="row">
                        @foreach($allPermissions as $permission)
                        <div class="col-md-6 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $permission->id }}" id="perm{{ $role->id }}_{{ $permission->id }}" {{ in_array($permission->id, $rolePermissions) ? 'checked' : '' }}>
                                <label class="form-check-label" for="perm{{ $role->id }}_{{ $permission->id }}">
                                    {{ $permission->name }}
                                </label>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <p class="text-muted">No permissions available. Create permissions first.</p>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save Permissions</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endforeach

@endsection