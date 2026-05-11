@extends('layouts.app')
@section('title', 'Purchases')
@section('page-title', 'Purchase Orders')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Purchases</h4>
    <button class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>New Purchase
    </button>
</div>

<div class="card">
    <div class="card-body">
        <div class="row g-3 mb-3">
            <div class="col-md-3">
                <input type="date" class="form-control" name="date_from" placeholder="From Date">
            </div>
            <div class="col-md-3">
                <input type="date" class="form-control" name="date_to" placeholder="To Date">
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary w-100">Filter</button>
            </div>
        </div>
        
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Invoice</th>
                    <th>Supplier</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($purchases as $purchase)
                <tr>
                    <td>{{ $purchase->invoice_no }}</td>
                    <td>{{ $purchase->supplier?->name ?? 'N/A' }}</td>
                    <td>${{ number_format($purchase->total, 2) }}</td>
                    <td>
                        <span class="badge bg-{{ $purchase->status == 'completed' ? 'success' : 'warning' }}">
                            {{ ucfirst($purchase->status) }}
                        </span>
                    </td>
                    <td>{{ $purchase->created_at->format('M d, Y') }}</td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-muted">No purchases found</td></tr>
                @endforelse
            </tbody>
        </table>
        {{ $purchases->links() }}
    </div>
</div>
@endsection
