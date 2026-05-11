@extends('layouts.app')
@section('title', 'Sales')
@section('page-title', 'Sales Transactions')

<style>
.reprint-btn { margin-left: 5px; }
</style>

@section('content')
<div class="container-fluid">
    <h4 class="mb-4">Sales</h4>

    <div class="card">
        <div class="card-body">
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <input type="text" class="form-control" placeholder="Search by invoice or customer..." id="salesSearch">
                </div>
                <div class="col-md-3">
                    <input type="date" class="form-control" name="date_from" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-3">
                    <input type="date" class="form-control" name="date_to" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary w-100">Search</button>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Invoice</th>
                            <th>Customer</th>
                            <th>Total</th>
                            <th>Payment</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sales as $sale)
                        <tr>
                            <td>{{ $sale->invoice_no }}</td>
                            <td>{{ $sale->customer?->name ?? 'Walk-in' }}</td>
                            <td>${{ number_format($sale->total, 2) }}</td>
                            <td>
                                <span class="badge bg-{{ $sale->payment_method == 'cash' ? 'success' : 'info' }}">
                                    {{ ucfirst($sale->payment_method) }}
                                </span>
                            </td>
                            <td>{{ $sale->created_at->format('M d, Y H:i') }}</td>
                            <td>
                                <a href="{{ route('sales.show', $sale) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('sales.print', $sale) }}" target="_blank" class="btn btn-sm btn-outline-secondary reprint-btn" title="Print Receipt">
                                    <i class="fas fa-print"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $sales->links() }}
        </div>
    </div>
</div>
@endsection
