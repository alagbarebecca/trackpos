@extends('layouts.app')

@section('title', 'Locations')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>Locations</h4>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addLocationModal">
                        <i class="fas fa-plus"></i> Add Location
                    </button>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Type</th>
                                    <th>Address</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($locations as $location)
                                <tr>
                                    <td>{{ $location->name }}</td>
                                    <td>{{ $location->type ?? 'Warehouse' }}</td>
                                    <td>{{ $location->address ?? '-' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $location->is_active ? 'success' : 'secondary' }}">
                                            {{ $location->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#editLocationModal{{ $location->id }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form action="{{ route('locations.destroy', $location) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center">No locations found</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Location Modal -->
<div class="modal fade" id="addLocationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('locations.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add Location</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Type</label>
                        <select name="type" class="form-control">
                            <option value="warehouse">Warehouse</option>
                            <option value="store">Store</option>
                            <option value="outlet">Outlet</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Address</label>
                        <textarea name="address" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label>Status</label>
                        <select name="is_active" class="form-control">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

@foreach($locations as $location)
<!-- Edit Location Modal -->
<div class="modal fade" id="editLocationModal{{ $location->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('locations.update', $location) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Edit Location</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Name</label>
                        <input type="text" name="name" class="form-control" value="{{ $location->name }}" required>
                    </div>
                    <div class="mb-3">
                        <label>Type</label>
                        <select name="type" class="form-control">
                            <option value="warehouse" {{ $location->type == 'warehouse' ? 'selected' : '' }}>Warehouse</option>
                            <option value="store" {{ $location->type == 'store' ? 'selected' : '' }}>Store</option>
                            <option value="outlet" {{ $location->type == 'outlet' ? 'selected' : '' }}>Outlet</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Address</label>
                        <textarea name="address" class="form-control" rows="2">{{ $location->address }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label>Status</label>
                        <select name="is_active" class="form-control">
                            <option value="1" {{ $location->is_active ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ !$location->is_active ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
@endsection