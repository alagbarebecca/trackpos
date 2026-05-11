@extends('layouts.app')
@section('title', 'Expense Categories')
@section('page-title', 'Manage Expense Categories')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Expense Categories</h4>
        <button class="btn" style="background: #7c3aed; color: #fff; border: none;" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
            <i class="fas fa-plus me-2"></i>Add Category
        </button>
    </div>

    <!-- Categories Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-tags me-2" style="color: #7c3aed;"></i>Categories List</h5>
            <a href="{{ route('expenses.index') }}" class="btn btn-sm" style="background: #64748b; color: #fff; border: none;">
                <i class="fas fa-arrow-left me-2"></i>Back to Expenses
            </a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Expenses Count</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $category)
                        <tr>
                            <td class="fw-semibold">{{ $category->name }}</td>
                            <td>{{ $category->description ?? '-' }}</td>
                            <td><span class="badge" style="background: #7c3aed; color: #fff;">{{ $category->expenses()->count() }}</span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editCategoryModal{{ $category->id }}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form action="{{ route('expenses.categories.destroy', $category) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure? This will delete the category.')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>

                        <!-- Edit Modal -->
                        <div class="modal fade" id="editCategoryModal{{ $category->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="{{ route('expenses.categories.update', $category) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-header" style="background: linear-gradient(135deg, #1e1b4b 0%, #0f0d1a 100%); color: #fff;">
                                            <h5 class="modal-title">Edit Category</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label">Name</label>
                                                <input type="text" name="name" class="form-control" value="{{ $category->name }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Description</label>
                                                <textarea name="description" class="form-control" rows="2">{{ $category->description }}</textarea>
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
                            <td colspan="4" class="text-center text-muted py-4">
                                <i class="fas fa-tags fa-2x mb-2 d-block opacity-50"></i>
                                No categories found. Create one to get started.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('expenses.categories.store') }}" method="POST">
                @csrf
                <div class="modal-header" style="background: linear-gradient(135deg, #1e1b4b 0%, #0f0d1a 100%); color: #fff;">
                    <h5 class="modal-title">Add New Category</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Category Name *</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g., Rent, Utilities, Supplies" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description (Optional)</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Enter description..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn" style="background: #7c3aed; color: #fff; border: none;">Save Category</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection