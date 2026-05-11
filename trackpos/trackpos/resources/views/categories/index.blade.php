@extends('layouts.app')
@section('title', 'Categories')
@section('page-title', 'Product Categories')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-5">
            <!-- Add Category Form -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-plus me-2"></i>Add New Category</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('categories.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Category Name</label>
                            <input type="text" class="form-control" name="name" required placeholder="e.g., Electronics">
                        </div>
                        @if(Schema::hasColumn('categories', 'parent_id'))
                        <div class="mb-3">
                            <label class="form-label">Parent Category (optional)</label>
                            <select class="form-select" name="parent_id">
                                <option value="">-- No Parent (Main Category) --</option>
                                @foreach($allCategories as $cat)
                                <option value="{{ $cat->id }}">- {{ $cat->name }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Leave empty for main category</small>
                        </div>
                        @endif
                        <div class="mb-3">
                            <label class="form-label">Description (optional)</label>
                            <textarea class="form-control" name="description" rows="2" placeholder="Brief description"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Save Category
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-7">
            <!-- Categories List -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-sitemap me-2"></i>Category Tree</h5>
                    <span class="badge bg-primary">{{ $allCategories->count() }}</span>
                </div>
                <div class="card-body p-0">
                    @if(session('success'))
                        <div class="alert alert-success m-3 mb-0">{{ session('success') }}</div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger m-3 mb-0">{{ session('error') }}</div>
                    @endif
                    
                    @if($allCategories->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Category</th>
                                    <th>Type</th>
                                    <th>Products</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($categories as $category)
                                <tr class="table-light">
                                    <td>
                                        <i class="fas fa-folder text-primary me-2"></i>
                                        <strong>{{ $category->name }}</strong>
                                        @if($category->description)
                                        <br><small class="text-muted">{{ $category->description }}</small>
                                        @endif
                                    </td>
                                    <td><span class="badge bg-primary">Main</span></td>
                                    <td><span class="badge bg-secondary">{{ $category->products()->count() }}</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editModal{{ $category->id }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form action="{{ route('categories.destroy', $category->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                
                                <!-- Edit Modal for Main Category -->
                                <div class="modal fade" id="editModal{{ $category->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Edit Category</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form action="{{ route('categories.update', $category->id) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label">Category Name</label>
                                                        <input type="text" class="form-control" name="name" value="{{ $category->name }}" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Description</label>
                                                        <textarea class="form-control" name="description" rows="2">{{ $category->description }}</textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-primary">Save Changes</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Subcategories -->
                                @php
                                $subcategories = [];
                                if ($category->relationLoaded('subcategories')) {
                                    $subcategories = $category->subcategories;
                                } elseif (Schema::hasColumn('categories', 'parent_id') && method_exists($category, 'subcategories')) {
                                    $subcategories = $category->subcategories;
                                }
                                @endphp
                                @if(count($subcategories) > 0)
                                @foreach($subcategories as $subcategory)
                                <tr>
                                    <td class="ps-4">
                                        <i class="fas fa-folder-open text-warning me-2"></i>
                                        {{ $subcategory->name }}
                                        @if($subcategory->description)
                                        <br><small class="text-muted ps-4">{{ $subcategory->description }}</small>
                                        @endif
                                    </td>
                                    <td><span class="badge bg-warning text-dark">Sub</span></td>
                                    <td><span class="badge bg-secondary">{{ $subcategory->products()->count() }}</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editSubModal{{ $subcategory->id }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form action="{{ route('categories.destroy', $subcategory->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                
                                <!-- Edit Modal for Subcategory -->
                                <div class="modal fade" id="editSubModal{{ $subcategory->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Edit Subcategory</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form action="{{ route('categories.update', $subcategory->id) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label">Category Name</label>
                                                        <input type="text" class="form-control" name="name" value="{{ $subcategory->name }}" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Description</label>
                                                        <textarea class="form-control" name="description" rows="2">{{ $subcategory->description }}</textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-primary">Save Changes</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                                @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-folder-open fa-2x mb-2"></i>
                        <p>No categories yet. Create one to get started!</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection