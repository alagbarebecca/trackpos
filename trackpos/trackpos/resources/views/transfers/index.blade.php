@extends('layouts.app')
@section('title', 'Stock Transfers')
@section('page-title', 'Stock Transfers')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Stock Transfers</h4>
        <button class="btn" style="background: #7c3aed; color: #fff; border: none;" data-bs-toggle="modal" data-bs-target="#transferModal">
            <i class="fas fa-plus me-2"></i>New Transfer
        </button>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <table class="table mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Transfer #</th>
                        <th>From</th>
                        <th>To</th>
                        <th>Status</th>
                        <th>Created By</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transfers as $transfer)
                    <tr>
                        <td class="fw-semibold">{{ $transfer->transfer_no }}</td>
                        <td>{{ $transfer->from_location }}</td>
                        <td>{{ $transfer->to_location }}</td>
                        <td>
                            @if($transfer->status == 'pending')<span class="badge bg-warning">Pending</span>
                            @elseif($transfer->status == 'sent')<span class="badge bg-info">Sent</span>
                            @elseif($transfer->status == 'received')<span class="badge bg-success">Received</span>
                            @else<span class="badge bg-danger">Cancelled</span>@endif
                        </td>
                        <td>{{ $transfer->user->name ?? '-' }}</td>
                        <td>{{ $transfer->created_at->format('M d, Y') }}</td>
                        <td>
                            <a href="{{ route('transfers.show', $transfer) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a>
                            @if($transfer->status == 'pending')
                            <form action="{{ route('transfers.update', $transfer) }}" method="POST" class="d-inline">
                                @csrf @method('PUT')
                                <input type="hidden" name="status" value="sent">
                                <button type="submit" class="btn btn-sm btn-outline-success"><i class="fas fa-truck"></i></button>
                            </form>
                            @endif
                            @if($transfer->status == 'sent')
                            <form action="{{ route('transfers.update', $transfer) }}" method="POST" class="d-inline">
                                @csrf @method('PUT')
                                <input type="hidden" name="status" value="received">
                                <button type="submit" class="btn btn-sm btn-outline-success"><i class="fas fa-check"></i></button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No transfers</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="transferModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('transfers.store') }}" method="POST">
                @csrf
                <div class="modal-header" style="background: #7c3aed; color: #fff;">
                    <h5 class="modal-title">New Stock Transfer</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">From Location *</label>
                            <input type="text" name="from_location" class="form-control" placeholder="Main Warehouse" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">To Location *</label>
                            <input type="text" name="to_location" class="form-control" placeholder="Store B" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Products</label>
                        <div id="transferItems">
                            <div class="row g-2 mb-2 transfer-item">
                                <div class="col-md-7">
                                    <select name="items[0][product_id]" class="form-select">
                                        <option value="">Select Product</option>
                                        @foreach($products as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <input type="number" name="items[0][quantity]" class="form-control" placeholder="Qty" min="1" value="1">
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm" id="addTransferItem">+ Add Product</button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn" style="background: #7c3aed; color: #fff; border: none;">Create Transfer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let itemIndex = 1;
document.getElementById('addTransferItem').onclick = function() {
    document.getElementById('transferItems').insertAdjacentHTML('beforeend', `
        <div class="row g-2 mb-2 transfer-item">
            <div class="col-md-7">
                <select name="items[${itemIndex}[product_id]" class="form-select">
                    <option value="">Select Product</option>
                    @foreach($products as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-4">
                <input type="number" name="items[${itemIndex}][quantity]" class="form-control" placeholder="Qty" min="1" value="1">
            </div>
        </div>
    `);
    itemIndex++;
};
</script>
@endsection