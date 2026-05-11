@extends('layouts.app')
@section('title', 'Payment Method Report')
@section('page-title', 'Payment Method Analysis')

<style>
@media print {
    .no-print { display: none !important; }
    .card { border: none !important; box-shadow: none !important; }
}
</style>

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3 no-print">
        <h4 class="mb-0"><i class="fas fa-credit-card me-2"></i>Payment Method Report</h4>
        <div>
            <a href="{{ route('reports.payment-method.export', ['date_from' => $dateFrom, 'date_to' => $dateTo]) }}" class="btn btn-info">
                <i class="fas fa-file-csv me-2"></i>Export CSV
            </a>
            <a href="{{ route('reports.index') }}" class="btn btn-secondary">Back</a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4 no-print">
        <div class="card-body">
            <form method="GET" action="{{ route('reports.payment-method') }}" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">From Date</label>
                    <input type="date" name="date_from" class="form-control" value="{{ $dateFrom }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">To Date</label>
                    <input type="date" name="date_to" class="form-control" value="{{ $dateTo }}">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter me-2"></i>Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Payment Method Breakdown -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6 class="mb-0">Total Revenue</h6>
                    <h3 class="mb-0">{{ $currencySymbol ?? '$' }}{{ number_format($totalRevenue, 2) }}</h3>
                </div>
            </div>
        </div>
        @foreach($paymentBreakdown as $method => $data)
        <div class="col-md-4">
            <div class="card bg-{{ $method == 'cash' ? 'warning' : ($method == 'card' ? 'primary' : 'info') }} text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0 text-capitalize">{{ $method }}</h6>
                            <small>{{ $data['count'] }} transactions</small>
                        </div>
                        <h4 class="mb-0">{{ $currencySymbol ?? '$' }}{{ number_format($data['total'], 2) }}</h4>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Detailed Breakdown -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Payment Method Summary</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Payment Method</th>
                                    <th class="text-end">Transactions</th>
                                    <th class="text-end">Subtotal</th>
                                    <th class="text-end">Discount</th>
                                    <th class="text-end">Tax</th>
                                    <th class="text-end">Total</th>
                                    <th class="text-end">% of Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($paymentBreakdown as $method => $data)
                                <tr>
                                    <td><span class="badge bg-{{ $method == 'cash' ? 'warning' : ($method == 'card' ? 'primary' : 'info') }}">{{ ucfirst($method) }}</span></td>
                                    <td class="text-end">{{ $data['count'] }}</td>
                                    <td class="text-end">{{ $currencySymbol ?? '$' }}{{ number_format($data['subtotal'], 2) }}</td>
                                    <td class="text-end">-{{ $currencySymbol ?? '$' }}{{ number_format($data['discount'], 2) }}</td>
                                    <td class="text-end">{{ $currencySymbol ?? '$' }}{{ number_format($data['tax'], 2) }}</td>
                                    <td class="text-end">{{ $currencySymbol ?? '$' }}{{ number_format($data['total'], 2) }}</td>
                                    <td class="text-end">{{ $totalRevenue > 0 ? number_format(($data['total'] / $totalRevenue) * 100, 1) : 0 }}%</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">No payment data found</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sales Details -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Transaction Details</h5>
        </div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Invoice</th>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Payment Method</th>
                        <th class="text-end">Subtotal</th>
                        <th class="text-end">Discount</th>
                        <th class="text-end">Tax</th>
                        <th class="text-end">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sales as $sale)
                    <tr>
                        <td>{{ $sale->invoice_no }}</td>
                        <td>{{ $sale->created_at->format('M d, H:i') }}</td>
                        <td>{{ $sale->customer?->name ?? 'Walk-in' }}</td>
                        <td><span class="badge bg-{{ $sale->payment_method == 'cash' ? 'warning' : ($sale->payment_method == 'card' ? 'primary' : 'info') }}">{{ ucfirst($sale->payment_method) }}</span></td>
                        <td class="text-end">{{ $currencySymbol ?? '$' }}{{ number_format($sale->subtotal, 2) }}</td>
                        <td class="text-end">-{{ $currencySymbol ?? '$' }}{{ number_format($sale->discount, 2) }}</td>
                        <td class="text-end">{{ $currencySymbol ?? '$' }}{{ number_format($sale->tax, 2) }}</td>
                        <td class="text-end">{{ $currencySymbol ?? '$' }}{{ number_format($sale->total, 2) }}</td>
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