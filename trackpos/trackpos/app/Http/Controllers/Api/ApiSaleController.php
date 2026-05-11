<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApiSaleController extends Controller
{
    /**
     * List sales
     * GET /api/sales
     */
    public function index(Request $request)
    {
        $query = Sale::with(['user', 'customer']);
        
        if ($request->status) {
            $query->where('status', $request->status);
        }
        
        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        $sales = $query->orderByDesc('created_at')->paginate($request->per_page ?? 20);
        
        return response()->json($sales);
    }

    /**
     * Get single sale
     * GET /api/sales/{id}
     */
    public function show(Sale $sale)
    {
        $sale->load(['user', 'customer', 'items.product']);
        return response()->json($sale);
    }

    /**
     * Create sale
     * POST /api/sales
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'customer_id' => 'nullable|exists:customers,id',
            'payment_method' => 'nullable|in:cash,card,transfer',
            'discount' => 'nullable|numeric|min:0',
        ]);

        $subtotal = 0;
        
        foreach ($validated['items'] as $item) {
            $product = \App\Models\Product::find($item['product_id']);
            $subtotal += $product->sell_price * $item['quantity'];
        }
        
        $discount = $validated['discount'] ?? 0;
        $total = $subtotal - $discount;
        
        $invoiceNo = 'INV-' . str_pad(Sale::count() + 1, 5, '0', STR_PAD_LEFT);
        
        $sale = Sale::create([
            'invoice_no' => $invoiceNo,
            'customer_id' => $validated['customer_id'] ?? null,
            'user_id' => Auth::id() ?? 1,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'tax' => 0,
            'total' => $total,
            'payment_method' => $validated['payment_method'] ?? 'cash',
            'paid_amount' => $total,
            'change_amount' => 0,
            'status' => 'completed',
        ]);

        foreach ($validated['items'] as $item) {
            $product = \App\Models\Product::find($item['product_id']);
            $itemSubtotal = $product->sell_price * $item['quantity'];
            
            SaleItem::create([
                'sale_id' => $sale->id,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $product->sell_price,
                'discount' => 0,
                'tax' => 0,
                'subtotal' => $itemSubtotal,
            ]);
            
            $product->decrement('stock_quantity', $item['quantity']);
        }

        $sale->load(['items.product']);
        
        return response()->json([
            'success' => true,
            'sale' => $sale,
        ], 201);
    }

    /**
     * Get daily sales summary
     * GET /api/sales/summary?date=2024-01-01
     */
    public function summary(Request $request)
    {
        $date = $request->date ?? now()->toDateString();
        
        $sales = Sale::whereDate('created_at', $date)
            ->where('status', 'completed')
            ->get();
        
        $totalSales = $sales->count();
        $totalRevenue = $sales->sum('total');
        $totalTax = $sales->sum('tax');
        $totalDiscount = $sales->sum('discount');
        
        return response()->json([
            'date' => $date,
            'total_sales' => $totalSales,
            'total_revenue' => $totalRevenue,
            'total_tax' => $totalTax,
            'total_discount' => $totalDiscount,
        ]);
    }

    /**
     * Get top selling products
     * GET /api/sales/top-products?date_from=...&date_to=...
     */
    public function topProducts(Request $request)
    {
        $query = SaleItem::query();
        
        if ($request->date_from) {
            $query->whereHas('sale', function($q) use ($request) {
                $q->whereDate('created_at', '>=', $request->date_from);
            });
        }
        
        if ($request->date_to) {
            $query->whereHas('sale', function($q) use ($request) {
                $q->whereDate('created_at', '<=', $request->date_to);
            });
        }
        
        $products = $query->selectRaw('product_id, SUM(quantity) as total_qty, SUM(subtotal) as total_sales')
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->limit(10)
            ->get();
        
        return response()->json($products);
    }
}