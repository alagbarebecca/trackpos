<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\Payment;
use App\Models\HeldSale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class OfflineSyncController extends Controller
{
    /**
     * Sync offline sales when connection is restored
     * Handles both new sales and resuming held sales
     */
    public function syncSale(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.product_id' => 'required|integer',
            'items.*.quantity' => 'required|integer|min:1',
            'payment_method' => 'required|string|in:cash,card,mobile,split',
            'payments' => 'nullable|array',
            'payments.*.method' => 'required|string|in:cash,card,mobile,gift_card,other',
            'payments.*.amount' => 'required|numeric|min:0.01',
            'paid_amount' => 'required|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'tax' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'customer_id' => 'nullable|integer',
            'held_sale_id' => 'nullable|integer',
            'is_held_sale' => 'nullable|boolean',
        ]);
        
        DB::beginTransaction();
        
        try {
            // Validate stock availability first
            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);
                if (!$product) {
                    throw new \Exception('Product not found: ID ' . $item['product_id']);
                }
                if ($item['quantity'] > $product->stock_quantity) {
                    throw new \Exception('Insufficient stock for product: ' . $product->name . '. Available: ' . $product->stock_quantity . ', Requested: ' . $item['quantity']);
                }
            }
            
            // Calculate totals
            $subtotal = 0;
            $itemsData = [];
            
            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);
                $quantity = $item['quantity'];
                $itemSubtotal = $product->sell_price * $quantity;
                $subtotal += $itemSubtotal;
                
                $itemsData[] = [
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $product->sell_price,
                    'discount' => 0,
                    'tax' => $itemSubtotal * ($request->tax_rate ?? 0) / 100,
                    'subtotal' => $itemSubtotal,
                ];
            }
            
            $discount = $request->discount ?? 0;
            $taxRate = $request->tax_rate ?? 0;
            $tax = $request->tax ?? ($subtotal - $discount) * $taxRate / 100;
            $total = $subtotal - $discount + $tax;
            $paidAmount = $request->paid_amount;
            $changeAmount = max(0, $paidAmount - $total);
            
            // Handle payments
            $payments = $request->filled('payments') ? $request->payments : [
                ['method' => $request->payment_method, 'amount' => $paidAmount]
            ];
            
            // Generate invoice number
            $lastSale = Sale::orderBy('id', 'desc')->first();
            $invoiceNo = 'INV-' . str_pad(($lastSale ? $lastSale->id + 1 : 1), 5, '0', STR_PAD_LEFT);
            
            // Determine primary payment method
            $primaryMethod = count($payments) > 1 ? 'split' : $request->payment_method;
            
            // Create sale
            $createdAt = $request->timestamp ? \Carbon\Carbon::parse($request->timestamp) : now();
            
            $sale = Sale::create([
                'invoice_no' => $invoiceNo,
                'customer_id' => $request->customer_id,
                'user_id' => Auth::id() ?? $request->user_id ?? 1,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax' => $tax,
                'total' => $total,
                'payment_method' => $primaryMethod,
                'paid_amount' => $paidAmount,
                'change_amount' => $changeAmount,
                'status' => 'completed',
                'created_at' => $createdAt,
            ]);
            
            // Update sale created_at if timestamp provided
            if ($request->timestamp) {
                $sale->update(['created_at' => $createdAt]);
            }
            
            // Create payments
            foreach ($payments as $payment) {
                Payment::create([
                    'sale_id' => $sale->id,
                    'payment_method' => $payment['method'],
                    'amount' => $payment['amount'],
                    'reference' => $payment['reference'] ?? null,
                    'created_at' => $createdAt,
                ]);
            }
            
            // Create sale items AND decrement stock atomically
            foreach ($itemsData as $itemData) {
                $saleItem = SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $itemData['product_id'],
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'discount' => $itemData['discount'],
                    'tax' => $itemData['tax'],
                    'subtotal' => $itemData['subtotal'],
                ]);
                
                // Decrement stock - this is the key fix!
                Product::where('id', $itemData['product_id'])->decrement('stock_quantity', $itemData['quantity']);
            }
            
            // If this was resuming a held sale, delete the held sale and release reservation
            if ($request->filled('held_sale_id')) {
                $heldSale = HeldSale::find($request->held_sale_id);
                if ($heldSale) {
                    // Release any reserved stock
                    $cartItems = $heldSale->cart_items;
                    if ($cartItems) {
                        foreach ($cartItems as $heldItem) {
                            Product::where('id', $heldItem['product_id'])
                                ->decrement('reserved_stock', $heldItem['quantity']);
                        }
                    }
                    $heldSale->delete();
                }
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'sale_id' => $sale->id,
                'invoice_no' => $invoiceNo,
                'message' => 'Sale synced successfully'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Sync failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Sync held sales from offline
     */
    public function syncHeldSale(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.product_id' => 'required|integer',
            'items.*.quantity' => 'required|integer|min:1',
            'hold_name' => 'nullable|string',
            'customer_id' => 'nullable|integer',
        ]);
        
        try {
            $items = $request->input('items');
            $subtotal = 0;
            $cartData = [];
            
            // Reserve stock for held items
            foreach ($items as $item) {
                $product = Product::find($item['product_id']);
                if (!$product) {
                    throw new \Exception('Product not found');
                }
                if ($item['quantity'] > $product->stock_quantity) {
                    throw new \Exception('Insufficient stock for: ' . $product->name);
                }
                
                $itemSubtotal = $product->sell_price * $item['quantity'];
                $subtotal += $itemSubtotal;
                
                // Reserve stock
                $currentReserved = $product->reserved_stock ?? 0;
                $product->update(['reserved_stock' => $currentReserved + $item['quantity']]);
                
                $cartData[] = [
                    'product_id' => $item['product_id'],
                    'product_name' => $product->name,
                    'product_sku' => $product->sku,
                    'unit_price' => $product->sell_price,
                    'quantity' => $item['quantity'],
                    'discount' => 0,
                    'tax' => 0,
                    'subtotal' => $itemSubtotal,
                    'stock' => $product->stock_quantity,
                ];
            }
            
            $discount = $request->input('discount', 0);
            $tax = $request->input('tax', 0);
            $total = $subtotal - $discount + $tax;
            
            // Generate reference
            $date = now()->format('Ymd');
            $count = HeldSale::whereDate('created_at', today())->count() + 1;
            $holdName = $request->input('hold_name');
            $referenceNo = $holdName ? "HS-" . strtoupper($holdName) : "HS-{$date}-" . str_pad($count, 3, '0', STR_PAD_LEFT);
            
            // Create held sale
            $heldSale = HeldSale::create([
                'reference_no' => $referenceNo,
                'hold_name' => $holdName,
                'user_id' => Auth::id() ?? $request->user_id ?? 1,
                'customer_id' => $request->customer_id,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax' => $tax,
                'total' => $total,
                'item_count' => count($items),
                'cart_data' => json_encode($cartData),
            ]);
            
            return response()->json([
                'success' => true,
                'held_sale_id' => $heldSale->id,
                'reference_no' => $referenceNo,
                'message' => 'Sale held offline successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Hold failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get pending sync count
     */
    public function getPendingCount()
    {
        return response()->json(['pending' => 0]);
    }
    
    /**
     * Batch sync multiple offline sales
     */
    public function batchSync(Request $request)
    {
        $request->validate([
            'sales' => 'required|array',
        ]);
        
        $results = [];
        
        foreach ($request->sales as $saleData) {
            // Create a fake request for each sale
            $saleRequest = new Request($saleData);
            $saleRequest->setMethod('POST');
            
            // Call syncSale for each
            try {
                $response = $this->syncSale($saleRequest);
                $results[] = [
                    'local_id' => $saleData['local_id'] ?? null,
                    'success' => true,
                    'response' => json_decode($response->getContent(), true)
                ];
            } catch (\Exception $e) {
                $results[] = [
                    'local_id' => $saleData['local_id'] ?? null,
                    'success' => false,
                    'error' => $e->getMessage()
                ];
            }
        }
        
        return response()->json([
            'success' => true,
            'results' => $results,
            'message' => 'Batch sync completed'
        ]);
    }
}