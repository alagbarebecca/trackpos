@extends('layouts.app')
@section('title', 'Suppliers')
@section('page-title', 'Supplier Management')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Suppliers</h4>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#supplierModal">
        <i class="fas fa-plus me-2"></i>Add Supplier
    </button>
</div>

<div class="card">
    <div class="card-body">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>City</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($suppliers as $supplier)
                <tr>
                    <td>{{ $supplier->name }}</td>
                    <td>{{ $supplier->email }}</td>
                    <td>{{ $supplier->phone }}</td>
                    <td>{{ $supplier->city }}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editModal{{ $supplier->id }}"><i class="fas fa-edit"></i></button>
                        <form method="POST" action="{{ route('suppliers.destroy', $supplier) }}" class="d-inline">@csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this supplier?')"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-muted">No suppliers found</td></tr>
                @endforelse
            </tbody>
        </table>
        {{ $suppliers->links() }}
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="supplierModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('suppliers.store') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Add Supplier</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">Company Name</label><input type="text" class="form-control" name="name" required></div>
                    <div class="mb-3"><label class="form-label">Email</label><input type="email" class="form-control" name="email"></div>
                    <div class="mb-3"><label class="form-label">Phone</label><input type="text" class="form-control" name="phone"></div>
                    <div class="mb-3"><label class="form-label">City</label><input type="text" class="form-control" name="city"></div>
                    <div class="mb-3"><label class="form-label">Contact Person</label><input type="text" class="form-control" name="contact_person"></div>
                    <div class="mb-3"><label class="form-label">Address</label><textarea class="form-control" name="address" rows="2"></textarea></div>
                </div>
                <div class="modal-footer"><button type="submit" class="btn btn-primary">Save</button></div>
            </div>
        </form>
    </div>
</div>

@foreach($suppliers as $supplier)
<div class="modal fade" id="editModal{{ $supplier->id }}" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('suppliers.update', $supplier) }}">
            @csrf @method('PUT')
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Edit Supplier</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">Company Name</label><input type="text" class="form-control" name="name" value="{{ $supplier->name }}" required></div>
                    <div class="mb-3"><label class="form-label">Email</label><input type="email" class="form-control" name="email" value="{{ $supplier->email }}"></div>
                    <div class="mb-3"><label class="form-label">Phone</label><input type="text" class="form-control" name="phone" value="{{ $supplier->phone }}"></div>
                    <div class="mb-3"><label class="form-label">City</label><input type="text" class="form-control" name="city" value="{{ $supplier->city }}"></div>
                    <div class="mb-3"><label class="form-label">Contact Person</label><input type="text" class="form-control" name="contact_person" value="{{ $supplier->contact_person }}"></div>
                    <div class="mb-3"><label class="form-label">Address</label><textarea class="form-control" name="address" rows="2">{{ $supplier->address }}</textarea></div>
                </div>
                <div class="modal-footer"><button type="submit" class="btn btn-primary">Update</button></div>
            </div>
        </form>
    </div>
</div>
@endforeach
@endsection
