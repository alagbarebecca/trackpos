@extends('layouts.app')
@section('title', 'Tax Report')
@section('page-title', 'Tax Collection Report')

<style>
@media print {
    .no-print { display: none !important; }
    .card { border: none !important; box-shadow: none !important; }
    .card-body { padding: 10px !important; }
    body { font-size: 12px; }
    .table { font-size: 11px; }
    .col-md-3 { width: 25% !important; float: left; }
}
</style>

@section('content')
<div class="container-fluid no-print mb-3">
    <div class="d-flex justify-content-between align-items-center">
        <h4 class="mb-0"><i class="fas fa-file-invoice-dollar me-2"></i>Tax Collection Report</h4>
        <div>
            <a href="{{ route('reports.tax.export', ['date_from' => $dateFrom, 'date_to' => $dateTo]) }}" class="btn btn-info">
                <i class="fas fa-file-csv me-2"></i>Export CSV
            </a>
            <a href="{{ route('reports.tax.print', ['date_from' => $dateFrom, 'date_to' => $dateTo]) }}" target="_blank" class="btn btn-success">
                <i class="fas fa-print me-2"></i>Print / PDF
            </a>
            <a href="{{ route('reports.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back
            </a>
        </div>
    </div>
</div>

<div class="container-fluid">
    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">Total Tax Collected</h6>
                            <h3 class="mb-0">{{ $currencySymbol ?? '$' }}{{ number_format($totalTax, 2) }}</h3>
                        </div>
                        <div><i class="fas fa-receipt fa-2x opacity-50"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">Total Sales</h6>
                            <h3 class="mb-0">{{ $currencySymbol ?? '$' }}{{ number_format($totalRevenue, 2) }}</h3>
                        </div>
                        <div><i class="fas fa-dollar-sign fa-2x opacity-50"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">Total Subtotal</h6>
                            <h3 class="mb-0">{{ $currencySymbol ?? '$' }}{{ number_format($totalSales, 2) }}</h3>
                        </div>
                        <div><i class="fas fa-shopping-cart fa-2x opacity-50"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">Total Discount</h6>
                            <h3 class="mb-0">{{ $currencySymbol ?? '$' }}{{ number_format($totalDiscount, 2) }}</h3>
                        </div>
                        <div><i class="fas fa-percent fa-2x opacity-50"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Date Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('reports.tax') }}" class="row g-3 align-items-end">
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
                <div class="col-md-2">
                    <a href="{{ route('reports.tax', ['date_from' => now()->startOfMonth()->toDateString(), 'date_to' => now()->endOfMonth()->toDateString()]) }}" class="btn btn-outline-secondary w-100">
                        This Month
                    </a>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('reports.tax', ['date_from' => now()->startOfYear()->toDateString(), 'date_to' => now()->endOfYear()->toDateString()]) }}" class="btn btn-outline-info w-100">
                        This Year
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        <!-- Daily Breakdown -->
        <div class="col-md-7">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-calendar-day me-2"></i>Daily Tax Summary</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th class="text-end">Sales</th>
                                    <th class="text-end">Subtotal</th>
                                    <th class="text-end">Tax</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($dailyTax as $date => $data)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($date)->format('M d, Y') }}</td>
                                    <td class="text-end">{{ $data['sales_count'] }}</td>
                                    <td class="text-end">{{ $currencySymbol ?? '$' }}{{ number_format($data['subtotal'], 2) }}</td>
                                    <td class="text-end text-success">{{ $currencySymbol ?? '$' }}{{ number_format($data['tax'], 2) }}</td>
                                    <td class="text-end">{{ $currencySymbol ?? '$' }}{{ number_format($data['total'], 2) }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">No sales data found</td>
                                </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="table-secondary">
                                <tr>
                                    <th>Total</th>
                                    <th class="text-end">{{ $sales->count() }}</th>
                                    <th class="text-end">{{ $currencySymbol ?? '$' }}{{ number_format($totalSales, 2) }}</th>
                                    <th class="text-end text-success">{{ $currencySymbol ?? '$' }}{{ number_format($totalTax, 2) }}</th>
                                    <th class="text-end">{{ $currencySymbol ?? '$' }}{{ number_format($totalRevenue, 2) }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tax Rate Info -->
        <div class="col-md-5">
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Tax Remittance Summary</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td>Period</td>
                            <td class="text-end"><strong>{{ \Carbon\Carbon::parse($dateFrom)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($dateTo)->format('M d, Y') }}</strong></td>
                        </tr>
                        <tr>
                            <td>Total Sales Count</td>
                            <td class="text-end"><strong>{{ $sales->count() }}</strong></td>
                        </tr>
                        <tr class="border-top">
                            <td><strong>Gross Sales</strong></td>
                            <td class="text-end"><strong>{{ $currencySymbol ?? '$' }}{{ number_format($totalSales, 2) }}</strong></td>
                        </tr>
                        <tr>
                            <td>Less: Discounts</td>
                            <td class="text-end">-{{ $currencySymbol ?? '$' }}{{ number_format($totalDiscount, 2) }}</td>
                        </tr>
                        <tr class="border-top">
                            <td><strong>Net Sales</strong></td>
                            <td class="text-end"><strong>{{ $currencySymbol ?? '$' }}{{ number_format($totalSales - $totalDiscount, 2) }}</strong></td>
                        </tr>
                        <tr class="bg-success text-white">
                            <td><strong>Tax Collected</strong></td>
                            <td class="text-end"><strong>{{ $currencySymbol ?? '$' }}{{ number_format($totalTax, 2) }}</strong></td>
                        </tr>
                    </table>
                    
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-lightbulb me-2"></i>
                        <strong>Note:</strong> This report shows tax collected from sales. Use this amount for tax remittance to your local tax authority.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sales Details -->
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Sales Details</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
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
                            <td class="text-end text-success">{{ $currencySymbol ?? '$' }}{{ number_format($sale->tax, 2) }}</td>
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
</div>
@endsection