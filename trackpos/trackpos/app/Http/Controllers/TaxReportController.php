<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class TaxReportController extends Controller
{
    use \App\Http\Controllers\ExportCsvTrait;
    
    public function index(Request $request)
    {
        $dateFrom = $request->date_from ?? now()->startOfMonth()->toDateString();
        $dateTo = $request->date_to ?? now()->endOfMonth()->toDateString();
        
        $sales = Sale::with(['customer', 'user'])
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->where('status', 'completed')
            ->orderBy('created_at')
            ->get();
        
        $totalSales = $sales->sum('subtotal');
        $totalDiscount = $sales->sum('discount');
        $totalTax = $sales->sum('tax');
        $totalRevenue = $sales->sum('total');
        
        // Daily breakdown
        $dailyTax = $sales->groupBy(function($sale) {
            return $sale->created_at->format('Y-m-d');
        })->map(function($daySales) {
            return [
                'sales_count' => $daySales->count(),
                'subtotal' => $daySales->sum('subtotal'),
                'discount' => $daySales->sum('discount'),
                'tax' => $daySales->sum('tax'),
                'total' => $daySales->sum('total'),
            ];
        });
        
        return view('reports.tax', compact('sales', 'dateFrom', 'dateTo', 'totalSales', 'totalDiscount', 'totalTax', 'totalRevenue', 'dailyTax'));
    }
    
    public function export(Request $request)
    {
        $dateFrom = $request->date_from ?? now()->startOfMonth()->toDateString();
        $dateTo = $request->date_to ?? now()->endOfMonth()->toDateString();
        
        $sales = Sale::with(['customer', 'user'])
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->where('status', 'completed')
            ->orderBy('created_at')
            ->get();
        
        $headers = ['Invoice', 'Date', 'Customer', 'Subtotal', 'Discount', 'Tax', 'Total', 'Payment Method'];
        
        $rows = $sales->map(function($sale) {
            return [
                $sale->invoice_no,
                $sale->created_at->format('Y-m-d H:i'),
                $sale->customer?->name ?? 'Walk-in',
                number_format($sale->subtotal, 2),
                number_format($sale->discount, 2),
                number_format($sale->tax, 2),
                number_format($sale->total, 2),
                $sale->payment_method
            ];
        })->toArray();
        
        $filename = 'tax-report-' . $dateFrom . '-to-' . $dateTo . '.csv';
        
        return $this->exportToCsv($filename, $headers, $rows);
    }
    
    public function print(Request $request)
    {
        $dateFrom = $request->date_from ?? now()->startOfMonth()->toDateString();
        $dateTo = $request->date_to ?? now()->endOfMonth()->toDateString();
        
        $sales = Sale::whereBetween('created_at', [$dateFrom, $dateTo])
            ->where('status', 'completed')
            ->orderBy('created_at')
            ->get();
        
        $totalSales = $sales->sum('subtotal');
        $totalDiscount = $sales->sum('discount');
        $totalTax = $sales->sum('tax');
        $totalRevenue = $sales->sum('total');
        
        $dailyTax = $sales->groupBy(function($sale) {
            return $sale->created_at->format('Y-m-d');
        })->map(function($daySales) {
            return [
                'sales_count' => $daySales->count(),
                'subtotal' => $daySales->sum('subtotal'),
                'discount' => $daySales->sum('discount'),
                'tax' => $daySales->sum('tax'),
                'total' => $daySales->sum('total'),
            ];
        });
        
        return view('reports.tax-print', compact('sales', 'dateFrom', 'dateTo', 'totalSales', 'totalDiscount', 'totalTax', 'totalRevenue', 'dailyTax'));
    }
}