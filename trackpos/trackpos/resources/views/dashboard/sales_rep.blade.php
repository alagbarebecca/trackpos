@extends('layouts.app')
@section('title', 'My Dashboard')
@section('page-title', 'Sales Representative Dashboard')

@section('content')
<div class="row">
    <div class="col-12">
        <h4 class="mb-4">Welcome, {{ auth()->user()->name }}!</h4>
    </div>
</div>

<!-- Today's Summary Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h6 class="card-title">Today's Sales</h6>
                <h2 class="mb-0">${{ number_format($todaySales, 2) }}</h2>
                <small>{{ $todayTransactions }} transactions</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h6 class="card-title">This Week</h6>
                <h2 class="mb-0">${{ number_format($weekSales, 2) }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <h6 class="card-title">This Month</h6>
                <h2 class="mb-0">${{ number_format($monthSales, 2) }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <h6 class="card-title">Today's Transactions</h6>
                <h2 class="mb-0">{{ $todayTransactions }}</h2>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Today's Transactions -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Today's Transactions</h5>
                <button class="btn btn-sm btn-primary" onclick="window.print()">
                    <i class="fas fa-print"></i> Print Report
                </button>
            </div>
            <div class="card-body">
                @if($todayTransactionsList->count() > 0)
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>Invoice</th>
                                <th>Customer</th>
                                <th>Amount</th>
                                <th>Payment</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($todayTransactionsList as $sale)
                            <tr>
                                <td>{{ $sale->created_at->format('H:i') }}</td>
                                <td>{{ $sale->invoice_no }}</td>
                                <td>{{ $sale->customer?->name ?? 'Walk-in' }}</td>
                                <td>${{ number_format($sale->total, 2) }}</td>
                                <td>{{ ucfirst($sale->payment_method) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="table-primary">
                                <th colspan="3">Total</th>
                                <th>${{ number_format($todaySales, 2) }}</th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                @else
                <p class="text-center text-muted py-4">No sales today yet</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Sales by Product -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Today's Top Products</h5>
            </div>
            <div class="card-body">
                @if($todaySalesByProduct->count() > 0)
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th class="text-right">Qty</th>
                            <th class="text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($todaySalesByProduct as $item)
                        <tr>
                            <td>{{ $item->product?->name ?? 'Product #'.$item->product_id }}</td>
                            <td class="text-right">{{ $item->total_qty }}</td>
                            <td class="text-right">${{ number_format($item->total_subtotal, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                <p class="text-center text-muted py-4">No products sold today</p>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- End of Day Report Section -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">
                    <i class="fas fa-file-alt me-2"></i>End of Day Report
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-muted">SALES SUMMARY</h6>
                        <table class="table table-bordered">
                            <tr>
                                <td>Total Sales Amount</td>
                                <th class="text-right">${{ number_format($todaySales, 2) }}</th>
                            </tr>
                            <tr>
                                <td>Number of Transactions</td>
                                <th class="text-right">{{ $todayTransactions }}</th>
                            </tr>
                            <tr>
                                <td>Average Transaction Value</td>
                                <th class="text-right">${{ $todayTransactions > 0 ? number_format($todaySales / $todayTransactions, 2) : '0.00' }}</th>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted">PERIOD SUMMARY</h6>
                        <table class="table table-bordered">
                            <tr>
                                <td>This Week</td>
                                <th class="text-right">${{ number_format($weekSales, 2) }}</th>
                            </tr>
                            <tr>
                                <td>This Month</td>
                                <th class="text-right">${{ number_format($monthSales, 2) }}</th>
                            </tr>
                            <tr>
                                <td>Date</td>
                                <th class="text-right">{{ now()->format('Y-m-d') }}</th>
                            </tr>
                        </table>
                    </div>
                </div>
                <div class="mt-3 text-center">
                    <button class="btn btn-success btn-lg" onclick="window.print()">
                        <i class="fas fa-print me-2"></i>Print End of Day Report
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
@media print {
    .no-print { display: none !important; }
    .card { border: 1px solid #000 !important; }
    .card-header { background: #f0f0f0 !important; color: #000 !important; }
    body { font-size: 12px; }
}
</style>
@endsection