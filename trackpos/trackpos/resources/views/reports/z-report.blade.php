@extends('layouts.app')
@section('title', 'Z-Report')
@section('page-title', 'End of Day Report')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">End of Day Report</h4>
        <div class="d-flex gap-2">
            <form method="GET" class="d-flex gap-2">
                <input type="date" name="date" class="form-control" value="{{ $date->format('Y-m-d') }}">
                <select name="user_id" class="form-select" style="width: 180px;">
                    <option value="">All Users</option>
                    @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ $userId == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn" style="background: #7c3aed; color: #fff; border: none;">View</button>
            </form>
            <a href="{{ route('reports.z-report.print', ['date' => $date->format('Y-m-d')]) }}" target="_blank" class="btn btn-secondary">
                <i class="fas fa-print me-2"></i>Print
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Summary Cards -->
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h6 class="card-title">Total Sales</h6>
                    <h2 class="mb-0">{{ $totalSales }}</h2>
                    <small>Transactions</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6 class="card-title">Total Revenue</h6>
                    <h2 class="mb-0">${{ number_format($totalRevenue, 2) }}</h2>
                    <small>Before returns</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <h6 class="card-title">Total Returns</h6>
                    <h2 class="mb-0">${{ number_format($totalReturns, 2) }}</h2>
                    <small>{{ $returnCount }} items</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h6 class="card-title">Net Revenue</h6>
                    <h2 class="mb-0">${{ number_format($totalRevenue - $totalReturns, 2) }}</h2>
                    <small>After returns</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mt-2">
        <!-- Payment Methods -->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-credit-card me-2"></i>Payment Breakdown</h5>
                </div>
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Method</th>
                                <th>Transactions</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($payments as $method => $data)
                            <tr>
                                <td><span class="badge bg-{{ $method == 'cash' ? 'success' : ($method == 'card' ? 'primary' : 'info') }}">{{ ucfirst($method) }}</span></td>
                                <td>{{ $data['count'] }}</td>
                                <td>${{ number_format($data['total'], 2) }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="text-center">No sales</td></tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="fw-bold">
                                <td>Total</td>
                                <td>{{ $totalSales }}</td>
                                <td>${{ number_format($totalRevenue, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Cash Reconciliation -->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-money-bill-wave me-2"></i>Cash Reconciliation</h5>
                </div>
                <div class="card-body">
                    <table class="table">
                        <tbody>
                            <tr>
                                <td>Cash Sales</td>
                                <td class="text-end">${{ number_format($payments['cash']['total'] ?? 0, 2) }}</td>
                            </tr>
                            <tr>
                                <td>Cash Collected</td>
                                <td class="text-end">${{ number_format($payments['cash']['collected'] ?? 0, 2) }}</td>
                            </tr>
                            <tr>
                                <td>Change Given</td>
                                <td class="text-end">-${{ number_format($payments['cash']['change'] ?? 0, 2) }}</td>
                            </tr>
                            <tr>
                                <td>Cash Refunds</td>
                                <td class="text-end">-${{ number_format($returns->where('refund_method', 'cash')->sum('refund_amount'), 2) }}</td>
                            </tr>
                            <tr class="table-primary fw-bold">
                                <td>Expected Cash in Drawer</td>
                                <td class="text-end">${{ number_format($expectedCash, 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Products -->
    <div class="row g-4 mt-2">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Top Selling Products</h5>
                </div>
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Product</th>
                                <th>Quantity Sold</th>
                                <th>Total Sales</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topProducts as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $item->product->name }}</td>
                                <td>{{ $item->total_qty }}</td>
                                <td>${{ number_format($item->total_sales, 2) }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center">No sales data</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Sales by Category -->
    <div class="row g-4 mt-2">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-tags me-2" style="color: #7c3aed;"></i>Sales by Category</h5>
                </div>
                <div class="card-body">
                    @forelse($salesByCategory as $category)
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2" style="background: #f8fafc; padding: 10px 15px; border-radius: 8px;">
                            <h6 class="mb-0" style="color: #7c3aed;"><i class="fas fa-folder me-2"></i>{{ $category['category'] }}</h6>
                            <div>
                                <span class="badge" style="background: #7c3aed; color: #fff;">{{ $category['total_quantity'] }} items</span>
                                <span class="badge" style="background: #059669; color: #fff;">${{ number_format($category['total_sales'], 2) }}</span>
                            </div>
                        </div>
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Quantity</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($category['items'] as $item)
                                <tr>
                                    <td>{{ $item['product_name'] }}</td>
                                    <td>{{ $item['quantity'] }}</td>
                                    <td>${{ number_format($item['total'], 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if(!$loop->last)<hr>@endif
                    @empty
                    <p class="text-center text-muted">No sales data for this period</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Discounts & Tax -->
    <div class="row g-4 mt-2">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Total Discounts Given</h6>
                </div>
                <div class="card-body text-center">
                    <h3 class="text-warning">${{ number_format($totalDiscount, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Total Tax Collected</h6>
                </div>
                <div class="card-body text-center">
                    <h3 class="text-info">${{ number_format($totalTax, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Average Transaction</h6>
                </div>
                <div class="card-body text-center">
                    <h3 class="text-success">${{ $totalSales > 0 ? number_format($totalRevenue / $totalSales, 2) : '0.00' }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary at Bottom -->
    <div class="row g-4 mt-2">
        <div class="col-12">
            <div class="card" style="background: linear-gradient(135deg, #1e1b4b 0%, #0f0d1a 100%); border: none;">
                <div class="card-body">
                    <h5 class="mb-4" style="color: #fff;"><i class="fas fa-chart-pie me-2"></i>Summary</h5>
                    <div class="row">
                        <!-- Amount Sold -->
                        <div class="col-md-3">
                            <div style="background: rgba(255,255,255,0.1); padding: 20px; border-radius: 12px; text-align: center;">
                                <h6 style="color: #a78bfa; margin-bottom: 10px;">Total Amount Sold</h6>
                                <h3 style="color: #fff; margin: 0;">${{ number_format($totalRevenue, 2) }}</h3>
                            </div>
                        </div>
                        
                        <!-- Payment Methods -->
                        <div class="col-md-3">
                            <div style="background: rgba(255,255,255,0.1); padding: 20px; border-radius: 12px;">
                                <h6 style="color: #a78bfa; margin-bottom: 10px;">Cash Received</h6>
                                <h4 style="color: #10b981; margin: 0;">${{ number_format($payments['cash']['total'] ?? 0, 2) }}</h4>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div style="background: rgba(255,255,255,0.1); padding: 20px; border-radius: 12px;">
                                <h6 style="color: #a78bfa; margin-bottom: 10px;">Transfer Received</h6>
                                <h4 style="color: #3b82f6; margin: 0;">${{ number_format($payments['transfer']['total'] ?? 0, 2) }}</h4>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div style="background: rgba(255,255,255,0.1); padding: 20px; border-radius: 12px;">
                                <h6 style="color: #a78bfa; margin-bottom: 10px;">Card Received</h6>
                                <h4 style="color: #f59e0b; margin: 0;">${{ number_format($payments['card']['total'] ?? 0, 2) }}</h4>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <!-- Profit -->
                        <div class="col-md-6">
                            <div style="background: rgba(16, 185, 129, 0.2); padding: 20px; border-radius: 12px; border: 1px solid #10b981;">
                                <h6 style="color: #10b981; margin-bottom: 5px;">Total Profit</h6>
                                <h2 style="color: #10b981; margin: 0;">${{ number_format(max(0, $totalProfit), 2) }}</h2>
                            </div>
                        </div>
                        <!-- Loss -->
                        <div class="col-md-6">
                            <div style="background: rgba(239, 68, 68, 0.2); padding: 20px; border-radius: 12px; border: 1px solid #ef4444;">
                                <h6 style="color: #ef4444; margin-bottom: 5px;">Total Loss</h6>
                                <h2 style="color: #ef4444; margin: 0;">${{ number_format(max(0, $totalProfit < 0 ? abs($totalProfit) : 0), 2) }}</h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection