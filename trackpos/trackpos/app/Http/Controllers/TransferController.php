<?php

namespace App\Http\Controllers;

use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransferController extends Controller
{
    /**
     * List transfers
     */
    public function index(Request $request)
    {
        $query = StockTransfer::with('user');

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $transfers = $query->orderByDesc('created_at')->paginate(20);
        $products = Product::where('status', true)->orderBy('name')->get();

        return view('transfers.index', compact('transfers', 'products'));
    }

    /**
     * Create transfer
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'from_location' => 'required',
            'to_location' => 'required|different:from_location',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:1',
        ]);

        DB::transaction(function () use ($validated) {
            $transfer = StockTransfer::create([
                'transfer_no' => StockTransfer::generateTransferNumber(),
                'from_location' => $validated['from_location'],
                'to_location' => $validated['to_location'],
                'user_id' => auth()->id(),
                'status' => 'pending',
            ]);

            foreach ($validated['items'] as $item) {
                StockTransferItem::create([
                    'stock_transfer_id' => $transfer->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                ]);
            }
        });

        return redirect()->route('transfers.index')->with('success', 'Transfer created!');
    }

    /**
     * Update status
     */
    public function update(Request $request, StockTransfer $transfer)
    {
        $request->validate(['status' => 'required|in:pending,sent,received,cancelled']);

        $oldStatus = $transfer->status;
        $transfer->update(['status' => $request->status]);

        // Update stock
        if ($request->status === 'received' && $oldStatus !== 'received') {
            foreach ($transfer->items as $item) {
                // Deduct from source
                $product = $item->product;
                if ($product && $product->stock_quantity >= $item->quantity) {
                    $product->decrement('stock_quantity', $item->quantity);
                }
                
                // Add to destination (same inventory for now, different location tracked)
                $product->increment('stock_quantity', $item->quantity);
            }
            
            $transfer->update(['received_at' => now()]);
        }

        return back()->with('success', 'Transfer updated!');
    }

    /**
     * View transfer
     */
    public function show(StockTransfer $transfer)
    {
        $transfer->load(['user', 'items.product']);
        return view('transfers.show', compact('transfer'));
    }
}