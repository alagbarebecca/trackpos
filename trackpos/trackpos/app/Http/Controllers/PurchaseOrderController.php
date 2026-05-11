<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    /**
     * Display purchase orders list
     */
    public function index(Request $request)
    {
        $query = PurchaseOrder::with(['supplier', 'user']);

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->supplier_id) {
            $query->where('supplier_id', $request->supplier_id);
        }

        $purchaseOrders = $query->orderByDesc('created_at')->paginate(20);
        $suppliers = Supplier::orderBy('name')->get();
        $products = Product::with('supplier')
            ->where('status', true)
            ->orderBy('name')
            ->get();

        return view('purchase-orders.index', compact('purchaseOrders', 'suppliers', 'products'));
    }

    /**
     * Create new purchase order
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.unit_cost' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($validated) {
            $purchaseOrder = PurchaseOrder::create([
                'purchase_no' => PurchaseOrder::generatePONumber(),
                'supplier_id' => $validated['supplier_id'],
                'user_id' => auth()->id(),
                'status' => 'pending',
            ]);

            $total = 0;
            foreach ($validated['items'] as $item) {
                $itemTotal = $item['quantity'] * $item['unit_cost'];
                $total += $itemTotal;

                PurchaseOrderItem::create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                    'total' => $itemTotal,
                    'received_quantity' => 0,
                ]);
            }

            $purchaseOrder->update([
                'subtotal' => $total,
                'total' => $total,
            ]);
        });

        return redirect()->route('purchase-orders.index')
            ->with('success', 'Purchase order created successfully!');
    }

    /**
     * Update purchase order status
     */
    public function update(Request $request, PurchaseOrder $purchaseOrder)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,ordered,received,cancelled',
        ]);

        $oldStatus = $purchaseOrder->status;
        $purchaseOrder->update(['status' => $validated['status']]);

        // If received, update stock
        if ($validated['status'] === 'received' && $oldStatus !== 'received') {
            foreach ($purchaseOrder->items as $item) {
                $product = $item->product;
                if ($product) {
                    $product->update([
                        'stock_quantity' => $product->stock_quantity + $item->quantity,
                    ]);
                }
            }

            $purchaseOrder->update(['received_at' => now()]);
        }

        // If ordered, set ordered_at
        if ($validated['status'] === 'ordered' && $oldStatus === 'pending') {
            $purchaseOrder->update(['ordered_at' => now()]);
        }

        return redirect()->route('purchase-orders.index')
            ->with('success', 'Purchase order updated!');
    }

    /**
     * Show purchase order details
     */
    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['supplier', 'user', 'items.product']);
        return view('purchase-orders.show', compact('purchaseOrder'));
    }

    /**
     * Delete purchase order
     */
    public function destroy(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status !== 'pending') {
            return back()->with('error', 'Cannot delete non-pending orders!');
        }

        $purchaseOrder->items()->delete();
        $purchaseOrder->delete();

        return redirect()->route('purchase-orders.index')
            ->with('success', 'Purchase order deleted!');
    }
}