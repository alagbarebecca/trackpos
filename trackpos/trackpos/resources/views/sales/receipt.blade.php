<?php
// Simple helper to get settings
function getSetting($key, $default = null) {
    $settings = \App\Models\Setting::pluck('value', 'key')->toArray();
    return $settings[$key] ?? $default;
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Receipt - {{ $sale->invoice_no }}</title>
    <style>
        body { font-family: 'Courier New', monospace; margin: 0; padding: 20px; }
        
        /* Regular Receipt */
        .receipt { max-width: 300px; margin: 0 auto; }
        
        /* Thermal Printer Format (80mm) */
        @media print {
            @page { margin: 0; size: 80mm; }
            body { width: 80mm; margin: 0; padding: 5mm; }
            .no-print { display: none; }
        }
        
        .header { text-align: center; margin-bottom: 15px; }
        .header h2 { margin: 0; font-size: 18px; }
        .header p { margin: 2px 0; font-size: 12px; }
        
        .divider { border-bottom: 1px dashed #000; margin: 10px 0; }
        
        .items { width: 100%; border-collapse: collapse; }
        .items th, .items td { padding: 3px 0; text-align: left; }
        .items th { border-bottom: 1px solid #000; }
        .items .qty { width: 30px; }
        .items .price { text-align: right; }
        
        .totals { margin-top: 10px; }
        .totals .row { display: flex; justify-content: space-between; }
        .totals .total { font-weight: bold; font-size: 14px; }
        
        .footer { text-align: center; margin-top: 15px; font-size: 11px; }
        
        .actions { margin-top: 20px; text-align: center; }
        .actions button {
            padding: 10px 20px; margin: 5px; cursor: pointer;
        }
    </style>
</head>
<body>
<?php
$companyName = \App\Models\Setting::where('key', 'company_name')->first()?->value ?: 'TrackPOS';
$companyAddress = \App\Models\Setting::where('key', 'company_address')->first()?->value;
$companyPhone = \App\Models\Setting::where('key', 'company_phone')->first()?->value;
$companyEmail = \App\Models\Setting::where('key', 'company_email')->first()?->value;
$companyTaxId = \App\Models\Setting::where('key', 'company_tax_id')->first()?->value;
$currency = \App\Models\Setting::where('key', 'currency_symbol')->first()?->value ?: '$';
?>
    <div class="receipt">
        <div class="header">
            <h2>{{ $companyName }}</h2>
            @if($companyAddress)
            <p>{{ $companyAddress }}</p>
            @endif
            @if($companyPhone)
            <p>Tel: {{ $companyPhone }}</p>
            @endif
            @if($companyEmail)
            <p>{{ $companyEmail }}</p>
            @endif
            @if($companyTaxId)
            <p>Tax ID: {{ $companyTaxId }}</p>
            @endif
            <p>{{ $sale->invoice_no }}</p>
            <p>{{ $sale->created_at->format('Y-m-d H:i:s') }}</p>
            @if($sale->customer)
            <p>Customer: {{ $sale->customer->name }}</p>
            @endif
            <p>Cashier: {{ $sale->user->name }}</p>
        </div>
        
        <div class="divider"></div>
        
        <table class="items">
            <thead>
                <tr>
                    <th>Item</th>
                    <th class="qty">Qty</th>
                    <th class="price">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sale->items as $item)
                <tr>
                    <td>
                        {{ $item->product->name }}
                        <br>
                        <small>{{ $currency }}{{ number_format($item->unit_price, 2) }} x {{ $item->quantity }}</small>
                    </td>
                    <td class="qty">{{ $item->quantity }}</td>
                    <td class="price">{{ $currency }}{{ number_format($item->subtotal, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        
        <div class="divider"></div>
        
        <div class="totals">
            <div class="row">
                <span>Subtotal:</span>
                <span>{{ $currency }}{{ number_format($sale->subtotal, 2) }}</span>
            </div>
            @if($sale->discount > 0)
            <div class="row">
                <span>Discount:</span>
                <span>-{{ $currency }}{{ number_format($sale->discount, 2) }}</span>
            </div>
            @endif
            @if($sale->tax > 0)
            <div class="row">
                <span>Tax:</span>
                <span>{{ $currency }}{{ number_format($sale->tax, 2) }}</span>
            </div>
            @endif
            <div class="row total">
                <span>TOTAL:</span>
                <span>{{ $currency }}{{ number_format($sale->total, 2) }}</span>
            </div>
            <div class="divider"></div>
            <div class="row">
                <span>Paid ({{ $sale->payment_method }}):</span>
                <span>{{ $currency }}{{ number_format($sale->paid_amount, 2) }}</span>
            </div>
            <div class="row">
                <span>Change:</span>
                <span>{{ $currency }}{{ number_format($sale->change_amount, 2) }}</span>
            </div>
        </div>
        
        <div class="divider"></div>
        
        <div class="footer">
            <p>Thank you for your purchase!</p>
            <p>Please come again</p>
        </div>
    </div>
    
    <div class="actions no-print">
        <button onclick="window.print()">Print</button>
        <button onclick="window.location.href='/pos'">Close & Return to POS</button>
    </div>
</body>
</html>
