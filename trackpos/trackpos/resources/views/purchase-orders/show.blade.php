@extends('layouts.app')
@section('title', 'Purchase Order')
@section('page-title', 'Purchase Order Details')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0">PO: {{ $purchaseOrder->purchase_no }}</h4>
            <small class="text-muted">Created {{ $purchaseOrder->created_at->format('F d, Y g:i A') }}</small>
        </div>
        <div>
            <a href="{{ route('purchase-orders.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back
            </a>
            @if($purchaseOrder->status == 'pending')
            <form action="{{ route('purchase-orders.update', $purchaseOrder) }}" method="POST" class="d-inline">
                @csrf
                @method('PUT')
                <input type="hidden" name="status" value="ordered">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-check me-2"></i>Mark as Ordered
                </button>
            </form>
            @endif
            @if($purchaseOrder->status == 'ordered')
            <form action="{{ route('purchase-orders.update', $purchaseOrder) }}" method="POST" class="d-inline">
                @csrf
                @method('PUT')
                <input type="hidden" name="status" value="received">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-check-double me-2"></i>Receive Order
                </button>
            </form>
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Order Items</h5>
                </div>
                <div class="card-body p-0">
                    <table class="table mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Product</th>
                                <th>SKU</th>
                                <th class="text-center">Qty Ordered</th>
                                <th class="text-center">Qty Received</th>
                                <th class="text-end">Unit Cost</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($purchaseOrder->items as $item)
                            <tr>
                                <td>{{ $item->product->name ?? '-' }}</td>
                                <td>{{ $item->product->sku ?? '-' }}</td>
                                <td class="text-center">{{ $item->quantity }}</td>
                                <td class="text-center">{{ $item->received_quantity }}</td>
                                <td class="text-end">${{ number_format($item->unit_cost, 2) }}</td>
                                <td class="text-end">${{ number_format($item->total, 2) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">No items</td>
                            </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="5" class="text-end fw-bold">Total:</td>
                                <td class="text-end fw-bold">${{ number_format($purchaseOrder->total, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Order Details</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <td>Status</td>
                            <td>
                                @if($purchaseOrder->status == 'pending')
                                <span class="badge bg-warning">Pending</span>
                                @elseif($purchaseOrder->status == 'ordered')
                                <span class="badge bg-info">Ordered</span>
                                @elseif($purchaseOrder->status == 'received')
                                <span class="badge bg-success">Received</span>
                                @else
                                <span class="badge bg-danger">Cancelled</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td>Supplier</td>
                            <td>{{ $purchaseOrder->supplier->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td>Created By</td>
                            <td>{{ $purchaseOrder->user->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td>Ordered Date</td>
                            <td>{{ $purchaseOrder->ordered_at?->format('M d, Y') : '-' }}</td>
                        </tr>
                        <tr>
                            <td>Received Date</td>
                            <td>{{ $purchaseOrder->received_at?->format('M d, Y') : '-' }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Summary</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal:</span>
                        <span>${{ number_format($purchaseOrder->subtotal, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Tax:</span>
                        <span>${{ number_format($purchaseOrder->tax_amount, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Discount:</span>
                        <span>-${{ number_format($purchaseOrder->discount_amount, 2) }}</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between fw-bold">
                        <span>Total:</span>
                        <span>${{ number_format($purchaseOrder->total, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection