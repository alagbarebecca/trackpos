<?php

namespace App\Http\Controllers;

use App\Models\WasteRecord;
use App\Models\Product;
use Illuminate\Http\Request;

class WasteController extends Controller
{
    /**
     * List waste records
     */
    public function index(Request $request)
    {
        $query = WasteRecord::with(['product', 'user']);
        
        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        if ($request->reason) {
            $query->where('reason', $request->reason);
        }
        
        $records = $query->orderByDesc('created_at')->paginate(20);
        
        $totalQuantity = $query->sum('quantity');
        $totalValue = $query->sum('value');
        
        return view('waste.index', compact('records', 'totalQuantity', 'totalValue'));
    }

    /**
     * Record waste
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'reason' => 'required|in:expired,damaged,spoiled,stolen,other',
            'notes' => 'nullable|string',
        ]);

        $product = Product::find($validated['product_id']);
        
        if ($product->stock_quantity < $validated['quantity']) {
            return back()->with('error', 'Insufficient stock!');
        }

        $value = $product->cost_price * $validated['quantity'];
        
        $record = WasteRecord::create([
            'product_id' => $validated['product_id'],
            'user_id' => auth()->id(),
            'quantity' => $validated['quantity'],
            'reason' => $validated['reason'],
            'notes' => $validated['notes'],
            'value' => $value,
        ]);

        $product->decrement('stock_quantity', $validated['quantity']);

        return redirect()->route('waste.index')->with('success', 'Waste recorded!');
    }

    /**
     * Delete waste record (restore stock)
     */
    public function destroy(WasteRecord $waste)
    {
        $product = $waste->product;
        $product->increment('stock_quantity', $waste->quantity);
        $waste->delete();

        return back()->with('success', 'Stock restored!');
    }
}