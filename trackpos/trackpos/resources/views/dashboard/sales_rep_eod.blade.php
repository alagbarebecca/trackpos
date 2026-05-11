@extends('layouts.app')
@section('title', 'End of Day Report')
@section('page-title', 'End of Day Report')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-success text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        <i class="fas fa-file-invoice-dollar me-2"></i>
                        END OF DAY REPORT
                    </h4>
                    <div>
                        <a href="/dashboard" class="btn btn-sm btn-light">
                            <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
                        </a>
                        <button class="btn btn-sm btn-primary" onclick="window.print()">
                            <i class="fas fa-print me-1"></i>Print
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <!-- Header Info -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td class="text-muted">Sales Rep:</td>
                                <th>{{ auth()->user()->name }}</th>
                            </tr>
                            <tr>
                                <td class="text-muted">Date:</td>
                                <th>{{ now()->format('Y-m-d') }}</th>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6 text-md-right">
                        <table class="table table-borderless">
                            <tr>
                                <td class="text-muted">Time:</td>
                                <th>{{ now()->format('H:i:s') }}</th>
                            </tr>
                            <tr>
                                <td class="text-muted">Report Type:</td>
                                <th>Daily Sales Summary</th>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Sales Summary -->
                <h5 class="bg-light p-2 mb-3">SALES SUMMARY</h5>
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card bg-primary text-white">
                            <div class="card-body text-center">
                                <h6 class="card-title">TOTAL SALES</h6>
                                <h2 class="mb-0">${{ number_format($todaySales, 2) }}</h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-info text-white">
                            <div class="card-body text-center">
                                <h6 class="card-title">TRANSACTIONS</h6>
                                <h2 class="mb-0">{{ $todayTransactions }}</h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-secondary text-white">
                            <div class="card-body text-center">
                                <h6 class="card-title">AVERAGE</h6>
                                <h2 class="mb-0">${{ $todayTransactions > 0 ? number_format($todaySales / $todayTransactions, 2) : '0.00' }}</h2>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Period Summary -->
                <h5 class="bg-light p-2 mb-3">PERIOD SUMMARY</h5>
                <table class="table table-bordered mb-4">
                    <thead class="bg-light">
                        <tr>
                            <th>Period</th>
                            <th class="text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Today</td>
                            <th class="text-right">${{ number_format($todaySales, 2) }}</th>
                        </tr>
                        <tr>
                            <td>This Week</td>
                            <th class="text-right">${{ number_format($weekSales, 2) }}</th>
                        </tr>
                        <tr>
                            <td>This Month</td>
                            <th class="text-right">${{ number_format($monthSales, 2) }}</th>
                        </tr>
                    </tbody>
                </table>

                <!-- Transaction Details -->
                <h5 class="bg-light p-2 mb-3">TRANSACTION DETAILS</h5>
                @if($todayTransactionsList->count() > 0)
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead class="bg-light">
                            <tr>
                                <th>#</th>
                                <th>Time</th>
                                <th>Invoice</th>
                                <th>Customer</th>
                                <th class="text-right">Amount</th>
                                <th>Payment</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($todayTransactionsList as $index => $sale)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $sale->created_at->format('H:i:s') }}</td>
                                <td>{{ $sale->invoice_no }}</td>
                                <td>{{ $sale->customer?->name ?? 'Walk-in' }}</td>
                                <td class="text-right">${{ number_format($sale->total, 2) }}</td>
                                <td>{{ ucfirst($sale->payment_method) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-light">
                            <tr>
                                <th colspan="4" class="text-right">TOTAL</th>
                                <th class="text-right">${{ number_format($todaySales, 2) }}</th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                @else
                <p class="text-center text-muted">No transactions today</p>
                @endif

                <!-- Products Sold -->
                @if($todaySalesByProduct->count() > 0)
                <h5 class="bg-light p-2 mb-3 mt-4">PRODUCTS SOLD TODAY</h5>
                <table class="table table-bordered">
                    <thead class="bg-light">
                        <tr>
                            <th>Product</th>
                            <th class="text-right">Quantity</th>
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
                @endif

                <!-- Footer -->
                <div class="text-center mt-4 pt-4 border-top">
                    <p class="text-muted mb-0">
                        End of Day Report - Generated on {{ now()->format('Y-m-d H:i:s') }}
                    </p>
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
    .card-header { background: #28a745 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .bg-light { background: #f0f0f0 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    body { font-size: 11px; }
    .table { font-size: 10px; }
}
</style>
@endsection