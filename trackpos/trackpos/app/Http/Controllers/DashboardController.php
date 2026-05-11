<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use App\Models\SaleItem;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Check if user is sales_rep
        $isSalesRep = $user->hasRole('sales_rep');
        
        if ($isSalesRep) {
            return $this->salesRepDashboard($user);
        }
        
        // Default dashboard for other roles
        return $this->defaultDashboard($user);
    }
    
    /**
     * Sales Rep Dashboard - shows their personal sales
     */
    private function salesRepDashboard($user)
    {
        // Today's sales by this sales rep
        $todaySales = Sale::whereDate('created_at', today())
            ->where('user_id', $user->id)
            ->where('status', 'completed')
            ->sum('total');
        
        $todayTransactions = Sale::whereDate('created_at', today())
            ->where('user_id', $user->id)
            ->where('status', 'completed')
            ->count();
        
        // This week's sales
        $weekSales = Sale::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->where('user_id', $user->id)
            ->where('status', 'completed')
            ->sum('total');
        
        // This month's sales
        $monthSales = Sale::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->where('user_id', $user->id)
            ->where('status', 'completed')
            ->sum('total');
        
        // Today's sales by product
        $todaySalesByProduct = SaleItem::whereHas('sale', function ($query) use ($user) {
            $query->whereDate('created_at', today())
                ->where('user_id', $user->id)
                ->where('status', 'completed');
        })
        ->with('product')
        ->selectRaw('product_id, SUM(quantity) as total_qty, SUM(subtotal) as total_subtotal')
        ->groupBy('product_id')
        ->orderByDesc('total_subtotal')
        ->take(10)
        ->get();
        
        // Recent sales by this rep
        $recentSales = Sale::with(['customer'])
            ->where('user_id', $user->id)
            ->where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();
        
        // Today's transaction details
        $todayTransactionsList = Sale::with(['customer'])
            ->whereDate('created_at', today())
            ->where('user_id', $user->id)
            ->where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('dashboard.sales_rep', compact(
            'todaySales',
            'todayTransactions',
            'weekSales',
            'monthSales',
            'todaySalesByProduct',
            'recentSales',
            'todayTransactionsList'
        ));
    }
    
    /**
     * Default Dashboard for Admin, Manager, Supervisor, Accountant
     */
    private function defaultDashboard($user)
    {
        $todaySales = Sale::whereDate('created_at', today())
            ->where('status', 'completed')
            ->sum('total');
        
        $todayTransactions = Sale::whereDate('created_at', today())
            ->where('status', 'completed')
            ->count();
        
        $lowStockProducts = Product::whereRaw('stock_quantity <= min_stock_level')
            ->where('status', true)
            ->count();
        
        $topProducts = Product::orderBy('stock_quantity', 'asc')
            ->take(5)
            ->get();
        
        $recentSales = Sale::with(['customer', 'user'])
            ->where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
        
        $totalProducts = Product::where('status', true)->count();
        $totalCustomers = \App\Models\Customer::count();
        $totalSuppliers = \App\Models\Supplier::count();
        
        return view('dashboard', compact(
            'todaySales',
            'todayTransactions',
            'lowStockProducts',
            'topProducts',
            'recentSales',
            'totalProducts',
            'totalCustomers',
            'totalSuppliers'
        ));
    }
    
    /**
     * Sales Rep End of Day Report - dedicated page
     */
    public function salesRepEod()
    {
        $user = Auth::user();
        
        // Check if sales rep
        if (!$user->hasRole('sales_rep')) {
            return redirect('/dashboard');
        }
        
        $todaySales = Sale::whereDate('created_at', today())
            ->where('user_id', $user->id)
            ->where('status', 'completed')
            ->sum('total');
        
        $todayTransactions = Sale::whereDate('created_at', today())
            ->where('user_id', $user->id)
            ->where('status', 'completed')
            ->count();
        
        $weekSales = Sale::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->where('user_id', $user->id)
            ->where('status', 'completed')
            ->sum('total');
        
        $monthSales = Sale::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->where('user_id', $user->id)
            ->where('status', 'completed')
            ->sum('total');
        
        $todayTransactionsList = Sale::with(['customer'])
            ->whereDate('created_at', today())
            ->where('user_id', $user->id)
            ->where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->get();
        
        $todaySalesByProduct = SaleItem::whereHas('sale', function ($query) use ($user) {
            $query->whereDate('created_at', today())
                ->where('user_id', $user->id)
                ->where('status', 'completed');
        })
        ->with('product')
        ->selectRaw('product_id, SUM(quantity) as total_qty, SUM(subtotal) as total_subtotal')
        ->groupBy('product_id')
        ->orderByDesc('total_subtotal')
        ->get();
        
        return view('dashboard.sales_rep_eod', compact(
            'todaySales',
            'todayTransactions',
            'weekSales',
            'monthSales',
            'todayTransactionsList',
            'todaySalesByProduct'
        ));
    }
    
    /**
     * Sales Rep EOD Print View
     */
    public function salesRepEodPrint()
    {
        return $this->salesRepEod();
    }
}
