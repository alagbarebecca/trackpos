<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Tax Report - {{ $dateFrom }} to {{ $dateTo }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; padding: 20px; font-size: 12px; }
        
        .header { text-align: center; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #333; }
        .header h1 { font-size: 24px; margin-bottom: 5px; }
        .header .period { font-size: 14px; color: #666; }
        
        .summary-grid { display: flex; flex-wrap: wrap; gap: 15px; margin-bottom: 25px; }
        .summary-card { flex: 1; min-width: 150px; padding: 15px; border: 1px solid #ddd; border-radius: 5px; text-align: center; }
        .summary-card.highlight { background: #e8f5e9; border-color: #4caf50; }
        .summary-card .label { font-size: 11px; color: #666; text-transform: uppercase; }
        .summary-card .value { font-size: 20px; font-weight: bold; margin-top: 5px; }
        .summary-card.highlight .value { color: #2e7d32; }
        
        .section { margin-bottom: 25px; }
        .section-title { font-size: 16px; font-weight: bold; margin-bottom: 10px; padding-bottom: 5px; border-bottom: 1px solid #ddd; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th, td { padding: 8px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f5f5f5; font-weight: bold; font-size: 11px; text-transform: uppercase; }
        td.numeric { text-align: right; }
        th.numeric { text-align: right; }
        
        .totals-row { background: #f9f9f9; font-weight: bold; }
        
        .remittance-box { background: #e8f5e9; border: 2px solid #4caf50; border-radius: 8px; padding: 20px; margin-top: 20px; }
        .remittance-box h3 { color: #2e7d32; margin-bottom: 15px; }
        .remittance-row { display: flex; justify-content: space-between; padding: 8px 0; }
        .remittance-row.total { border-top: 2px solid #4caf50; font-size: 16px; font-weight: bold; margin-top: 10px; padding-top: 15px; }
        
        .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #999; }
        
        @media print {
            body { padding: 10px; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $appName ?? 'TrackPOS' }} - Tax Report</h1>
        <p class="period">Period: {{ \Carbon\Carbon::parse($dateFrom)->format('M d, Y') }} to {{ \Carbon\Carbon::parse($dateTo)->format('M d, Y') }}</p>
        <p>Generated: {{ now()->format('M d, Y H:i') }}</p>
    </div>
    
    <div class="summary-grid">
        <div class="summary-card highlight">
            <div class="label">Tax Collected</div>
            <div class="value">{{ $currencySymbol ?? '$' }}{{ number_format($totalTax, 2) }}</div>
        </div>
        <div class="summary-card">
            <div class="label">Total Sales</div>
            <div class="value">{{ $currencySymbol ?? '$' }}{{ number_format($totalRevenue, 2) }}</div>
        </div>
        <div class="summary-card">
            <div class="label">Subtotal</div>
            <div class="value">{{ $currencySymbol ?? '$' }}{{ number_format($totalSales, 2) }}</div>
        </div>
        <div class="summary-card">
            <div class="label">Discounts</div>
            <div class="value">{{ $currencySymbol ?? '$' }}{{ number_format($totalDiscount, 2) }}</div>
        </div>
    </div>
    
    <div class="section">
        <div class="section-title">Daily Summary</div>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th class="numeric">Sales</th>
                    <th class="numeric">Subtotal</th>
                    <th class="numeric">Tax</th>
                    <th class="numeric">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($dailyTax as $date => $data)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($date)->format('M d, Y') }}</td>
                    <td class="numeric">{{ $data['sales_count'] }}</td>
                    <td class="numeric">{{ $currencySymbol ?? '$' }}{{ number_format($data['subtotal'], 2) }}</td>
                    <td class="numeric">{{ $currencySymbol ?? '$' }}{{ number_format($data['tax'], 2) }}</td>
                    <td class="numeric">{{ $currencySymbol ?? '$' }}{{ number_format($data['total'], 2) }}</td>
                </tr>
                @endforeach
                <tr class="totals-row">
                    <td>Total</td>
                    <td class="numeric">{{ $sales->count() }}</td>
                    <td class="numeric">{{ $currencySymbol ?? '$' }}{{ number_format($totalSales, 2) }}</td>
                    <td class="numeric">{{ $currencySymbol ?? '$' }}{{ number_format($totalTax, 2) }}</td>
                    <td class="numeric">{{ $currencySymbol ?? '$' }}{{ number_format($totalRevenue, 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <div class="section">
        <div class="section-title">Sales Details</div>
        <table>
            <thead>
                <tr>
                    <th>Invoice</th>
                    <th>Date</th>
                    <th>Customer</th>
                    <th class="numeric">Subtotal</th>
                    <th class="numeric">Discount</th>
                    <th class="numeric">Tax</th>
                    <th class="numeric">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sales as $sale)
                <tr>
                    <td>{{ $sale->invoice_no }}</td>
                    <td>{{ $sale->created_at->format('M d, H:i') }}</td>
                    <td>{{ $sale->customer?->name ?? 'Walk-in' }}</td>
                    <td class="numeric">{{ $currencySymbol ?? '$' }}{{ number_format($sale->subtotal, 2) }}</td>
                    <td class="numeric">{{ $currencySymbol ?? '$' }}{{ number_format($sale->discount, 2) }}</td>
                    <td class="numeric">{{ $currencySymbol ?? '$' }}{{ number_format($sale->tax, 2) }}</td>
                    <td class="numeric">{{ $currencySymbol ?? '$' }}{{ number_format($sale->total, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    
    <div class="remittance-box">
        <h3>Tax Remittance Summary</h3>
        <div class="remittance-row">
            <span>Gross Sales</span>
            <span>{{ $currencySymbol ?? '$' }}{{ number_format($totalSales, 2) }}</span>
        </div>
        <div class="remittance-row">
            <span>Less: Discounts</span>
            <span>-{{ $currencySymbol ?? '$' }}{{ number_format($totalDiscount, 2) }}</span>
        </div>
        <div class="remittance-row">
            <span>Net Sales</span>
            <span>{{ $currencySymbol ?? '$' }}{{ number_format($totalSales - $totalDiscount, 2) }}</span>
        </div>
        <div class="remittance-row total">
            <span>Tax Collected (Remit This)</span>
            <span>{{ $currencySymbol ?? '$' }}{{ number_format($totalTax, 2) }}</span>
        </div>
    </div>
    
    <div class="footer">
        <p>{{ $appName ?? 'TrackPOS' }} - Tax Report | Generated on {{ now()->format('F j, Y g:i A') }}</p>
    </div>
</body>
</html>