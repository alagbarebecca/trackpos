@extends('layouts.app')
@section('title', 'Expenses')
@section('page-title', 'Expense Management')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Expenses</h4>
        <div class="d-flex gap-2">
            <a href="{{ route('expenses.categories') }}" class="btn" style="background: #64748b; color: #fff; border: none;">
                <i class="fas fa-tags me-2"></i>Manage Categories
            </a>
            <button class="btn" style="background: #7c3aed; color: #fff; border: none;" data-bs-toggle="modal" data-bs-target="#addExpenseModal">
                <i class="fas fa-plus me-2"></i>Add Expense
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Start Date</label>
                    <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">End Date</label>
                    <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Category</label>
                    <select name="category_id" class="form-select">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn" style="background: #7c3aed; color: #fff; border: none;">Filter</button>
                    <a href="{{ route('expenses.index') }}" class="btn btn-secondary ms-2">Clear</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Total Expenses -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card" style="background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);">
                <div class="card-body text-white text-center">
                    <h6 class="mb-1">Total Expenses</h6>
                    <h2 class="mb-0">${{ number_format($totalExpenses, 2) }}</h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Expenses Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-receipt me-2" style="color: #7c3aed;"></i>Expense List</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Reference</th>
                            <th>Category</th>
                            <th>Description</th>
                            <th>Amount</th>
                            <th>Created By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($expenses as $expense)
                        <tr>
                            <td>{{ $expense->date->format('M d, Y') }}</td>
                            <td>{{ $expense->reference ?? '-' }}</td>
                            <td><span class="badge" style="background: #7c3aed; color: #fff;">{{ $expense->category->name }}</span></td>
                            <td>{{ $expense->description ?? '-' }}</td>
                            <td class="fw-bold text-danger">${{ number_format($expense->amount, 2) }}</td>
                            <td>{{ $expense->user->name ?? 'System' }}</td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editExpenseModal{{ $expense->id }}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form action="{{ route('expenses.destroy', $expense) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>

                        <!-- Edit Modal -->
                        <div class="modal fade" id="editExpenseModal{{ $expense->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="{{ route('expenses.update', $expense) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-header" style="background: linear-gradient(135deg, #1e1b4b 0%, #0f0d1a 100%); color: #fff;">
                                            <h5 class="modal-title">Edit Expense</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label">Category</label>
                                                <select name="expense_category_id" class="form-select" required>
                                                    @foreach($categories as $category)
                                                    <option value="{{ $category->id }}" {{ $expense->expense_category_id == $category->id ? 'selected' : '' }}>
                                                        {{ $category->name }}
                                                    </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Amount</label>
                                                <input type="number" name="amount" class="form-control" step="0.01" value="{{ $expense->amount }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Date</label>
                                                <input type="date" name="date" class="form-control" value="{{ $expense->date->format('Y-m-d') }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Reference</label>
                                                <input type="text" name="reference" class="form-control" value="{{ $expense->reference }}">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Description</label>
                                                <textarea name="description" class="form-control" rows="2">{{ $expense->description }}</textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="submit" class="btn" style="background: #7c3aed; color: #fff; border: none;">Update</button>
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="fas fa-receipt fa-2x mb-2 d-block opacity-50"></i>
                                No expenses found
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($expenses->hasPages())
        <div class="card-footer">
            {{ $expenses->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Add Expense Modal -->
<div class="modal fade" id="addExpenseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('expenses.store') }}" method="POST">
                @csrf
                <div class="modal-header" style="background: linear-gradient(135deg, #1e1b4b 0%, #0f0d1a 100%); color: #fff;">
                    <h5 class="modal-title">Add New Expense</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Category *</label>
                        <select name="expense_category_id" class="form-select" required>
                            <option value="">Select Category</option>
                            @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Amount *</label>
                        <input type="number" name="amount" class="form-control" step="0.01" placeholder="0.00" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date *</label>
                        <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reference (Optional)</label>
                        <input type="text" name="reference" class="form-control" placeholder="e.g., INV-001">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description (Optional)</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Enter description..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn" style="background: #7c3aed; color: #fff; border: none;">Save Expense</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection