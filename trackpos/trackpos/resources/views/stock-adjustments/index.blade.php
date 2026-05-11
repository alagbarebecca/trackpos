@extends('layouts.app')
@section('title', 'Stock Adjustments')
@section('page-title', 'Inventory Adjustments')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Stock Adjustments</h4>
        <a href="{{ route('stock-adjustments.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>New Adjustment
        </a>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <select name="type" class="form-select">
                        <option value="">All Types</option>
                        <option value="addition" {{ request('type') == 'addition' ? 'selected' : '' }}>Addition</option>
                        <option value="deduction" {{ request('type') == 'deduction' ? 'selected' : '' }}>Deduction</option>
                        <option value="correction" {{ request('type') == 'correction' ? 'selected' : '' }}>Correction</option>
                        <option value="returned" {{ request('type') == 'returned' ? 'selected' : '' }}>Returned</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('stock-adjustments.index') }}" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Product</th>
                            <th>Type</th>
                            <th>Qty Change</th>
                            <th>From → To</th>
                            <th>Reason</th>
                            <th>User</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($adjustments as $adj)
                        <tr>
                            <td>{{ $adj->created_at->format('M d, Y H:i') }}</td>
                            <td>{{ $adj->product->name }}</td>
                            <td>
                                <span class="badge bg-{{ $adj->quantity >= 0 ? 'success' : 'danger' }}">
                                    {{ ucfirst($adj->type) }}
                                </span>
                            </td>
                            <td class="{{ $adj->quantity >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ $adj->quantity >= 0 ? '+' : '' }}{{ $adj->quantity }}
                            </td>
                            <td>{{ $adj->previous_quantity }} → {{ $adj->new_quantity }}</td>
                            <td>{{ Str::limit($adj->reason, 40) }}</td>
                            <td>{{ $adj->user->name }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No adjustments found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $adjustments->links() }}
        </div>
    </div>
</div>
@endsection