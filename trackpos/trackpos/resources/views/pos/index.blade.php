@extends('layouts.app')
@section('title', 'POS')
@section('page-title', 'Point of Sale')

<style>
/* POS Page - Fullscreen without sidebar with POS topbar overlay */
body.pos-page .sidebar { display: none !important; }
body.pos-page .main-content { margin-left: 0 !important; margin-top: 0 !important; }
body.pos-page .topbar { display: none !important; }
body.pos-page .content-area { padding-top: 0 !important; padding-left: 0 !important; }
body.pos-page { padding-top: 0 !important; }
body.pos-page .container-fluid { max-width: 100% !important; padding: 0 15px !important; }
body.pos-page .row { margin: 0 !important; }
body.pos-page .card { border: none !important; border-radius: 0 !important; }
body.pos-page .card-header { border-radius: 0 !important; }

/* POS Custom Topbar - Overlay */
body.pos-page .pos-custom-topbar {
    display: flex !important;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    height: 60px;
    background: linear-gradient(135deg, #1e1b4b 0%, #0f0d1a 100%);
    z-index: 10000;
    padding: 0 20px;
    align-items: center;
    justify-content: space-between;
}

body.pos-page .pos-content {
    margin-top: 60px;
}

@media (max-width: 767px) {
    body.pos-page .sidebar { display: none !important; }
}

#networkStatus { font-size: 0.75rem; padding: 6px 12px; }
.pos-product { cursor: pointer; transition: all 0.2s; border-radius: 10px; overflow: hidden; }
.pos-product:hover { transform: translateY(-3px); box-shadow: 0 8px 20px rgba(124, 58, 237, 0.2); }
.pos-product .product-image { height: 100px; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #f3e8ff 0%, #e9d5ff 100%); border-radius: 8px; font-size: 30px; color: #7c3aed; }
.pos-product .card { border: 1px solid var(--border); border-radius: 10px; transition: all 0.2s; }
.pos-product:hover .card { border-color: var(--primary); }
.cart-item { display: flex; align-items: center; justify-content: space-between; padding: 14px; border-bottom: 1px solid var(--border); background: #fff; }
.cart-item:last-child { border-bottom: none; }
.quantity-control { display: flex; align-items: center; gap: 8px; }
.quantity-control button { width: 32px; height: 32px; padding: 0; display: flex; align-items: center; justify-content: center; border-radius: 6px; }
.held-sale-item { border: 1px solid var(--border); border-radius: 10px; padding: 14px; margin-bottom: 10px; cursor: pointer; transition: all 0.2s; background: #fff; }
.held-sale-item:hover { background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); border-color: var(--primary); }

/* Held Sale Card in Modal */
.held-sale-card { transition: all 0.2s; }
.held-sale-card:hover { transform: translateY(-3px); box-shadow: 0 8px 20px rgba(30, 27, 75, 0.15); border-color: var(--primary); }

/* POS Card Headers */
.pos-card-header { background: linear-gradient(135deg, #1e1b4b 0%, #0f0d1a 100%); color: #fff; padding: 14px 20px; border-radius: 12px 12px 0 0; border: none; }
.pos-card-header h5, .pos-card-header span { color: #fff; }
</style>

@section('content')
<script>
    var appName = '{{ $appName ?? "TrackPOS" }}';
    var currencySymbol = '{{ $currencySymbol ?? "$" }}';
    var discountSettings = {
        type: '{{ $settings["default_discount_type"] ?? "percentage" }}',
        value: {{ $settings["default_discount_value"] ?? 0 }},
        bulkThreshold: {{ $settings["bulk_discount_threshold"] ?? 10 }},
        bulkPercent: {{ $settings["bulk_discount_percent"] ?? 5 }}
    };
</script>

{{-- POS Custom Topbar --}}
<div class="pos-custom-topbar">
    <div class="d-flex align-items-center">
        <span style="color: #fff; font-weight: 700; font-size: 18px;">{{ $appName ?? 'TrackPOS' }}</span>
        <span style="color: rgba(255,255,255,0.7); margin-left: 15px; font-size: 14px;">Point of Sale</span>
    </div>
    <div class="d-flex align-items-center gap-3">
        <button type="button" class="btn btn-sm" style="background: rgba(255,255,255,0.1); color: #fff;" onclick="toggleHeldSales()" title="View Held Sales">
            <i class="fas fa-pause-circle"></i> Held Sales
        </button>
        <span id="networkStatus" class="badge" style="background: rgba(255,255,255,0.2); color: #fff;">
            <i class="fas fa-wifi me-1"></i> Online
        </span>
        <a href="{{ route('dashboard') }}" class="btn btn-sm" style="background: rgba(255,255,255,0.1); color: #fff;" title="Go to Dashboard">
            <i class="fas fa-home"></i>
        </a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-sm btn-danger" title="Logout">
                <i class="fas fa-sign-out-alt"></i>
            </button>
        </form>
    </div>
</div>

<div class="pos-content container-fluid">
    <div class="row g-4">
        <!-- Products Grid -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header pos-card-header">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span style="font-weight: 600;"><i class="fas fa-boxes me-2"></i>Products</span>
                        <span id="networkStatus" class="badge" style="background: rgba(255,255,255,0.2); color: #fff;"><i class="fas fa-wifi me-1"></i> Online</span>
                    </div>
                    <div class="row align-items-center g-2">
                        <div class="col-md-4">
                            <input type="text" class="form-control" id="searchInput" placeholder="Search products..." style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); color: #fff;">
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="categoryFilter" style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); color: #fff;">
                                <option value="">All Categories</option>
                                @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <input type="text" class="form-control" id="barcodeInput" placeholder="Scan barcode..." style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); color: #fff;">
                        </div>
                    </div>
                </div>
                <div class="card-body" style="background: #f8fafc;">
                    <div class="row g-3" id="productsGrid">
                        @forelse($products as $product)
                        <div class="col-md-3 col-6 pos-product" data-id="{{ $product->id }}" data-name="{{ $product->name }}" data-price="{{ $product->sell_price }}" data-stock="{{ $product->stock_quantity }}">
                            <div class="card h-100">
                                <div class="product-image"><i class="fas fa-box"></i></div>
                                <div class="card-body p-2">
                                    <h6 class="mb-1 text-truncate" style="font-size: 13px; color: #1e293b;">{{ $product->name }}</h6>
                                    <p class="mb-1 text-muted small">{{ $product->category?->name }}</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fw-bold" style="color: #7c3aed; font-size: 14px;">{{ $currencySymbol ?? '$' }}{{ number_format($product->sell_price, 2) }}</span>
                                        <span class="badge" style="background: {{ $product->stock_quantity > 10 ? '#dcfce7' : ($product->stock_quantity > 0 ? '#fef3c7' : '#fee2e2')}}; color: {{ $product->stock_quantity > 10 ? '#166534' : ($product->stock_quantity > 0 ? '#92400e' : '#991b1b')}}; font-size: 10px;">{{ $product->stock_quantity }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="col-12 text-center text-muted py-5">
                            <i class="fas fa-box-open fa-3x mb-3"></i>
                            <p>No products available</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Cart Sidebar -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header pos-card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-shopping-cart me-2"></i>Current Sale</h5>
                    <div>
                        <button class="btn btn-sm" style="background: rgba(255,255,255,0.2); color: #fff; border: none;" id="clearCartBtn" title="Clear Cart">
                            <i class="fas fa-trash"></i> Clear
                        </button>
                    </div>
                </div>
                <div class="card-body p-0" style="background: #f8fafc;">
                    <div id="cartItems" style="max-height: 250px; overflow-y: auto; background: #fff;">
                        <p class="text-center text-secondary py-5 mb-0">Cart is empty</p>
                    </div>
                </div>
                <div class="card-footer" style="background: #fff;">
                    <div class="mb-2">
                        <label class="form-label" style="font-size:13px; color: #64748b;">Customer (Optional)</label>
                        <select class="form-select form-select-sm" id="customerSelect">
                            <option value="">Walk-in Customer</option>
                            @foreach($customers as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span style="font-size:13px; color: #64748b;">Subtotal:</span>
                        <span id="cartSubtotal" style="font-weight: 600;">{{ $currencySymbol ?? '$' }}0.00</span>
                    </div>
                    <div class="mb-2">
                        <label class="form-label" style="font-size:13px; color: #64748b;">Discount ({{ $currencySymbol ?? '$' }})</label>
                        <input type="number" class="form-control form-control-sm" id="discountInput" value="0" min="0" step="0.01">
                    </div>
                    <div class="mb-2">
                        <label class="form-label" style="font-size:13px; color: #64748b;">Tax Rate (%)</label>
                        <input type="number" class="form-control form-control-sm" id="taxInput" value="{{ $defaultTaxRate ?? 0 }}" min="0" step="0.01" data-default="{{ $defaultTaxRate ?? 0 }}">
                        <small class="text-muted" style="font-size:10px">Default: {{ $defaultTaxRate ?? 0 }}%</small>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-3">
                        <h5 class="mb-0" style="font-size:16px; color: #1e293b;">Total:</h5>
                        <h5 class="mb-0" id="cartTotal" style="font-size:20px; font-weight: 700; color: #7c3aed;">{{ $currencySymbol ?? '$' }}0.00</h5>
                    </div>
                    
                    <!-- Single Payment Mode -->
                    <div id="singlePaymentSection">
                        <div class="mb-2">
                            <label class="form-label" style="font-size:13px; color: #64748b;">Payment Method</label>
                            <select class="form-select form-select-sm" id="paymentMethod">
                                <option value="cash">Cash</option>
                                <option value="card">Card</option>
                                <option value="transfer">Transfer</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" style="font-size:13px; color: #64748b;">Paid Amount</label>
                            <input type="number" class="form-control form-control-sm" id="paidAmount" value="0" min="0" step="0.01">
                        </div>
                    </div>
                    
                    <!-- Split Payment Toggle -->
                    <div class="mb-2 form-check">
                        <input type="checkbox" class="form-check-input" id="splitPaymentToggle">
                        <label class="form-check-label" style="font-size:13px; color: #64748b;" for="splitPaymentToggle">Split Payment</label>
                    </div>
                    
                    <!-- Split Payment Section (hidden by default) -->
                    <div id="splitPaymentSection" style="display:none;">
                        <div class="mb-2">
                            <div id="splitPayments">
                                <div class="row g-2 mb-2 split-payment-row">
                                    <div class="col-5">
                                        <select class="form-select form-select-sm payment-method-select">
                                            <option value="cash">Cash</option>
                                            <option value="card">Card</option>
                                            <option value="transfer">Transfer</option>
                                            <option value="gift_card">Gift Card</option>
                                        </select>
                                    </div>
                                    <div class="col-5">
                                        <input type="number" class="form-control form-control-sm payment-amount" placeholder="Amount" min="0" step="0.01">
                                    </div>
                                    <div class="col-2">
                                        <button type="button" class="btn btn-sm btn-outline-danger remove-split-payment"><i class="fas fa-times"></i></button>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="addSplitPayment"><i class="fas fa-plus"></i> Add</button>
                        </div>
                        <div class="mb-2 p-2" style="background:#f8fafc; border-radius:8px;">
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Balance:</span>
                                <span class="fw-bold" id="splitBalance">$0.00</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button class="btn flex-fill btn-lg" id="holdSaleBtn" style="background: #f59e0b; color: #fff; border: none;">
                            <i class="fas fa-pause me-2"></i>Hold
                        </button>
                        <button class="btn flex-fill btn-lg" id="completeSaleBtn" style="background: #059669; color: #fff; border: none;">
                            <i class="fas fa-check me-2"></i>Complete
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Held Sales Button (triggers modal) -->
            <div class="mt-3">
                <button class="btn w-100" style="background: linear-gradient(135deg, #1e1b4b 0%, #0f0d1a 100%); color: #fff; border: none;" data-bs-toggle="modal" data-bs-target="#heldSalesModal">
                    <i class="fas fa-pause me-2"></i>View Held Sales ({{ $heldSales->count() }})
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Held Sales Modal -->
<div class="modal fade" id="heldSalesModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header" style="background: linear-gradient(135deg, #1e1b4b 0%, #0f0d1a 100%); color: #fff; border: none;">
                <h5 class="modal-title"><i class="fas fa-pause me-2"></i>Held Sales</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="max-height:500px;overflow-y:auto; background: #f8fafc; padding: 20px;">
                @if($heldSales->count() > 0)
                    <div class="row g-3">
                    @foreach($heldSales as $held)
                        <div class="col-md-4 col-sm-6">
                            <div class="held-sale-card h-100" style="background: #fff; border: 2px solid #e2e8f0; border-radius: 12px; padding: 16px; cursor: pointer; transition: all 0.2s;" onclick="resumeHeldSale({{ $held->id }})">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div style="background: linear-gradient(135deg, #1e1b4b 0%, #0f0d1a 100%); color: #fff; width: 36px; height: 36px; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-pause"></i>
                                    </div>
                                    <small class="text-muted">{{ $held->created_at->format('H:i') }}</small>
                                </div>
                                <h6 class="mb-1" style="color: #1e293b; font-weight: 600;">{{ $held->hold_name ?? $held->reference_no }}</h6>
                                @if($held->customer)
                                <p class="mb-1 text-muted" style="font-size: 12px;">{{ $held->customer->name }}</p>
                                @endif
                                <p class="mb-2 text-muted" style="font-size: 12px;">{{ $held->item_count }} items</p>
                                <div class="d-flex justify-content-between align-items-end">
                                    <span style="color: #7c3aed; font-weight: 700; font-size: 18px;">{{ $currencySymbol ?? '$' }}{{ number_format($held->total,2) }}</span>
                                    <button class="btn btn-sm" style="background: #dc2626; color: #fff; border: none; width: 28px; height: 28px; padding: 0; border-radius: 6px;" onclick="event.stopPropagation(); deleteHeldSale({{ $held->id }})">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                    </div>
                @else
                <div class="text-center py-5">
                    <i class="fas fa-pause-circle" style="font-size: 48px; color: #cbd5e1; margin-bottom: 15px;"></i>
                    <p class="text-secondary mb-0">No held sales</p>
                    <small class="text-muted">Sales on hold will appear here</small>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Hold Sale Name Modal -->
<div class="modal fade" id="holdSaleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header" style="background: linear-gradient(135deg, #1e1b4b 0%, #0f0d1a 100%); color: #fff; border: none;">
                <h5 class="modal-title"><i class="fas fa-pause me-2"></i>Hold Sale</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Reference Name (optional)</label>
                    <input type="text" class="form-control" id="holdSaleName" placeholder="Enter a name for this held sale">
                </div>
                <p class="text-muted small">This will help you identify this sale when resuming.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn" id="confirmHoldBtn" style="background: #f59e0b; color: #fff; border: none;"><i class="fas fa-pause me-2"></i>Hold Sale</button>
            </div>
        </div>
    </div>
</div>

<!-- Receipt Modal -->
<div class="modal fade" id="receiptModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header" style="background: linear-gradient(135deg, #1e1b4b 0%, #0f0d1a 100%); color: #fff; border: none;">
                <h5 class="modal-title">Sale Complete</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="receiptContent"></div>
            <div class="modal-footer">
                <button type="button" class="btn" style="background: #7c3aed; color: #fff; border: none;" onclick="window.print()"><i class="fas fa-print me-2"></i>Print</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
// Toggle Held Sales Modal from topbar
function toggleHeldSales() {
    var modalEl = document.getElementById('heldSalesModal');
    var modal = new bootstrap.Modal(modalEl);
    modal.show();
}

// Resume held sale from card click
function resumeHeldSale(heldId) {
    fetch('pos/resume/' + heldId).then(function(res) {
        if (!res.ok) { throw new Error('Network error: ' + res.status); }
        return res.json();
    }).then(function(data) {
        if (data.success) {
            cart = data.cart_items.map(function(item) {
                return {
                    id: item.product_id,
                    name: item.product_name,
                    price: parseFloat(item.unit_price) || 0,
                    stock: parseInt(item.available_stock) || 0,
                    quantity: parseInt(item.quantity) || 1
                };
            });
            currentHeldSaleId = heldId;
            if (data.held_sale.customer_id) { document.getElementById('customerSelect').value = data.held_sale.customer_id; }
            if (data.held_sale.discount) { document.getElementById('discountInput').value = data.held_sale.discount; }
            if (data.held_sale.tax) { document.getElementById('taxInput').value = data.held_sale.tax; }
            updateCart();
            var modal = bootstrap.Modal.getInstance(document.getElementById('heldSalesModal'));
            if (modal) modal.hide();
            alert('Resumed: ' + data.held_sale.reference_no);
        } else { alert('Error: ' + (data.message || 'Unknown')); }
    }).catch(function(err) { console.error(err); alert('Failed to resume: ' + err.message); });
}

// Delete held sale from card
function deleteHeldSale(heldId) {
    if (!confirm('Delete this held sale?')) return;
    fetch('pos/held-sale/' + heldId, {method: 'DELETE', headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'}})
        .then(function(res) { if (!res.ok) { throw new Error('Network error: ' + res.status); } return res.json(); })
        .then(function(data) { if (data.success) location.reload(); })
        .catch(function(err) { console.error(err); alert('Failed to delete: ' + err.message); });
}

var cart = [];
var currentHeldSaleId = null; // Track which held sale is currently being edited
var currentSale = null;

function updateCart() {
    var cartItems = document.getElementById('cartItems');
    if (cart.length === 0) {
        cartItems.innerHTML = '<p class="text-center text-secondary py-5 mb-0">Cart is empty</p>';
    } else {
        cartItems.innerHTML = cart.map(function(item, index) {
            var itemSubtotal = item.price * item.quantity;
            var itemDiscount = item.discountPercent ? (itemSubtotal * item.discountPercent / 100) : 0;
            var discountBadge = item.discountPercent ? '<span class="badge bg-success ms-1" style="font-size:10px">-'+item.discountPercent+'%</span>' : '';
            return '<div class="cart-item"><div class="flex-grow-1"><h6 class="mb-0" style="font-size:14px">'+item.name+discountBadge+'</h6><small class="text-muted">'+currencySymbol+item.price.toFixed(2)+' x '+item.quantity+(itemDiscount ? ' (-'+currencySymbol+itemDiscount.toFixed(2)+')' : '')+'</small></div><div class="d-flex align-items-center gap-2"><div class="quantity-control"><button class="btn btn-sm btn-outline-secondary" onclick="updateQuantity('+index+',-1)">-</button><span style="min-width:20px;text-align:center">'+item.quantity+'</span><button class="btn btn-sm btn-outline-secondary" onclick="updateQuantity('+index+',1)">+</button></div><button class="btn btn-sm btn-outline-danger" onclick="removeFromCart('+index+')"><i class="fas fa-trash"></i></button></div></div>';
        }).join('');
    }
    calculateTotals();
}

function calculateTotals() {
    var subtotal = 0;
    var itemDiscounts = 0;
    cart.forEach(function(item) {
        var itemSubtotal = item.price * item.quantity;
        subtotal += itemSubtotal;
        if (item.discountPercent) {
            itemDiscounts += itemSubtotal * item.discountPercent / 100;
        }
    });
    var cartDiscount = parseFloat(document.getElementById('discountInput').value) || 0;
    var totalDiscount = itemDiscounts + cartDiscount;
    var taxRate = parseFloat(document.getElementById('taxInput').value) || 0;
    var tax = (subtotal - itemDiscounts) * (taxRate / 100); // Tax on discounted subtotal
    var total = subtotal - totalDiscount + tax;
    document.getElementById('cartSubtotal').textContent = currencySymbol + subtotal.toFixed(2);
    document.getElementById('cartTotal').textContent = currencySymbol + total.toFixed(2);
    return { subtotal: subtotal, discount: totalDiscount, tax: tax, total: total };
}

function updateQuantity(index, change) {
    var newQty = cart[index].quantity + change;
    if (newQty > 0 && newQty <= cart[index].stock) {
        cart[index].quantity = newQty;
        // Update bulk discount when quantity changes
        if (newQty >= discountSettings.bulkThreshold) {
            cart[index].discountPercent = discountSettings.bulkPercent;
        } else {
            cart[index].discountPercent = 0;
        }
        updateCart();
    }
}

function removeFromCart(index) {
    cart.splice(index, 1);
    updateCart();
}

function addToCart(product) {
    var existing = cart.find(function(item) { return item.id === product.id; });
    if (existing) {
        if (existing.quantity < product.stock) {
            existing.quantity++;
            // Check for bulk discount
            if (existing.quantity >= discountSettings.bulkThreshold) {
                existing.discountPercent = discountSettings.bulkPercent;
            } else {
                existing.discountPercent = 0;
            }
        }
    } else {
        // Check for bulk discount on first item
        var discountPercent = 0;
        if (1 >= discountSettings.bulkThreshold) {
            discountPercent = discountSettings.bulkPercent;
        }
        cart.push({
            id: product.id, 
            name: product.name, 
            price: product.sell_price, 
            stock: product.stock_quantity, 
            quantity: 1,
            discountPercent: discountPercent
        });
    }
    updateCart();
}

document.querySelectorAll('.pos-product').forEach(function(el) {
    el.addEventListener('click', function() {
        var addToProduct = {id: parseInt(el.dataset.id), name: el.dataset.name, sell_price: parseFloat(el.dataset.price), stock_quantity: parseInt(el.dataset.stock)};
        addToCart(addToProduct);
    });
});

document.getElementById('searchInput').addEventListener('input', function(e) {
    var query = e.target.value.toLowerCase();
    document.querySelectorAll('.pos-product').forEach(function(el) {
        var name = el.dataset.name.toLowerCase();
        el.style.display = name.includes(query) ? '' : 'none';
    });
});

document.getElementById('barcodeInput').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        var barcode = e.target.value;
        fetch('pos/barcode?barcode=' + barcode).then(function(res) { return res.json(); }).then(function(data) {
            if (data.id) { addToCart(data); e.target.value = ''; } else { alert('Product not found'); }
        }).catch(function() { alert('Product not found'); });
    }
});

document.getElementById('discountInput').addEventListener('input', calculateTotals);
document.getElementById('taxInput').addEventListener('input', calculateTotals);

document.getElementById('clearCartBtn').addEventListener('click', function() {
    if (confirm('Clear the cart?')) { cart = []; updateCart(); }
});

// HOLD SALE - Show modal first
document.getElementById('holdSaleBtn').addEventListener('click', function() {
    if (cart.length === 0) { alert('Cart is empty'); return; }
    // Show the hold sale modal
    new bootstrap.Modal(document.getElementById('holdSaleModal')).show();
});

// CONFIRM HOLD SALE
document.getElementById('confirmHoldBtn').addEventListener('click', function() {
    var holdName = document.getElementById('holdSaleName').value;
    var requestBody = {
        items: cart.map(function(item) { return {product_id: item.id, quantity: item.quantity}; }),
        customer_id: document.getElementById('customerSelect').value || null,
        discount: parseFloat(document.getElementById('discountInput').value) || 0,
        tax: parseFloat(document.getElementById('taxInput').value) || 0,
        hold_name: holdName
    };
    
    // If we have a current held sale ID, include it to update instead of creating new
    if (currentHeldSaleId) {
        requestBody.update_held_sale_id = currentHeldSaleId;
    }
    
    fetch('pos/hold', {
        method: 'POST',
        headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
        body: JSON.stringify(requestBody)
    }).then(function(res) { 
        if (!res.ok) { throw new Error('Network error: ' + res.status); }
        return res.json(); 
    }).then(function(data) {
        if (data.success) { 
            // Close modal
            var modal = bootstrap.Modal.getInstance(document.getElementById('holdSaleModal'));
            modal.hide();
            // Reset form
            document.getElementById('holdSaleName').value = '';
            alert(data.message); 
            cart = []; 
            currentHeldSaleId = null; // Reset after holding
            document.getElementById('discountInput').value = 0; 
            document.getElementById('taxInput').value = 0; 
            document.getElementById('customerSelect').value = ''; 
            updateCart(); 
            location.reload(); 
        } else { alert('Error: ' + (data.message || 'Unknown')); }
    }).catch(function(err) { console.error(err); alert('Failed to hold sale: ' + err.message); });
});

// RESUME HELD SALE
document.querySelectorAll('.resume-held').forEach(function(btn) {
    btn.addEventListener('click', function(e) {
        e.stopPropagation();
        var heldId = this.dataset.id;
        fetch('pos/resume/' + heldId).then(function(res) { 
            if (!res.ok) { throw new Error('Network error: ' + res.status); }
            return res.json(); 
        }).then(function(data) {
            if (data.success) {
                cart = data.cart_items.map(function(item) {
                    return {
                        id: item.product_id, 
                        name: item.product_name, 
                        price: parseFloat(item.unit_price) || 0, 
                        stock: parseInt(item.available_stock) || 0, 
                        quantity: parseInt(item.quantity) || 1
                    };
                });
                // Store the held sale ID so we can update it when holding again
                currentHeldSaleId = heldId;
                if (data.held_sale.customer_id) { document.getElementById('customerSelect').value = data.held_sale.customer_id; }
                // Restore discount and tax if any
                if (data.held_sale.discount) { document.getElementById('discountInput').value = data.held_sale.discount; }
                if (data.held_sale.tax) { document.getElementById('taxInput').value = data.held_sale.tax; }
                updateCart();
                // Close modal if open
                var modal = bootstrap.Modal.getInstance(document.getElementById('heldSalesModal'));
                if (modal) modal.hide();
                alert('Resumed: ' + data.held_sale.reference_no);
            } else { alert('Error: ' + (data.message || 'Unknown')); }
        }).catch(function(err) { console.error(err); alert('Failed to resume: ' + err.message); });
    });
});

// DELETE HELD SALE
document.querySelectorAll('.delete-held').forEach(function(btn) {
    btn.addEventListener('click', function(e) {
        e.stopPropagation();
        var heldId = this.dataset.id;
        if (!confirm('Delete this held sale?')) return;
        fetch('pos/held-sale/' + heldId, {method: 'DELETE', headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'}})
            .then(function(res) { 
                if (!res.ok) { throw new Error('Network error: ' + res.status); }
                return res.json(); 
            })
            .then(function(data) { if (data.success) location.reload(); })
            .catch(function(err) { console.error(err); alert('Failed to delete: ' + err.message); });
    });
});

// ==========================================
// SPLIT PAYMENT TOGGLE
// ==========================================
document.getElementById('splitPaymentToggle').addEventListener('change', function() {
    var splitSection = document.getElementById('splitPaymentSection');
    var singleSection = document.getElementById('singlePaymentSection');
    if (this.checked) {
        splitSection.style.display = 'block';
        singleSection.style.display = 'none';
    } else {
        splitSection.style.display = 'none';
        singleSection.style.display = 'block';
    }
});

// Add split payment row
document.getElementById('addSplitPayment').addEventListener('click', function() {
    var container = document.getElementById('splitPayments');
    var row = document.createElement('div');
    row.className = 'row g-2 mb-2 split-payment-row';
    row.innerHTML = '<div class="col-5"><select class="form-select form-select-sm payment-method-select"><option value="cash">Cash</option><option value="card">Card</option><option value="transfer">Transfer</option><option value="gift_card">Gift Card</option></select></div><div class="col-5"><input type="number" class="form-control form-control-sm payment-amount" placeholder="Amount" min="0" step="0.01"></div><div class="col-2"><button type="button" class="btn btn-sm btn-outline-danger remove-split-payment"><i class="fas fa-times"></i></button></div>';
    container.appendChild(row);
});

// Remove split payment row
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('remove-split-payment') || e.target.closest('.remove-split-payment')) {
        var rows = document.querySelectorAll('.split-payment-row');
        if (rows.length > 1) {
            var row = e.target.classList.contains('remove-split-payment') ? e.target : e.target.closest('.split-payment-row');
            row.remove();
        }
    }
});

// Update split balance on change
document.addEventListener('input', function(e) {
    if (e.target.classList.contains('payment-amount')) {
        updateSplitBalance();
    }
});

function updateSplitBalance() {
    var totals = calculateTotals();
    var paid = 0;
    document.querySelectorAll('.payment-amount').forEach(function(input) {
        paid += parseFloat(input.value) || 0;
    });
    var balance = totals.total - paid;
    var balanceEl = document.getElementById('splitBalance');
    balanceEl.textContent = currencySymbol + balance.toFixed(2);
    balanceEl.className = balance > 0 ? 'fw-bold text-danger' : 'fw-bold text-success';
}

// INITIALIZE SPLIT BALANCE
updateSplitBalance();


document.getElementById('completeSaleBtn').addEventListener('click', function() {
    if (cart.length === 0) { alert('Cart is empty'); return; }
    var totals = calculateTotals();
    
    var isSplit = document.getElementById('splitPaymentToggle').checked;
    var saleData;
    
    if (isSplit) {
        // Split payment mode
        var rows = document.querySelectorAll('.split-payment-row');
        var payments = [];
        var splitTotal = 0;
        
        rows.forEach(function(row) {
            var method = row.querySelector('.payment-method-select').value;
            var amount = parseFloat(row.querySelector('.payment-amount').value) || 0;
            if (amount > 0) {
                payments.push({method: method, amount: amount});
                splitTotal += amount;
            }
        });
        
        if (payments.length === 0) { 
            alert('Add at least one payment'); 
            return; 
        }
        if (splitTotal < totals.total) { 
            alert('Insufficient payment. Total: ' + currencySymbol + totals.total.toFixed(2) + ', Paid: ' + currencySymbol + splitTotal.toFixed(2)); 
            return; 
        }
        
        saleData = {
            items: cart.map(function(item) { return {product_id: item.id, quantity: item.quantity}; }),
            customer_id: document.getElementById('customerSelect').value || null,
            payments: payments,
            discount: parseFloat(document.getElementById('discountInput').value) || 0,
            tax_rate: parseFloat(document.getElementById('taxInput').value) || 0,
            tax: totals.tax,
            timestamp: new Date().toISOString(),
            held_sale_id: currentHeldSaleId || null
        };
    } else {
        // Single payment mode (backward compatibility)
        var paidAmount = parseFloat(document.getElementById('paidAmount').value) || 0;
        if (paidAmount < totals.total) { alert('Insufficient payment'); return; }
        
        saleData = {
            items: cart.map(function(item) { return {product_id: item.id, quantity: item.quantity}; }),
            customer_id: document.getElementById('customerSelect').value || null,
            payment_method: document.getElementById('paymentMethod').value,
            paid_amount: paidAmount,
            discount: parseFloat(document.getElementById('discountInput').value) || 0,
            tax_rate: parseFloat(document.getElementById('taxInput').value) || 0,
            tax: totals.tax,
            timestamp: new Date().toISOString(),
            held_sale_id: currentHeldSaleId || null
        };
    }
    
    // Check if online
    if (navigator.onLine) {
        fetch('pos/sale', {
            method: 'POST',
            headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
            body: JSON.stringify(saleData)
        }).then(function(res) { 
            if (!res.ok) { throw new Error('Network error: ' + res.status); }
            return res.json(); 
        }).then(function(data) {
            if (data.success) {
                localStorage.removeItem(CART_KEY);
                currentHeldSaleId = null;
                window.location.href = data.receipt_url;
            } else { alert('Error: ' + (data.message || 'Unknown')); }
        }).catch(function(err) { 
            console.error('Sale error:', err);
            alert('Error: ' + err.message);
            // Save offline as fallback (for production use)
            savePendingSale(saleData);
            cart = [];
            updateCart();
            alert('Sale saved offline. Will sync when online.');
        });
    } else {
        savePendingSale(saleData);
        cart = [];
        updateCart();
        alert('Offline! Sale saved locally.');
    }
});

// ==========================================
// OFFLINE SUPPORT
// ==========================================

// Check if service worker is supported
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/sw.js')
        .then(function(registration) {
            console.log('Service Worker registered');
        })
        .catch(function(error) {
            console.log('Service Worker registration failed:', error);
        });
}

// Network status
var isOnline = navigator.onLine;
updateOnlineStatus();

window.addEventListener('online', updateOnlineStatus);
window.addEventListener('offline', updateOnlineStatus);

function updateOnlineStatus() {
    isOnline = navigator.onLine;
    var statusEl = document.getElementById('networkStatus');
    if (statusEl) {
        if (isOnline) {
            statusEl.className = 'badge bg-success';
            statusEl.innerHTML = '<i class="fas fa-wifi me-1"></i> Online';
            syncPendingSales();
        } else {
            statusEl.className = 'badge bg-warning';
            statusEl.innerHTML = '<i class="fas fa-wifi-slash me-1"></i> Offline';
        }
    }
}

// Local storage keys
var PENDING_SALES_KEY = 'trackpos_pending_sales';
var CART_KEY = 'trackpos_cart';

// Save cart to localStorage
function saveCartToLocal() {
    localStorage.setItem(CART_KEY, JSON.stringify(cart));
}

// Load cart from localStorage
function loadCartFromLocal() {
    var saved = localStorage.getItem(CART_KEY);
    if (saved) {
        try {
            cart = JSON.parse(saved);
            updateCart();
        } catch (e) {
            console.error('Failed to load cart:', e);
        }
    }
}

// Save pending sale for sync
function savePendingSale(saleData) {
    var pending = JSON.parse(localStorage.getItem(PENDING_SALES_KEY) || '[]');
    pending.push({
        data: saleData,
        timestamp: new Date().toISOString()
    });
    localStorage.setItem(PENDING_SALES_KEY, JSON.stringify(pending));
}

// Sync pending sales when back online
function syncPendingSales() {
    var pending = JSON.parse(localStorage.getItem(PENDING_SALES_KEY) || '[]');
    if (pending.length === 0 || !navigator.onLine) return;
    
    var synced = 0;
    var failed = 0;
    
    pending.forEach(function(item) {
        fetch('pos/sync-sale', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(item.data)
        }).then(function(res) {
            if (res.ok) {
                synced++;
            } else {
                failed++;
            }
            if (synced + failed === pending.length) {
                if (synced > 0) {
                    // Remove synced items from localStorage
                    var remaining = pending.slice(synced + failed);
                    if (remaining.length > 0) {
                        localStorage.setItem(PENDING_SALES_KEY, JSON.stringify(remaining));
                    } else {
                        localStorage.removeItem(PENDING_SALES_KEY);
                    }
                    showNotification(synced + ' sale(s) synced successfully!');
                    // Reload page to show new sales
                    setTimeout(function() { location.reload(); }, 1500);
                }
                if (failed > 0) {
                    showNotification(failed + ' sale(s) failed to sync. Will retry later.');
                }
            }
        }).catch(function(err) {
            failed++;
            console.error('Sync failed:', err);
        });
    });
}

// Show notification
function showNotification(message) {
    var toast = '<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999;">' +
        '<div class="toast show" role="alert"><div class="toast-header bg-success text-white">' +
        '<strong class="me-auto">Sync</strong><button type="button" class="btn-close" data-bs-dismiss="toast"></button></div>' +
        '<div class="toast-body">' + message + '</div></div></div>';
    document.body.insertAdjacentHTML('beforeend', toast);
    setTimeout(function() {
        document.querySelector('.toast')?.remove();
    }, 3000);
}

// Load cart on init
loadCartFromLocal();

// Save cart on changes
var originalUpdateCart = updateCart;
updateCart = function() {
    originalUpdateCart();
    saveCartToLocal();
};

// ==========================================
// END OFFLINE SUPPORT
// ==========================================

function showReceipt(sale) {
    var html = '<div class="text-center mb-4"><h4>'+appName+'</h4><p class="mb-0">Invoice: '+sale.invoice_no+'</p><small class="text-muted">'+new Date(sale.created_at).toLocaleString()+'</small></div><table class="table table-sm"><thead><tr><th>Item</th><th>Qty</th><th>Price</th><th>Total</th></tr></thead><tbody>';
    sale.items.forEach(function(item) { html += '<tr><td>'+item.product.name+'</td><td>'+item.quantity+'</td><td>'+currencySymbol+item.unit_price.toFixed(2)+'</td><td>'+currencySymbol+item.subtotal.toFixed(2)+'</td></tr>'; });
    html += '</tbody></table><hr><div class="d-flex justify-content-between"><span>Subtotal:</span><span>'+currencySymbol+sale.subtotal.toFixed(2)+'</span></div><div class="d-flex justify-content-between"><span>Discount:</span><span>-'+currencySymbol+sale.discount.toFixed(2)+'</span></div><div class="d-flex justify-content-between"><span>Tax:</span><span>'+currencySymbol+sale.tax.toFixed(2)+'</span></div><div class="d-flex justify-content-between"><strong>Total:</strong><strong>'+currencySymbol+sale.total.toFixed(2)+'</strong></div><div class="d-flex justify-content-between"><span>Paid ('+sale.payment_method+'):</span><span>'+currencySymbol+sale.paid_amount.toFixed(2)+'</span></div><div class="d-flex justify-content-between"><span>Change:</span><span>'+currencySymbol+sale.change_amount.toFixed(2)+'</span></div><hr><div class="text-center text-muted"><small>Thank you for your purchase!</small></div>';
    document.getElementById('receiptContent').innerHTML = html;
    new bootstrap.Modal(document.getElementById('receiptModal')).show();
}
</script>
</div>
@endsection