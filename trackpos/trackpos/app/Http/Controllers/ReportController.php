<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Product;
use App\Models\SaleItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    use \App\Http\Controllers\ExportCsvTrait;
    
    public function index()
    {
        return view('reports.index');
    }

    public function dailySales(Request $request)
    {
        $date = $request->get('date', date('Y-m-d'));

        $sales = Sale::with(['customer', 'user'])
            ->whereDate('created_at', $date)
            ->where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->get();

        $total = $sales->sum('total');

        return view('reports.daily-sales', compact('sales', 'date', 'total'));
    }
    
    public function exportDailySales(Request $request)
    {
        $date = $request->get('date', date('Y-m-d'));

        $sales = Sale::with(['customer', 'user'])
            ->whereDate('created_at', $date)
            ->where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->get();

        $headers = ['Invoice', 'Customer', 'Total', 'Payment Method', 'Time'];
        
        $rows = $sales->map(function($sale) {
            return [
                $sale->invoice_no,
                $sale->customer?->name ?? 'Walk-in',
                number_format($sale->total, 2),
                $sale->payment_method,
                $sale->created_at->format('H:i')
            ];
        })->toArray();
        
        $filename = 'daily-sales-' . $date . '.csv';
        
        return $this->exportToCsv($filename, $headers, $rows);
    }
    
    public function printDailySales(Request $request)
    {
        $date = $request->get('date', date('Y-m-d'));

        $sales = Sale::with(['customer', 'user'])
            ->whereDate('created_at', $date)
            ->where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->get();

        $total = $sales->sum('total');

        return view('reports.daily-sales-print', compact('sales', 'date', 'total'));
    }

    public function productSales(Request $request)
    {
        $dateFrom = $request->get('date_from', date('Y-m-01'));
        $dateTo = $request->get('date_to', date('Y-m-t'));

        $products = SaleItem::select('product_id', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(subtotal) as total_sales'))
            ->with('product')
            ->whereHas('sale', function($q) use ($dateFrom, $dateTo) {
                $q->whereDate('created_at', '>=', $dateFrom)
                  ->whereDate('created_at', '<=', $dateTo)
                  ->where('status', 'completed');
            })
            ->groupBy('product_id')
            ->orderByDesc('total_sales')
            ->get();

        return view('reports.product-sales', compact('products', 'dateFrom', 'dateTo'));
    }

    public function stockReport()
    {
        $products = Product::with(['category', 'brand'])
            ->where('status', true)
            ->orderBy('stock_quantity')
            ->get();

        return view('reports.stock', compact('products'));
    }

    public function profitLoss(Request $request)
    {
        $dateFrom = $request->get('date_from', date('Y-m-01'));
        $dateTo = $request->get('date_to', date('Y-m-t'));

        $sales = Sale::whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo)
            ->where('status', 'completed')
            ->get();

        $totalSales = $sales->sum('total');
        $totalCost = $sales->flatMap(function($sale) {
            return $sale->items;
        })->sum(function($item) {
            return $item->product->cost_price * $item->quantity;
        });

        $profit = $totalSales - $totalCost;

        return view('reports.profit-loss', compact('dateFrom', 'dateTo', 'totalSales', 'totalCost', 'profit'));
    }
}
