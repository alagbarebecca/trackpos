@extends('layouts.app')
@section('title', 'New Return')
@section('page-title', 'Process Return')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Select Sale</h5>
                </div>
                <div class="card-body">
                    <select class="form-select" id="saleSelect" onchange="loadSaleItems()">
                        <option value="">Select a sale...</option>
                        @foreach($sales as $sale)
                        <option value="{{ $sale->id }}">
                            {{ $sale->invoice_no }} - {{ $sale->customer?->name ?? 'Walk-in' }} - ${{ number_format($sale->total, 2) }} - {{ $sale->created_at->format('M d, Y') }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>
            
            <div class="card" id="saleItemsCard" style="display: none;">
                <div class="card-header">
                    <h5 class="mb-0">Sale Items</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('returns.store') }}">
                        @csrf
                        <input type="hidden" name="sale_id" id="selectedSaleId">
                        
                        <div class="table-responsive">
                            <table class="table table-hover" id="itemsTable">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Price</th>
                                        <th>Sold</th>
                                        <th>Return Qty</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody id="itemsBody"></tbody>
                            </table>
                        </div>
                        
                        <div class="mt-4">
                            <div class="mb-3">
                                <label class="form-label">Reason for Return</label>
                                <textarea class="form-control" name="reason" rows="2" placeholder="Optional..."></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Refund Method</label>
                                <select class="form-select" name="refund_method" required>
                                    <option value="original">Original Payment Method</option>
                                    <option value="cash">Cash</option>
                                </select>
                            </div>
                            
                            <div class="alert alert-info">
                                <strong>Total Refund: </strong>$<span id="totalRefund">0.00</span>
                            </div>
                            
                            <button type="submit" class="btn btn-success btn-lg" id="processBtn" disabled>
                                <i class="fas fa-check me-2"></i>Process Return
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card" id="saleInfoCard" style="display: none;">
                <div class="card-header">
                    <h5 class="mb-0">Sale Info</h5>
                </div>
                <div class="card-body" id="saleInfo"></div>
            </div>
        </div>
    </div>
</div>

<script>
var salesData = @json($sales);

function loadSaleItems() {
    var saleId = document.getElementById('saleSelect').value;
    if (!saleId) {
        document.getElementById('saleItemsCard').style.display = 'none';
        document.getElementById('saleInfoCard').style.display = 'none';
        return;
    }
    
    var sale = salesData.find(s => s.id == saleId);
    document.getElementById('selectedSaleId').value = saleId;
    document.getElementById('saleItemsCard').style.display = 'block';
    document.getElementById('saleInfoCard').style.display = 'block';
    
    // Show sale info
    var infoHtml = '<dl class="row mb-0">' +
        '<dt class="col-sm-4">Invoice</dt><dd class="col-sm-8">' + sale.invoice_no + '</dd>' +
        '<dt class="col-sm-4">Customer</dt><dd class="col-sm-8">' + (sale.customer ? sale.customer.name : 'Walk-in') + '</dd>' +
        '<dt class="col-sm-4">Total</dt><dd class="col-sm-8">$' + sale.total.toFixed(2) + '</dd>' +
        '<dt class="col-sm-4">Payment</dt><dd class="col-sm-8">' + sale.payment_method + '</dd>' +
        '</dl>';
    document.getElementById('saleInfo').innerHTML = infoHtml;
    
    // Show items
    var itemsHtml = '';
    var totalRefund = 0;
    
    sale.items.forEach(function(item) {
        itemsHtml += '<tr>' +
            '<td>' + item.product.name + '</td>' +
            '<td>$' + item.unit_price.toFixed(2) + '</td>' +
            '<td>' + item.quantity + '</td>' +
            '<td>' +
                '<input type="hidden" name="items[' + item.id + '][sale_item_id]" value="' + item.id + '">' +
                '<input type="number" class="form-control" name="items[' + item.id + '][quantity]" ' +
                'min="0" max="' + item.quantity + '" value="0" ' +
                'onchange="updateSubtotal(this, ' + item.unit_price + ')">' +
            '</td>' +
            '<td class="item-subtotal">$0.00</td>' +
            '</tr>';
    });
    
    document.getElementById('itemsBody').innerHTML = itemsHtml;
}

function updateSubtotal(input, unitPrice) {
    var qty = parseInt(input.value) || 0;
    var row = input.closest('tr');
    var subtotal = qty * unitPrice;
    row.querySelector('.item-subtotal').textContent = '$' + subtotal.toFixed(2);
    
    // Calculate total
    var total = 0;
    document.querySelectorAll('#itemsBody tr').forEach(function(row) {
        var qtyInput = row.querySelector('input[type="number"]');
        var priceCell = row.querySelector('.item-subtotal');
        total += parseFloat(priceCell.textContent.replace('$', '')) || 0;
    });
    
    document.getElementById('totalRefund').textContent = total.toFixed(2);
    document.getElementById('processBtn').disabled = total === 0;
}
</script>
@endsection