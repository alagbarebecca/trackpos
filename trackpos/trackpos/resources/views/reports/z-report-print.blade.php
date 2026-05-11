<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Z-Report - {{ $date->format('M d, Y') }}</title>
    <style>
        body { font-family: 'Courier New', monospace; max-width: 800px; margin: 0 auto; padding: 20px; }
        h1 { text-align: center; margin-bottom: 5px; }
        .subtitle { text-align: center; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f5f5f5; }
        .total-row { font-weight: bold; font-size: 1.2em; }
        .section { margin-bottom: 30px; }
        .footer { text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; }
        @media print { body { padding: 0; } }
    </style>
</head>
<body onload="window.print()">
    <h1>Z-REPORT</h1>
    <div class="subtitle">
        <strong>TrackPOS</strong><br>
        {{ $date->format('l, F d, Y') }}<br>
        Printed: {{ now()->format('H:i:s') }}
    </div>

    <div class="section">
        <h3>SUMMARY</h3>
        <table>
            <tr>
                <td>Total Transactions</td>
                <td style="text-align:right">{{ $totalSales }}</td>
            </tr>
            <tr>
                <td>Gross Revenue</td>
                <td style="text-align:right">${{ number_format($totalRevenue, 2) }}</td>
            </tr>
            <tr>
                <td>Total Refunds</td>
                <td style="text-align:right">-${{ number_format($totalReturns, 2) }}</td>
            </tr>
            <tr class="total-row">
                <td>Net Revenue</td>
                <td style="text-align:right">${{ number_format($totalRevenue - $totalReturns, 2) }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h3>PAYMENT METHODS</h3>
        <table>
            <thead>
                <tr>
                    <th>Method</th>
                    <th style="text-align:right">Count</th>
                    <th style="text-align:right">Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $method => $data)
                <tr>
                    <td>{{ strtoupper($method) }}</td>
                    <td style="text-align:right">{{ $data['count'] }}</td>
                    <td style="text-align:right">${{ number_format($data['total'], 2) }}</td>
                </tr>
                @empty
                <tr><td colspan="3">No sales</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="section">
        <h3>REFUNDS</h3>
        <table>
            <tr>
                <td>Total Refunds</td>
                <td style="text-align:right">{{ $returns->count() }} items</td>
                <td style="text-align:right">${{ number_format($totalReturns, 2) }}</td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <p>--- END OF REPORT ---</p>
        <p>TrackPOS Inventory Management System</p>
    </div>
</body>
</html>