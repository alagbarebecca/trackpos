@extends('layouts.app')

@section('title', 'Staff Management')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>Staff Management</h4>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEmployeeModal">
                        <i class="fas fa-plus"></i> Add Employee
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
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Designation</th>
                                    <th>Hourly Rate</th>
                                    <th>Hire Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($employees as $employee)
                                <tr>
                                    <td>{{ $employee->id }}</td>
                                    <td>{{ $employee->user->name ?? 'N/A' }}</td>
                                    <td>{{ $employee->designation }}</td>
                                    <td>${{ number_format($employee->hourly_rate ?? 0, 2) }}</td>
                                    <td>{{ $employee->hire_date ? \Carbon\Carbon::parse($employee->hire_date)->format('Y-m-d') : '-' }}</td>
                                    <td>
                                        <a href="{{ route('staff.show', $employee) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button class="btn btn-sm btn-danger" onclick="confirmDelete({{ $employee->id }})">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center">No employees found</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    {{ $employees->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Employee Modal -->
<div class="modal fade" id="addEmployeeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('staff.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add Employee</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>User</label>
                        <select name="user_id" class="form-control" required>
                            <option value="">Select User</option>
                            @foreach(\App\Models\User::whereDoesntHave('employee')->get() as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Designation</label>
                        <input type="text" name="designation" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Hourly Rate</label>
                        <input type="number" name="hourly_rate" class="form-control" step="0.01" min="0" value="0">
                    </div>
                    <div class="mb-3">
                        <label>Hire Date</label>
                        <input type="date" name="hire_date" class="form-control">
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

<script>
function confirmDelete(id) {
    if(confirm('Are you sure you want to delete this employee?')) {
        fetch('/staff/' + id, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        }).then(response => window.location.reload());
    }
}
</script>
@endsection