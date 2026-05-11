@extends('layouts.app')
@section('title', 'Products')
@section('page-title', 'Product Management')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Products</h4>
        <div>
            <a href="{{ route('products.import') }}" class="btn btn-info">
                <i class="fas fa-file-import me-2"></i>Import
            </a>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#productModal">
                <i class="fas fa-plus me-2"></i>Add Product
            </button>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="row g-3">
                <div class="col-md-4">
                    <input type="text" class="form-control" placeholder="Search..." name="search" value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="category">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                            @foreach($category->subcategories as $sub)
                            <option value="{{ $sub->id }}" {{ request('category') == $sub->id ? 'selected' : '' }}>&nbsp;&nbsp;└ {{ $sub->name }}</option>
                            @endforeach
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>SKU</th>
                            <th>Category</th>
                            <th>Brand</th>
                            <th>Cost</th>
                            <th>Sell Price</th>
                            <th>Stock</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($products as $product)
                        <tr>
                            <td>{{ $product->name }}</td>
                            <td>{{ $product->sku }}</td>
                            <td>{{ $product->category?->name }}</td>
                            <td>{{ $product->brand?->name }}</td>
                            <td>${{ number_format($product->cost_price, 2) }}</td>
                            <td>${{ number_format($product->sell_price, 2) }}</td>
                            <td>
                                @if($product->stock_quantity <= $product->min_stock_level)
                                <span class="badge bg-warning">{{ $product->stock_quantity }}</span>
                                @else
                                <span class="badge bg-success">{{ $product->stock_quantity }}</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $product->status ? 'success' : 'secondary' }}">
                                    {{ $product->status ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editModal{{ $product->id }}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <a href="{{ route('stock-adjustments.create') }}?product_id={{ $product->id }}&redirect=products" class="btn btn-sm btn-outline-warning" title="Stock Adjustment">
                                    <i class="fas fa-boxes"></i>
                                </a>
                                <form method="POST" action="{{ route('products.destroy', $product) }}" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this product?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $products->links() }}
        </div>
    </div>
</div>

<!-- Add Product Modal -->
<div class="modal fade" id="productModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">SKU</label>
                            <input type="text" class="form-control" name="sku" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Barcode</label>
                            <input type="text" class="form-control" name="barcode">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Product Image</label>
                        <input type="file" class="form-control" name="image" accept="image/*">
                        <small class="text-muted">JPG, PNG, GIF, WebP - Max 2MB</small>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Category</label>
                            <select class="form-select" name="category_id" required>
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @foreach($category->subcategories as $sub)
                                    <option value="{{ $sub->id }}">&nbsp;&nbsp;└ {{ $sub->name }}</option>
                                    @endforeach
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Brand</label>
                            <select class="form-select" name="brand_id">
                                <option value="">None</option>
                                @foreach($brands as $brand)
                                <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Cost Price</label>
                            <input type="number" class="form-control" name="cost_price" step="0.01" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Sell Price</label>
                            <input type="number" class="form-control" name="sell_price" step="0.01" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Stock Quantity</label>
                            <input type="number" class="form-control" name="stock_quantity" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Min Stock Level</label>
                            <input type="number" class="form-control" name="min_stock_level" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </div>
        </form>
    </div>
</div>

@foreach($products as $product)
<div class="modal fade" id="editModal{{ $product->id }}" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('products.update', $product) }}" enctype="multipart/form-data">
            @csrf @method('PUT')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control" name="name" value="{{ $product->name }}" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">SKU</label>
                            <input type="text" class="form-control" name="sku" value="{{ $product->sku }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Barcode</label>
                            <input type="text" class="form-control" name="barcode" value="{{ $product->barcode }}">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Product Image</label>
                        @if($product->image)
                        <div class="mb-2">
                            <img src="{{ asset($product->image) }}" alt="{{ $product->name }}" style="max-width: 100px; max-height: 100px;">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="remove_image" id="remove_image{{ $product->id }}">
                                <label class="form-check-label" for="remove_image{{ $product->id }}">Remove image</label>
                            </div>
                        </div>
                        @endif
                        <input type="file" class="form-control" name="image" accept="image/*">
                        <small class="text-muted">Leave empty to keep current image</small>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Category</label>
                            <select class="form-select" name="category_id" required>
                                @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ $product->category_id == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                    @foreach($category->subcategories as $sub)
                                    <option value="{{ $sub->id }}" {{ $product->category_id == $sub->id ? 'selected' : '' }}>&nbsp;&nbsp;└ {{ $sub->name }}</option>
                                    @endforeach
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Brand</label>
                            <select class="form-select" name="brand_id">
                                <option value="">None</option>
                                @foreach($brands as $brand)
                                <option value="{{ $brand->id }}" {{ $product->brand_id == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Cost Price</label>
                            <input type="number" class="form-control" name="cost_price" value="{{ $product->cost_price }}" step="0.01" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Sell Price</label>
                            <input type="number" class="form-control" name="sell_price" value="{{ $product->sell_price }}" step="0.01" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Stock Quantity</label>
                            <input type="number" class="form-control" name="stock_quantity" value="{{ $product->stock_quantity }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Min Stock Level</label>
                            <input type="number" class="form-control" name="min_stock_level" value="{{ $product->min_stock_level }}" required>
                        </div>
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
