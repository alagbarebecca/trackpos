<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockAdjustment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StockAdjustmentController extends Controller
{
    public function index(Request $request)
    {
        $query = StockAdjustment::with(['product', 'user'])->orderBy('created_at', 'desc');
        
        if ($request->product_id) {
            $query->where('product_id', $request->product_id);
        }
        if ($request->type) {
            $query->where('type', $request->type);
        }
        if ($request->from_date) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->to_date) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }
        
        $adjustments = $query->paginate(20);
        
        return view('stock-adjustments.index', compact('adjustments'));
    }

    public function create(Request $request)
    {
        $products = Product::orderBy('name')->get();
        $selectedProduct = $request->product_id ? Product::find($request->product_id) : null;
        return view('stock-adjustments.create', compact('products', 'selectedProduct'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer',
            'type' => 'required|in:add,remove,transfer',
            'reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $product = Product::findOrFail($request->product_id);
        $previousQty = $product->stock_quantity;
        
        // Calculate new quantity based on type
        if ($request->type === 'add') {
            $newQty = $previousQty + $request->quantity;
        } elseif ($request->type === 'remove') {
            $newQty = $previousQty - $request->quantity;
        } else {
            // For transfer, just record without changing (or could be treated as add)
            $newQty = $previousQty;
        }
        
        // Validate deduction doesn't go below zero
        if ($newQty < 0) {
            return back()->with('error', 'Cannot remove more than current stock. Current: ' . $previousQty);
        }
        
        // Update product stock
        $product->update(['stock_quantity' => $newQty]);
        
        // Record adjustment
        StockAdjustment::create([
            'product_id' => $product->id,
            'user_id' => auth()->id(),
            'quantity' => $request->quantity,
            'previous_quantity' => $previousQty,
            'new_quantity' => $newQty,
            'type' => $request->type,
            'reason' => $request->reason,
        ]);
        
        $action = $request->type === 'add' ? 'added' : 'removed';
        
        // Handle redirect
        if ($request->redirect === 'products') {
            return redirect()->route('products.index')
                ->with('success', "Stock {$action}: {$product->name} ({$previousQty} → {$newQty})");
        }
        
        return redirect()->route('stock-adjustments.index')
            ->with('success', "Stock {$action}: {$product->name} ({$previousQty} → {$newQty})");
    }

    public function show(StockAdjustment $adjustment)
    {
        return view('stock-adjustments.show', compact('adjustment'));
    }
}