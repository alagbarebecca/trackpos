@extends('layouts.app')
@section('title', 'Customer Sales Report')
@section('page-title', 'Customer Purchase History')

<style>
@media print {
    .no-print { display: none !important; }
    .card { border: none !important; box-shadow: none !important; }
}
</style>

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3 no-print">
        <h4 class="mb-0"><i class="fas fa-users me-2"></i>Customer Sales Report</h4>
        <div>
            <a href="{{ route('reports.customer-sales.export', ['date_from' => $dateFrom, 'date_to' => $dateTo, 'customer_id' => $customerId]) }}" class="btn btn-info">
                <i class="fas fa-file-csv me-2"></i>Export CSV
            </a>
            <a href="{{ route('reports.index') }}" class="btn btn-secondary">Back</a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4 no-print">
        <div class="card-body">
            <form method="GET" action="{{ route('reports.customer-sales') }}" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Customer</label>
                    <select name="customer_id" class="form-select">
                        <option value="">All Customers</option>
                        @foreach($customers as $customer)
                        <option value="{{ $customer->id }}" {{ $customerId == $customer->id ? 'selected' : '' }}>{{ $customer->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">From Date</label>
                    <input type="date" name="date_from" class="form-control" value="{{ $dateFrom }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">To Date</label>
                    <input type="date" name="date_to" class="form-control" value="{{ $dateTo }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter me-2"></i>Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Top Customers Summary -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-trophy me-2"></i>Top Customers</h5>
                </div>
                <div class="card-body">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Customer</th>
                                <th class="text-end">Total Purchases</th>
                                <th class="text-end">Total Spent</th>
                                <th>Last Purchase</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($customerSummary as $index => $summary)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $summary['name'] }}</td>
                                <td class="text-end">{{ $summary['sales_count'] }}</td>
                                <td class="text-end">{{ $currencySymbol ?? '$' }}{{ number_format($summary['total_spent'], 2) }}</td>
                                <td>{{ $summary['last_purchase'] ? $summary['last_purchase']->format('M d, Y') : '-' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No customer data found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Sales Details -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Sales Details</h5>
        </div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Invoice</th>
                        <th>Date</th>
                        <th>Customer</th>
                        <th class="text-end">Subtotal</th>
                        <th class="text-end">Discount</th>
                        <th class="text-end">Tax</th>
                        <th class="text-end">Total</th>
                        <th>Payment</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sales as $sale)
                    <tr>
                        <td>{{ $sale->invoice_no }}</td>
                        <td>{{ $sale->created_at->format('M d, H:i') }}</td>
                        <td>{{ $sale->customer?->name ?? 'Walk-in' }}</td>
                        <td class="text-end">{{ $currencySymbol ?? '$' }}{{ number_format($sale->subtotal, 2) }}</td>
                        <td class="text-end">-{{ $currencySymbol ?? '$' }}{{ number_format($sale->discount, 2) }}</td>
                        <td class="text-end">{{ $currencySymbol ?? '$' }}{{ number_format($sale->tax, 2) }}</td>
                        <td class="text-end">{{ $currencySymbol ?? '$' }}{{ number_format($sale->total, 2) }}</td>
                        <td><span class="badge bg-info">{{ ucfirst($sale->payment_method) }}</span></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">No sales found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection