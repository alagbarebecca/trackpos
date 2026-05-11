@extends('layouts.app')
@section('title', 'Low Stock Products')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Low Stock Products</h4>
        <a href="{{ route('products.index') }}" class="btn btn-secondary">Back</a>
    </div>

    <div class="card">
        <div class="card-body">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>SKU</th>
                        <th>Category</th>
                        <th>Stock</th>
                        <th>Min Level</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $product)
                    <tr>
                        <td>{{ $product->name }}</td>
                        <td>{{ $product->sku }}</td>
                        <td>{{ $product->category?->name }}</td>
                        <td><span class="badge bg-danger">{{ $product->stock_quantity }}</span></td>
                        <td>{{ $product->min_stock_level }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $products->links() }}
        </div>
    </div>
</div>
@endsection
