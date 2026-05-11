<?php

namespace App\Http\Controllers;

use App\Models\ReturnSale;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReturnController extends Controller
{
    public function index(Request $request)
    {
        $returns = ReturnSale::with(['sale', 'product', 'user'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
            
        return view('returns.index', compact('returns'));
    }

    public function create()
    {
        $sales = Sale::where('status', 'completed')
            ->whereDate('created_at', '>=', now()->subDays(30))
            ->with(['customer', 'items.product'])
            ->orderBy('created_at', 'desc')
            ->get();
            
        return view('returns.create', compact('sales'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'sale_id' => 'required|exists:sales,id',
            'items' => 'required|array|min:1',
            'items.*.sale_item_id' => 'required|exists:sale_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'reason' => 'nullable|string|max:500',
            'refund_method' => 'required|in:original,cash',
        ]);

        $sale = Sale::with('items')->findOrFail($request->sale_id);
        
        DB::beginTransaction();
        try {
            $totalRefund = 0;
            
            foreach ($request->items as $itemData) {
                $saleItem = SaleItem::findOrFail($itemData['sale_item_id']);
                $quantity = min($itemData['quantity'], $saleItem->quantity);
                $refundAmount = $saleItem->unit_price * $quantity;
                
                ReturnSale::create([
                    'return_number' => ReturnSale::generateReturnNumber(),
                    'sale_id' => $sale->id,
                    'product_id' => $saleItem->product_id,
                    'quantity' => $quantity,
                    'unit_price' => $saleItem->unit_price,
                    'subtotal' => $refundAmount,
                    'reason' => $request->reason,
                    'refund_method' => $request->refund_method,
                    'refund_amount' => $refundAmount,
                    'user_id' => auth()->id(),
                ]);
                
                // Restore stock
                Product::where('id', $saleItem->product_id)->increment('stock_quantity', $quantity);
                
                $totalRefund += $refundAmount;
            }
            
            DB::commit();
            
            return redirect()->route('returns.index')->with('success', 'Return processed! Total refund: $' . number_format($totalRefund, 2));
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error processing return: ' . $e->getMessage());
        }
    }

    public function show(ReturnSale $return)
    {
        return view('returns.show', compact('return'));
    }
}