@extends('layouts.app')
@section('title', 'Sale Details')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Sale #{{ $sale->invoice_no }}</h4>
        <div>
            <a href="{{ route('sales.index') }}" class="btn btn-secondary">Back</a>
            <a href="{{ route('sales.print', $sale) }}" target="_blank" class="btn btn-primary">
                <i class="fas fa-print me-2"></i>Print Receipt
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Items</h5>
                </div>
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Qty</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sale->items as $item)
                            <tr>
                                <td>{{ $item->product->name }}</td>
                                <td>${{ number_format($item->unit_price, 2) }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>${{ number_format($item->subtotal, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Summary</h5>
                </div>
                <div class="card-body">
                    <div class="mb-2"><strong>Customer:</strong> {{ $sale->customer?->name ?? 'Walk-in' }}</div>
                    <div class="mb-2"><strong>Cashier:</strong> {{ $sale->user->name }}</div>
                    <div class="mb-2"><strong>Date:</strong> {{ $sale->created_at->format('M d, Y H:i') }}</div>
                    <div class="mb-2"><strong>Status:</strong> 
                        <span class="badge bg-{{ $sale->status == 'completed' ? 'success' : 'warning' }}">
                            {{ ucfirst($sale->status) }}
                        </span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-2"><span>Subtotal:</span><span>${{ number_format($sale->subtotal, 2) }}</span></div>
                    <div class="d-flex justify-content-between mb-2"><span>Discount:</span><span>${{ number_format($sale->discount, 2) }}</span></div>
                    <div class="d-flex justify-content-between mb-2"><span>Tax:</span><span>${{ number_format($sale->tax, 2) }}</span></div>
                    <hr>
                    <div class="d-flex justify-content-between"><h5>Total:</h5><h5>${{ number_format($sale->total, 2) }}</h5></div>
                    <div class="d-flex justify-content-between mb-2"><span>Paid ({{ $sale->payment_method }}):</span><span>${{ number_format($sale->paid_amount, 2) }}</span></div>
                    <div class="d-flex justify-content-between"><span>Change:</span><span>${{ number_format($sale->change_amount, 2) }}</span></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
