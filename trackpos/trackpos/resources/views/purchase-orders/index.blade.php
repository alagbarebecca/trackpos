@extends('layouts.app')
@section('title', 'Purchase Orders')
@section('page-title', 'Purchase Orders')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Purchase Orders</h4>
        <button class="btn" style="background: #7c3aed; color: #fff; border: none;" data-bs-toggle="modal" data-bs-target="#addPOModal">
            <i class="fas fa-plus me-2"></i>New Order
        </button>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="ordered" {{ request('status') == 'ordered' ? 'selected' : '' }}>Ordered</option>
                        <option value="received" {{ request('status') == 'received' ? 'selected' : '' }}>Received</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Supplier</label>
                    <select name="supplier_id" class="form-select">
                        <option value="">All Suppliers</option>
                        @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                            {{ $supplier->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn" style="background: #7c3aed; color: #fff; border: none;">Filter</button>
                    <a href="{{ route('purchase-orders.index') }}" class="btn btn-secondary ms-2">Clear</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Purchase Orders Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-shopping-cart me-2" style="color: #7c3aed;"></i>Orders List</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>PO #</th>
                            <th>Supplier</th>
                            <th>Status</th>
                            <th>Total</th>
                            <th>Created By</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($purchaseOrders as $po)
                        <tr>
                            <td class="fw-semibold">{{ $po->purchase_no }}</td>
                            <td>{{ $po->supplier->name }}</td>
                            <td>
                                @if($po->status == 'pending')
                                <span class="badge bg-warning">Pending</span>
                                @elseif($po->status == 'ordered')
                                <span class="badge bg-info">Ordered</span>
                                @elseif($po->status == 'received')
                                <span class="badge bg-success">Received</span>
                                @else
                                <span class="badge bg-danger">Cancelled</span>
                                @endif
                            </td>
                            <td class="fw-bold">${{ number_format($po->total, 2) }}</td>
                            <td>{{ $po->user->name ?? '-' }}</td>
                            <td>{{ $po->created_at->format('M d, Y') }}</td>
                            <td>
                                <a href="{{ route('purchase-orders.show', $po) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if($po->status == 'pending')
                                <form action="{{ route('purchase-orders.update', $po) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="status" value="ordered">
                                    <button type="submit" class="btn btn-sm btn-outline-success" title="Mark as Ordered">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </form>
                                @endif
                                @if($po->status == 'ordered')
                                <form action="{{ route('purchase-orders.update', $po) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="status" value="received">
                                    <button type="submit" class="btn btn-sm btn-outline-success" title="Mark as Received">
                                        <i class="fas fa-check-double"></i>
                                    </button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="fas fa-shopping-cart fa-2x mb-2 d-block opacity-50"></i>
                                No purchase orders found
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($purchaseOrders->hasPages())
        <div class="card-footer">
            {{ $purchaseOrders->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Add Purchase Order Modal -->
<div class="modal fade" id="addPOModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('purchase-orders.store') }}" method="POST" id="poForm">
                @csrf
                <div class="modal-header" style="background: linear-gradient(135deg, #1e1b4b 0%, #0f0d1a 100%); color: #fff;">
                    <h5 class="modal-title">New Purchase Order</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Supplier *</label>
                        <select name="supplier_id" class="form-select" required>
                            <option value="">Select Supplier</option>
                            @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Products</label>
                        <div id="poItems">
                            <div class="row g-2 mb-2 po-item">
                                <div class="col-md-6">
                                    <select name="items[0][product_id]" class="form-select product-select">
                                        <option value="">Select Product</option>
                                        @foreach($products as $product)
                                        <option value="{{ $product->id }}" data-cost="{{ $product->cost_price ?? 0 }}">
                                            {{ $product->name }} ({{ $product->sku }})
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <input type="number" name="items[0][quantity]" class="form-control" placeholder="Qty" min="1" value="1">
                                </div>
                                <div class="col-md-3">
                                    <input type="number" name="items[0][unit_cost]" class="form-control" placeholder="Unit Cost" step="0.01" min="0">
                                </div>
                                <div class="col-md-1">
                                    <button type="button" class="btn btn-outline-danger remove-item">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-outline-primary" id="addItemBtn">
                            <i class="fas fa-plus me-1"></i> Add Product
                        </button>
                    </div>

                    <div class="bg-light p-3 rounded">
                        <div class="d-flex justify-content-between">
                            <span>Total:</span>
                            <span class="fw-bold" id="poTotal">$0.00</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn" style="background: #7c3aed; color: #fff; border: none;">Create Order</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let itemCount = 1;
const products = @json($products);

document.getElementById('addItemBtn').addEventListener('click', function() {
    const html = `
        <div class="row g-2 mb-2 po-item">
            <div class="col-md-6">
                <select name="items[${itemCount}][product_id]" class="form-select product-select">
                    <option value="">Select Product</option>
                    ${products.map(p => `<option value="${p.id}" data-cost="${p.cost_price || 0}">${p.name} (${p.sku})</option>`).join('')}
                </select>
            </div>
            <div class="col-md-2">
                <input type="number" name="items[${itemCount}][quantity]" class="form-control" placeholder="Qty" min="1" value="1">
            </div>
            <div class="col-md-3">
                <input type="number" name="items[${itemCount}][unit_cost]" class="form-control" placeholder="Unit Cost" step="0.01" min="0">
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-outline-danger remove-item">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    `;
    document.getElementById('poItems').insertAdjacentHTML('beforeend', html);
    itemCount++;
});

document.addEventListener('click', function(e) {
    if (e.target.classList.contains('remove-item') || e.target.closest('.remove-item')) {
        const btn = e.target.classList.contains('remove-item') ? e.target : e.target.closest('.remove-item');
        const items = document.querySelectorAll('.po-item');
        if (items.length > 1) {
            btn.closest('.po-item').remove();
            calculateTotal();
        }
    }
});

document.addEventListener('change', function(e) {
    if (e.target.name && e.target.name.includes('[product_id]')) {
        const cost = e.target.options[e.target.selectedIndex]?.dataset?.cost || 0;
        const row = e.target.closest('.po-item');
        row.querySelector('[name$="[unit_cost]"]').value = cost;
        calculateTotal();
    }
    if (e.target.name && (e.target.name.includes('[quantity]') || e.target.name.includes('[unit_cost]'))) {
        calculateTotal();
    }
});

function calculateTotal() {
    let total = 0;
    document.querySelectorAll('.po-item').forEach(item => {
        const qty = parseFloat(item.querySelector('[name$="[quantity]"]')?.value || 0);
        const cost = parseFloat(item.querySelector('[name$="[unit_cost]"]')?.value || 0);
        total += qty * cost;
    });
    document.getElementById('poTotal').textContent = '$' + total.toFixed(2);
}
</script>
@endsection