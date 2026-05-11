@extends('layouts.app')
@section('title', 'Stock Adjustment')
@section('page-title', 'Adjust Stock')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">New Stock Adjustment</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('stock-adjustments.store') }}">
                        @csrf
                        <input type="hidden" name="redirect" value="{{ request('redirect') }}">
                        <div class="mb-3">
                            <label class="form-label">Product</label>
                            <select class="form-select" name="product_id" required>
                                <option value="">Select Product...</option>
                                @foreach($products as $product)
                                <option value="{{ $product->id }}" {{ $selectedProduct && $selectedProduct->id == $product->id ? 'selected' : '' }}>
                                    {{ $product->name }} (Current: {{ $product->stock_quantity }})
                                </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Adjustment Type</label>
                            <select class="form-select" name="type" required>
                                <option value="add">Stock In (Add)</option>
                                <option value="remove">Stock Out (Remove)</option>
                                <option value="transfer">Transfer</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Quantity</label>
                            <input type="number" class="form-control" name="quantity" required min="1" placeholder="Enter quantity">
                            <small class="text-muted">Enter positive number (system will handle + or - based on type)</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Reason</label>
                            <textarea class="form-control" name="reason" rows="3" required placeholder="Reason for adjustment..."></textarea>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save Adjustment</button>
                            <a href="{{ request('redirect') === 'products' ? route('products.index') : route('stock-adjustments.index') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection