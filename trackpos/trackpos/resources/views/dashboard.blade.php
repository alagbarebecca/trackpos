@extends('layouts.app')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

<style>
/* Modern Dashboard Styles - Purple Theme */
.dashboard-header {
    display: none; /* Removed header */
}

.stat-card-modern {
    background: white;
    border-radius: 16px;
    padding: 1.5rem;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
    border: none;
    height: 100%;
    position: relative;
    overflow: hidden;
}

.stat-card-modern::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #7c3aed, #a78bfa);
}

.stat-card-modern:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 30px rgba(124, 58, 237, 0.2);
}

.stat-icon {
    width: 56px;
    height: 56px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}

.stat-icon.sales { background: linear-gradient(135deg, #7c3aed 0%, #a78bfa 100%); color: white; }
.stat-icon.transactions { background: linear-gradient(135deg, #059669 0%, #10b981 100%); color: white; }
.stat-icon.products { background: linear-gradient(135deg, #dc2626 0%, #f87171 100%); color: white; }
.stat-icon.stock { background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%); color: white; }

.stat-number {
    font-size: 2rem;
    font-weight: 700;
    color: #1e293b;
    line-height: 1;
    margin-top: 0.5rem;
}

.stat-label {
    color: #64748b;
    font-size: 0.875rem;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.stat-change {
    font-size: 0.75rem;
    font-weight: 600;
    padding: 4px 8px;
    border-radius: 20px;
    margin-top: 0.5rem;
    display: inline-block;
}

.stat-change.positive { background: #dcfce7; color: #166534; }
.stat-change.negative { background: #fee2e2; color: #991b1b; }

.quick-action-btn {
    padding: 1rem;
    border-radius: 12px;
    background: #f8fafc;
    border: 2px dashed #e2e8f0;
    color: #1e293b;
    font-weight: 600;
    transition: all 0.3s ease;
    text-align: center;
    text-decoration: none;
    display: block;
}

.quick-action-btn:hover {
    background: linear-gradient(135deg, #7c3aed 0%, #a78bfa 100%);
    color: white;
    border-color: transparent;
    transform: scale(1.02);
}

.quick-action-btn i {
    font-size: 1.5rem;
    margin-bottom: 0.5rem;
    display: block;
}

.chart-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    border: none;
}

.chart-card .card-header {
    background: transparent;
    border-bottom: 1px solid #e2e8f0;
    padding: 1.25rem 1.5rem;
}

.chart-card .card-body {
    padding: 1.5rem;
}

/* Sleek Modern Chart Styles */
.sleek-chart {
    position: relative;
    height: 280px;
    padding: 20px 0;
}

.chart-line {
    position: absolute;
    bottom: 40px;
    left: 40px;
    right: 20px;
    height: 200px;
}

.chart-area {
    position: absolute;
    bottom: 40px;
    left: 40px;
    right: 20px;
    height: 200px;
    background: linear-gradient(180deg, rgba(124, 58, 237, 0.3) 0%, rgba(124, 58, 237, 0.05) 100%);
    border-radius: 12px;
}

.chart-svg {
    width: 100%;
    height: 100%;
}

.chart-path {
    fill: none;
    stroke: #7c3aed;
    stroke-width: 3;
    stroke-linecap: round;
    stroke-linejoin: round;
}

.chart-dot {
    fill: #7c3aed;
    stroke: white;
    stroke-width: 2;
}

.chart-grid-line {
    stroke: #e2e8f0;
    stroke-width: 1;
    stroke-dasharray: 4;
}

.chart-label {
    font-size: 0.75rem;
    color: #64748b;
    position: absolute;
    bottom: 10px;
    transform: translateX(-50%);
}

.chart-value {
    font-size: 0.7rem;
    font-weight: 600;
    color: #7c3aed;
    position: absolute;
    transform: translateY(-50%);
}

.activity-item {
    display: flex;
    align-items: center;
    padding: 1rem 0;
    border-bottom: 1px solid #f0f0f0;
}

.activity-item:last-child {
    border-bottom: none;
}

.activity-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1rem;
    flex-shrink: 0;
}

.activity-content {
    flex: 1;
}

.activity-title {
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 2px;
}

.activity-time {
    font-size: 0.75rem;
    color: #a0aec0;
}

.badge-modern {
    padding: 6px 12px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.75rem;
}

.progress-card {
    background: white;
    border-radius: 12px;
    padding: 1rem;
    margin-bottom: 1rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.progress-label {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.5rem;
    font-weight: 500;
}

.progress-value {
    color: #667eea;
}

/* Animations */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in {
    animation: fadeInUp 0.5s ease forwards;
}

.animate-delay-1 { animation-delay: 0.1s; }
.animate-delay-2 { animation-delay: 0.2s; }
.animate-delay-3 { animation-delay: 0.3s; }
.animate-delay-4 { animation-delay: 0.4s; }

/* Bar chart styles - Purple Theme */
.bar-chart {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    height: 200px;
    padding: 1rem 0;
}

.bar-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    flex: 1;
}

.bar {
    width: 30px;
    background: linear-gradient(180deg, #7c3aed 0%, #a78bfa 100%);
    border-radius: 6px 6px 0 0;
    transition: all 0.3s ease;
}

.bar:hover {
    background: linear-gradient(180deg, #a78bfa 0%, #c4b5fd 100%);
}

.bar-label {
    font-size: 0.7rem;
    color: #64748b;
    margin-top: 0.5rem;
}

.bar-value {
    font-size: 0.65rem;
    font-weight: 600;
    color: #7c3aed;
    margin-bottom: 0.25rem;
}
</style>

@section('content')
<!-- Stats Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-3 animate-fade-in animate-delay-1">
        <div class="stat-card-modern">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <span class="stat-label">Today's Sales</span>
                    <div class="stat-number">{{ $currencySymbol }}{{ number_format($todaySales, 2) }}</div>
                    <span class="stat-change positive">
                        <i class="fas fa-arrow-up me-1"></i>vs yesterday
                    </span>
                </div>
                <div class="stat-icon sales">
                    <i class="fas fa-dollar-sign"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 animate-fade-in animate-delay-2">
        <div class="stat-card-modern">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <span class="stat-label">Transactions</span>
                    <div class="stat-number">{{ $todayTransactions }}</div>
                    <span class="stat-change positive">
                        <i class="fas fa-arrow-up me-1"></i>vs yesterday
                    </span>
                </div>
                <div class="stat-icon transactions">
                    <i class="fas fa-receipt"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 animate-fade-in animate-delay-3">
        <div class="stat-card-modern">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <span class="stat-label">Low Stock Items</span>
                    <div class="stat-number">{{ $lowStockProducts }}</div>
                    @if($lowStockProducts > 0)
                    <span class="stat-change negative">
                        <i class="fas fa-exclamation-circle me-1"></i>Needs attention
                    </span>
                    @else
                    <span class="stat-change positive">
                        <i class="fas fa-check-circle me-1"></i>All good
                    </span>
                    @endif
                </div>
                <div class="stat-icon stock">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 animate-fade-in animate-delay-4">
        <div class="stat-card-modern">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <span class="stat-label">Total Products</span>
                    <div class="stat-number">{{ $totalProducts }}</div>
                    <span class="stat-change positive">
                        <i class="fas fa-box me-1"></i>In stock
                    </span>
                </div>
                <div class="stat-icon products">
                    <i class="fas fa-box"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart & Stock by Category -->
<div class="row g-4 mb-4">
    <!-- Sleek Line Chart -->
    <div class="col-lg-8">
        <div class="chart-card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-chart-line me-2" style="color: #7c3aed;"></i>Sales Overview</h5>
                <div class="btn-group">
                    <button class="btn btn-sm btn-outline-secondary active">Week</button>
                    <button class="btn btn-sm btn-outline-secondary">Month</button>
                </div>
            </div>
            <div class="card-body">
                <div class="sleek-chart">
                    <!-- Chart Area Background -->
                    <div class="chart-area"></div>
                    
                    <!-- SVG Chart -->
                    <svg class="chart-svg" viewBox="0 0 700 200" preserveAspectRatio="none">
                        <!-- Grid Lines -->
                        <line x1="0" y1="40" x2="700" y2="40" class="chart-grid-line"/>
                        <line x1="0" y1="80" x2="700" y2="80" class="chart-grid-line"/>
                        <line x1="0" y1="120" x2="700" y2="120" class="chart-grid-line"/>
                        <line x1="0" y1="160" x2="700" y2="160" class="chart-grid-line"/>
                        
                        <!-- Gradient Definition -->
                        <defs>
                            <linearGradient id="chartGradient" x1="0%" y1="0%" x2="0%" y2="100%">
                                <stop offset="0%" style="stop-color:#7c3aed;stop-opacity:0.4" />
                                <stop offset="100%" style="stop-color:#7c3aed;stop-opacity:0" />
                            </linearGradient>
                        </defs>
                        
                        <!-- Area Fill -->
                        <path d="M0,180 Q50,160 100,140 T200,120 T300,100 T400,80 T500,60 T600,40 T700,20 L700,200 L0,200 Z" fill="url(#chartGradient)"/>
                        
                        <!-- Line Path -->
                        <path class="chart-path" d="M0,180 Q50,160 100,140 T200,120 T300,100 T400,80 T500,60 T600,40 T700,20"/>
                        
                        <!-- Data Points -->
                        <circle cx="0" cy="180" r="5" class="chart-dot"/>
                        <circle cx="100" cy="140" r="5" class="chart-dot"/>
                        <circle cx="200" cy="120" r="5" class="chart-dot"/>
                        <circle cx="300" cy="100" r="5" class="chart-dot"/>
                        <circle cx="400" cy="80" r="5" class="chart-dot"/>
                        <circle cx="500" cy="60" r="5" class="chart-dot"/>
                        <circle cx="600" cy="40" r="5" class="chart-dot"/>
                        <circle cx="700" cy="20" r="5" class="chart-dot"/>
                    </svg>
                    
                    <!-- X-Axis Labels -->
                    <span class="chart-label" style="left: 50px;">Mon</span>
                    <span class="chart-label" style="left: 150px;">Tue</span>
                    <span class="chart-label" style="left: 250px;">Wed</span>
                    <span class="chart-label" style="left: 350px;">Thu</span>
                    <span class="chart-label" style="left: 450px;">Fri</span>
                    <span class="chart-label" style="left: 550px;">Sat</span>
                    <span class="chart-label" style="left: 650px;">Sun</span>
                    
                    <!-- Y-Axis Labels -->
                    <span class="chart-value" style="left: 5px; top: 35px;">$3k</span>
                    <span class="chart-value" style="left: 5px; top: 75px;">$2k</span>
                    <span class="chart-value" style="left: 5px; top: 115px;">$1k</span>
                    <span class="chart-value" style="left: 5px; top: 155px;">$0</span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Stock by Category -->
    <div class="col-lg-4">
        <div class="chart-card h-100">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-tags me-2" style="color: #7c3aed;"></i>Stock by Category</h5>
            </div>
            <div class="card-body">
                @php
                $categories = \App\Models\Category::withCount('products')->orderBy('products_count', 'desc')->limit(5)->get();
                @endphp
                @forelse($categories as $cat)
                <div class="progress-card">
                    <div class="progress-label">
                        <span>{{ $cat->name }}</span>
                        <span class="progress-value" style="color: #7c3aed;">{{ $cat->products_count }}</span>
                    </div>
                    <div class="progress" style="height: 8px; border-radius: 4px;">
                        <div class="progress-bar" role="progressbar" 
                             style="width: {{ ($cat->products_count / max($categories->pluck('products_count')->toArray())) * 100 }}%; 
                             background: linear-gradient(90deg, #7c3aed, #a78bfa);" 
                             aria-valuenow="{{ $cat->products_count }}" aria-valuemin="0" 
                             aria-valuemax="{{ max($categories->pluck('products_count')->toArray()) }}">
                        </div>
                    </div>
                </div>
                @empty
                <p class="text-muted text-center">No categories</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Recent Sales & Activity -->
<div class="row g-4">
    <!-- Recent Sales -->
    <div class="col-lg-8">
        <div class="chart-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-shopping-cart me-2" style="color: #7c3aed;"></i>Recent Sales</h5>
                <a href="{{ route('sales.index') }}" class="btn btn-sm" style="background: #7c3aed; color: #fff; border: none;">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Invoice</th>
                                <th>Customer</th>
                                <th>Payment</th>
                                <th>Total</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentSales as $sale)
                            <tr>
                                <td>
                                    <a href="{{ route('sales.show', $sale) }}" class="text-decoration-none fw-semibold">
                                        {{ $sale->invoice_no }}
                                    </a>
                                </td>
                                <td>{{ $sale->customer?->name ?? 'Walk-in' }}</td>
                                <td>
                                    <span class="badge-modern bg-{{ $sale->payment_method == 'cash' ? 'warning' : 'info' }}">
                                        {{ ucfirst($sale->payment_method) }}
                                    </span>
                                </td>
                                <td class="fw-semibold">{{ $currencySymbol }}{{ number_format($sale->total, 2) }}</td>
                                <td class="text-muted">{{ $sale->created_at->format('M d, H:i') }}</td>
                                <td>
                                    <span class="badge-modern bg-{{ $sale->status == 'completed' ? 'success' : 'secondary' }}">
                                        {{ ucfirst($sale->status) }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="fas fa-receipt fa-2x mb-2 d-block opacity-50"></i>
                                    No recent sales
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Low Stock Alert -->
    <div class="col-lg-4">
        <div class="chart-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2" style="color: #f59e0b;"></i>Low Stock Alert</h5>
                <a href="{{ route('products.low-stock') }}" class="btn btn-sm btn-link">View All</a>
            </div>
            <div class="card-body p-0">
                @php
                $lowStock = \App\Models\Product::whereRaw('stock_quantity <= COALESCE(min_stock_level, 5)')->limit(5)->get();
                @endphp
                @forelse($lowStock as $product)
                <div class="activity-item">
                    <div class="activity-icon bg-warning bg-opacity-10 text-warning">
                        <i class="fas fa-box"></i>
                    </div>
                    <div class="activity-content">
                        <div class="activity-title">{{ $product->name }}</div>
                        <div class="activity-time">
                            <span class="text-danger fw-semibold">{{ $product->stock_quantity }}</span> remaining
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-4">
                    <i class="fas fa-check-circle text-success fa-2x mb-2 d-block"></i>
                    <p class="text-muted mb-0">All products well stocked!</p>
                </div>
                @endforelse
            </div>
        </div>
        
        <!-- Top Products -->
        <div class="chart-card mt-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-fire me-2 text-danger"></i>Top Selling Products</h5>
            </div>
            <div class="card-body p-0">
                @php
                $topProducts = \App\Models\SaleItem::selectRaw('product_id, SUM(quantity) as total_qty')
                    ->with('product')
                    ->groupBy('product_id')
                    ->orderByDesc('total_qty')
                    ->limit(5)
                    ->get();
                @endphp
                @forelse($topProducts as $index => $item)
                <div class="activity-item">
                    <div class="activity-icon bg-{{ ['primary', 'success', 'info', 'warning', 'danger'][$index] }} bg-opacity-10 text-{{ ['primary', 'success', 'info', 'warning', 'danger'][$index] }}">
                        <span class="fw-bold">{{ $index + 1 }}</span>
                    </div>
                    <div class="activity-content">
                        <div class="activity-title">{{ $item->product?->name ?? 'Unknown' }}</div>
                        <div class="activity-time">{{ $item->total_qty }} units sold</div>
                    </div>
                </div>
                @empty
                <div class="text-center py-3">
                    <p class="text-muted mb-0">No sales data yet</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
