@extends('layouts.app')
@section('title', 'Customer Loyalty')
@section('page-title', 'Customer Loyalty')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Customer Loyalty Program</h4>
        <a href="{{ route('loyalty.settings') }}" class="btn btn-outline-primary">
            <i class="fas fa-cog me-2"></i>Settings
        </a>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h6 class="text-white-50">Total Members</h6>
                    <h3 class="mb-0">{{ $customers->total() }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6 class="text-white-50">Points Issued</h6>
                    <h3 class="mb-0">{{ number_format($customers->sum(fn($c) => $c->reward?->total_points_earned ?? 0)) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <h6 class="text-dark-50">Points Active</h6>
                    <h3 class="mb-0">{{ number_format($customers->sum(fn($c) => $c->reward?->points ?? 0)) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h6 class="text-white-50">$1 = {{ $pointsPerDollar }} pts</h6>
                    <h3 class="mb-0">${{ $redemptionValue }} /pt</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Search -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-6">
                    <input type="text" name="search" class="form-control" placeholder="Search customers..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary">Search</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Customers Table -->
    <div class="card">
        <div class="card-body p-0">
            <table class="table mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Customer</th>
                        <th>Points Balance</th>
                        <th>Total Earned</th>
                        <th>Total Redeemed</th>
                        <th>Lifetime Value</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $customer)
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $customer->name }}</div>
                            <small class="text-muted">{{ $customer->phone }} | {{ $customer->email }}</small>
                        </td>
                        <td>
                            <span class="badge bg-success">{{ $customer->reward?->points ?? 0 }} pts</span>
                        </td>
                        <td>{{ $customer->reward?->total_points_earned ?? 0 }}</td>
                        <td>{{ $customer->reward?->total_points_redeemed ?? 0 }}</td>
                        <td>${{ number_format($customer->reward?->lifetime_value ?? 0, 2) }}</td>
                        <td>
                            <a href="{{ route('loyalty.show', $customer) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            No customers found
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($customers->hasPages())
        <div class="card-footer">{{ $customers->links() }}</div>
        @endif
    </div>
</div>
@endsection