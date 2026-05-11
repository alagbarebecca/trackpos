<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Daily Sales Report - {{ $date }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; padding: 20px; font-size: 12px; }
        
        .header { text-align: center; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #333; }
        .header h1 { font-size: 20px; margin-bottom: 5px; }
        .header .date { font-size: 14px; color: #666; }
        
        .summary { margin-bottom: 20px; padding: 15px; background: #f9f9f9; border-radius: 5px; }
        .summary .total { font-size: 24px; font-weight: bold; color: #2e7d32; }
        
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 8px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f5f5f5; font-weight: bold; font-size: 11px; text-transform: uppercase; }
        td.numeric { text-align: right; }
        
        .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #999; }
        
        @media print {
            body { padding: 10px; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $appName ?? 'TrackPOS' }} - Daily Sales Report</h1>
        <p class="date">Date: {{ \Carbon\Carbon::parse($date)->format('F j, Y') }}</p>
        <p>Generated: {{ now()->format('F j, Y g:i A') }}</p>
    </div>
    
    <div class="summary">
        <div>Total Sales: <span class="total">{{ $currencySymbol ?? '$' }}{{ number_format($total, 2) }}</span></div>
        <div>Number of Transactions: {{ $sales->count() }}</div>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>Invoice</th>
                <th>Customer</th>
                <th class="numeric">Total</th>
                <th>Payment</th>
                <th>Time</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sales as $sale)
            <tr>
                <td>{{ $sale->invoice_no }}</td>
                <td>{{ $sale->customer?->name ?? 'Walk-in' }}</td>
                <td class="numeric">{{ $currencySymbol ?? '$' }}{{ number_format($sale->total, 2) }}</td>
                <td>{{ ucfirst($sale->payment_method) }}</td>
                <td>{{ $sale->created_at->format('H:i') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <div class="footer">
        <p>{{ $appName ?? 'TrackPOS' }} - Daily Sales Report | Generated on {{ now()->format('F j, Y g:i A') }}</p>
    </div>
</body>
</html>