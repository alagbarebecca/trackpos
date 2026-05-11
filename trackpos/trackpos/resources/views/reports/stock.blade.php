@extends('layouts.app')
@section('title', 'Stock Report')
@section('page-title', 'Inventory Report')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Stock Report</h4>
        <a href="{{ route('reports.index') }}" class="btn btn-secondary">Back</a>
    </div>

    <div class="card">
        <div class="card-body">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Category</th>
                        <th>Stock</th>
                        <th>Min Level</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $product)
                    <tr>
                        <td>{{ $product->name }}</td>
                        <td>{{ $product->category?->name }}</td>
                        <td>{{ $product->stock_quantity }}</td>
                        <td>{{ $product->min_stock_level }}</td>
                        <td>
                            @if($product->stock_quantity <= $product->min_stock_level)
                            <span class="badge bg-danger">Low Stock</span>
                            @else
                            <span class="badge bg-success">In Stock</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
