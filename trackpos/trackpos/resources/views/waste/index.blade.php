@extends('layouts.app')
@section('title', 'Waste Tracking')
@section('page-title', 'Waste Tracking')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Waste / Spoilage Records</h4>
        <button class="btn" style="background: #dc2626; color: #fff; border: none;" data-bs-toggle="modal" data-bs-target="#wasteModal">
            <i class="fas fa-plus me-2"></i>Record Waste
        </button>
    </div>

    <!-- Stats -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h6 class="text-white-50">Total Items Lost</h6>
                    <h3 class="mb-0">{{ $totalQuantity }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <h6 class="text-dark-50">Total Value Lost</h6>
                    <h3 class="mb-0">${{ number_format($totalValue, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-secondary text-white">
                <div class="card-body">
                    <h6 class="text-white-50">Total Records</h6>
                    <h3 class="mb-0">{{ $records->total() }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Records Table -->
    <div class="card">
        <div class="card-body p-0">
            <table class="table mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Reason</th>
                        <th>Value</th>
                        <th>Recorded By</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $record)
                    <tr>
                        <td>{{ $record->created_at->format('M d, Y') }}</td>
                        <td>{{ $record->product->name ?? '-' }}</td>
                        <td>{{ $record->quantity }}</td>
                        <td>
                            @if($record->reason == 'expired')<span class="badge bg-warning">Expired</span>
                            @elseif($record->reason == 'damaged')<span class="badge bg-danger">Damaged</span>
                            @elseif($record->reason == 'spoiled')<span class="badge bg-info">Spoiled</span>
                            @elseif($record->reason == 'stolen')<span class="badge bg-dark">Stolen</span>
                            @else<span class="badge bg-secondary">Other</span>@endif
                        </td>
                        <td>${{ number_format($record->value, 2) }}</td>
                        <td>{{ $record->user->name ?? '-' }}</td>
                        <td>
                            <form action="{{ route('waste.destroy', $record) }}" method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-success" onclick="return confirm('Restore stock?')">
                                    <i class="fas fa-undo"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No waste records</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="wasteModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('waste.store') }}" method="POST">
                @csrf
                <div class="modal-header" style="background: #dc2626; color: #fff;">
                    <h5 class="modal-title">Record Waste</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Product *</label>
                        <select name="product_id" class="form-select" required>
                            <option value="">Select Product</option>
                            @foreach(\App\Models\Product::where('status', true)->where('stock_quantity', '>', 0)->orderBy('name')->get() as $p)
                            <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->stock_quantity }} in stock)</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Quantity *</label>
                        <input type="number" name="quantity" class="form-control" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reason *</label>
                        <select name="reason" class="form-select" required>
                            <option value="expired">Expired</option>
                            <option value="damaged">Damaged</option>
                            <option value="spoiled">Spoiled</option>
                            <option value="stolen">Stolen</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn" style="background: #dc2626; color: #fff; border: none;">Record Waste</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection