@extends('layouts.app')
@section('title', 'Permissions')
@section('page-title', 'Permission Management')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Permissions</h4>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#permissionModal">
        <i class="fas fa-plus me-2"></i>Add Permission
    </button>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Slug</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($permissions as $permission)
                <tr>
                    <td>{{ $permission->name }}</td>
                    <td><code>{{ $permission->slug }}</code></td>
                    <td>{{ $permission->description ?? '-' }}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editModal{{ $permission->id }}">
                            <i class="fas fa-edit"></i>
                        </button>
                        <form method="POST" action="{{ route('permissions.destroy', $permission) }}" class="d-inline">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this permission?')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center text-muted py-4">No permissions found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Add Permission Modal -->
<div class="modal fade" id="permissionModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('permissions.store') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Permission</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control" name="name" placeholder="e.g. View Products" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Slug</label>
                        <input type="text" class="form-control" name="slug" placeholder="e.g. view-products" required>
                        <small class="text-muted">Use lowercase letters and hyphens</small>
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

<!-- Edit Permission Modals -->
@foreach($permissions as $permission)
<div class="modal fade" id="editModal{{ $permission->id }}" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('permissions.update', $permission) }}">
            @csrf @method('PUT')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Permission</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control" name="name" value="{{ $permission->name }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Slug</label>
                        <input type="text" class="form-control" name="slug" value="{{ $permission->slug }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="2">{{ $permission->description }}</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endforeach

@endsection