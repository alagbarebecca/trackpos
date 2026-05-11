<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\ReturnSale;
use App\Models\User;
use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ZReportController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->date ? Carbon::parse($request->date) : Carbon::today();
        $userId = $request->user_id;
        
        $startOfDay = $date->copy()->startOfDay();
        $endOfDay = $date->copy()->endOfDay();
        
        // Get all users for filter dropdown
        $users = User::where('role', '!=', 'admin')->get();
        
        // Build sales query with optional user filter
        $salesQuery = Sale::whereBetween('created_at', [$startOfDay, $endOfDay])
            ->where('status', 'completed');
            
        if ($userId) {
            $salesQuery->where('user_id', $userId);
        }
        
        $sales = $salesQuery->get();
            
        $totalSales = $sales->count();
        $totalRevenue = $sales->sum('total');
        $totalDiscount = $sales->sum('discount');
        $totalTax = $sales->sum('tax');
        
        // Payment breakdown
        $payments = $sales->groupBy('payment_method')->map(function ($group) {
            return [
                'count' => $group->count(),
                'total' => $group->sum('total'),
                'collected' => $group->sum('paid_amount'),
                'change' => $group->sum('change_amount'),
            ];
        });
        
        // Returns
        $returnsQuery = ReturnSale::whereBetween('created_at', [$startOfDay, $endOfDay]);
        if ($userId) {
            $returnsQuery->where('user_id', $userId);
        }
        $returns = $returnsQuery->get();
        $totalReturns = $returns->sum('refund_amount');
        $returnCount = $returns->count();
        
        // Sales by category (grouped products by category)
        $saleItemsQuery = SaleItem::whereHas('sale', function ($query) use ($startOfDay, $endOfDay, $userId) {
            $query->whereBetween('created_at', [$startOfDay, $endOfDay])
                  ->where('status', 'completed');
            if ($userId) {
                $query->where('user_id', $userId);
            }
        })->with('product.category');
        
        $saleItems = $saleItemsQuery->get();
        
        // Group by category
        $salesByCategory = $saleItems->groupBy(function($item) {
            return $item->product->category?->name ?? 'Uncategorized';
        })->map(function($items, $categoryName) {
            return [
                'category' => $categoryName,
                'total_quantity' => $items->sum('quantity'),
                'total_sales' => $items->sum('subtotal'),
                'items' => $items->groupBy('product_id')->map(function($productItems) {
                    return [
                        'product_name' => $productItems->first()->product->name,
                        'quantity' => $productItems->sum('quantity'),
                        'total' => $productItems->sum('subtotal')
                    ];
                })->values()
            ];
        })->sortByDesc('total_sales')->values();
        
        // Top products
        $topProducts = $saleItemsQuery->selectRaw('product_id, SUM(quantity) as total_qty, SUM(subtotal) as total_sales')
            ->groupBy('product_id')
            ->orderByDesc('total_sales')
            ->limit(10)
            ->get();
            
        // Calculate profit/loss
        $totalCost = 0;
        $totalRevenue = 0;
        foreach ($saleItems as $item) {
            $cost = $item->product->cost_price ?? 0;
            $totalCost += $cost * $item->quantity;
            $totalRevenue += $item->subtotal;
        }
        $totalProfit = $totalRevenue - $totalCost;
        $totalLoss = $totalCost > $totalRevenue ? $totalCost - $totalRevenue : 0;
            
        // Cash reconciliation
        $expectedCash = $sales->where('payment_method', 'cash')->sum('paid_amount') 
            - $sales->where('payment_method', 'cash')->sum('change_amount')
            - $returns->where('refund_method', 'cash')->sum('refund_amount');
            
        return view('reports.z-report', compact(
            'date', 'sales', 'totalSales', 'totalRevenue', 
            'totalDiscount', 'totalTax', 'payments',
            'returns', 'totalReturns', 'returnCount',
            'topProducts', 'expectedCash', 'users', 'userId',
            'salesByCategory', 'totalProfit', 'totalLoss', 'totalCost'
        ));
    }
    
    public function print(Request $request)
    {
        $date = $request->date ? Carbon::parse($request->date) : Carbon::today();
        
        $startOfDay = $date->copy()->startOfDay();
        $endOfDay = $date->copy()->endOfDay();
        
        $sales = Sale::whereBetween('created_at', [$startOfDay, $endOfDay])
            ->where('status', 'completed')
            ->get();
            
        $totalSales = $sales->count();
        $totalRevenue = $sales->sum('total');
        
        $payments = $sales->groupBy('payment_method')->map(function ($group) {
            return [
                'count' => $group->count(),
                'total' => $group->sum('total'),
            ];
        });
        
        $returns = ReturnSale::whereBetween('created_at', [$startOfDay, $endOfDay])->get();
        $totalReturns = $returns->sum('refund_amount');
        
        return view('reports.z-report-print', compact('date', 'totalSales', 'totalRevenue', 'payments', 'returns', 'totalReturns'));
    }
}