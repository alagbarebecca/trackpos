<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\Request;

class PaymentReportController extends Controller
{
    use \App\Http\Controllers\ExportCsvTrait;
    
    public function index(Request $request)
    {
        $dateFrom = $request->date_from ?? now()->startOfMonth()->toDateString();
        $dateTo = $request->date_to ?? now()->endOfMonth()->toDateString();
        
        $sales = Sale::with(['customer'])
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->where('status', 'completed')
            ->orderBy('created_at')
            ->get();
        
        // Payment method breakdown
        $paymentBreakdown = $sales->groupBy('payment_method')->map(function($methodSales) {
            return [
                'count' => $methodSales->count(),
                'total' => $methodSales->sum('total'),
                'subtotal' => $methodSales->sum('subtotal'),
                'tax' => $methodSales->sum('tax'),
                'discount' => $methodSales->sum('discount'),
            ];
        });
        
        $totalRevenue = $sales->sum('total');
        
        return view('reports.payment-method', compact('sales', 'dateFrom', 'dateTo', 'paymentBreakdown', 'totalRevenue'));
    }
    
    public function export(Request $request)
    {
        $dateFrom = $request->date_from ?? now()->startOfMonth()->toDateString();
        $dateTo = $request->date_to ?? now()->endOfMonth()->toDateString();
        
        $sales = Sale::with(['customer'])
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->where('status', 'completed')
            ->orderBy('created_at')
            ->get();
        
        $headers = ['Invoice', 'Date', 'Customer', 'Payment Method', 'Subtotal', 'Discount', 'Tax', 'Total'];
        
        $rows = $sales->map(function($sale) {
            return [
                $sale->invoice_no,
                $sale->created_at->format('Y-m-d H:i'),
                $sale->customer?->name ?? 'Walk-in',
                $sale->payment_method,
                number_format($sale->subtotal, 2),
                number_format($sale->discount, 2),
                number_format($sale->tax, 2),
                number_format($sale->total, 2),
            ];
        })->toArray();
        
        $filename = 'payment-method-' . $dateFrom . '-to-' . $dateTo . '.csv';
        
        return $this->exportToCsv($filename, $headers, $rows);
    }
}