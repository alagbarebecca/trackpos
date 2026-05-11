@extends('layouts.app')
@section('title', 'Customers')
@section('page-title', 'Customer Management')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Customers</h4>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#customerModal">
        <i class="fas fa-plus me-2"></i>Add Customer
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
                @forelse($customers as $customer)
                <tr>
                    <td>{{ $customer->name }}</td>
                    <td>{{ $customer->email }}</td>
                    <td>{{ $customer->phone }}</td>
                    <td>{{ $customer->city }}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editModal{{ $customer->id }}"><i class="fas fa-edit"></i></button>
                        <form method="POST" action="{{ route('customers.destroy', $customer) }}" class="d-inline">@csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this customer?')"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-muted">No customers found</td></tr>
                @endforelse
            </tbody>
        </table>
        {{ $customers->links() }}
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="customerModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('customers.store') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Add Customer</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">Name</label><input type="text" class="form-control" name="name" required></div>
                    <div class="mb-3"><label class="form-label">Email</label><input type="email" class="form-control" name="email"></div>
                    <div class="mb-3"><label class="form-label">Phone</label><input type="text" class="form-control" name="phone"></div>
                    <div class="mb-3"><label class="form-label">City</label><input type="text" class="form-control" name="city"></div>
                    <div class="mb-3"><label class="form-label">Address</label><textarea class="form-control" name="address" rows="2"></textarea></div>
                </div>
                <div class="modal-footer"><button type="submit" class="btn btn-primary">Save</button></div>
            </div>
        </form>
    </div>
</div>

@foreach($customers as $customer)
<div class="modal fade" id="editModal{{ $customer->id }}" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('customers.update', $customer) }}">
            @csrf @method('PUT')
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Edit Customer</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">Name</label><input type="text" class="form-control" name="name" value="{{ $customer->name }}" required></div>
                    <div class="mb-3"><label class="form-label">Email</label><input type="email" class="form-control" name="email" value="{{ $customer->email }}"></div>
                    <div class="mb-3"><label class="form-label">Phone</label><input type="text" class="form-control" name="phone" value="{{ $customer->phone }}"></div>
                    <div class="mb-3"><label class="form-label">City</label><input type="text" class="form-control" name="city" value="{{ $customer->city }}"></div>
                    <div class="mb-3"><label class="form-label">Address</label><textarea class="form-control" name="address" rows="2">{{ $customer->address }}</textarea></div>
                </div>
                <div class="modal-footer"><button type="submit" class="btn btn-primary">Update</button></div>
            </div>
        </form>
    </div>
</div>
@endforeach
@endsection
