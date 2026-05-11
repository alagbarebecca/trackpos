<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Customer;
use App\Models\HeldSale;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PosController extends Controller
{
    public function index()
    {
        $products = Product::with(['category', 'brand'])
            ->where('status', true)
            ->where('stock_quantity', '>', 0)
            ->orderBy('name')
            ->get();
        
        $categories = \App\Models\Category::where('status', true)->get();
        $customers = Customer::all();
        $heldSales = HeldSale::with(['user', 'customer'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Get settings for discount configuration
        $settings = [];
        if (\Illuminate\Support\Facades\Schema::hasTable('settings')) {
            $settings = \App\Models\Setting::pluck('value', 'key')->toArray();
        }
        
        return view('pos.index', compact('products', 'categories', 'customers', 'heldSales', 'settings'));
    }

    public function searchProducts(Request $request)
    {
        $query = $request->get('q');
        
        $products = Product::with(['category', 'brand'])
            ->where('status', true)
            ->where('stock_quantity', '>', 0)
            ->where(function($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('sku', 'like', "%{$query}%")
                  ->orWhere('barcode', 'like', "%{$query}%");
            })
            ->limit(20)
            ->get();
        
        return response()->json($products);
    }

    public function findByBarcode(Request $request)
    {
        $barcode = $request->get('barcode');
        
        $product = Product::with(['category', 'brand'])
            ->where('barcode', $barcode)
            ->where('status', true)
            ->first();
        
        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }
        
        return response()->json($product);
    }

    /**
     * Hold (suspend) a sale - or update existing held sale
     */
    public function holdSale(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $items = $request->input('items');
        $subtotal = 0;
        $itemCount = 0;
        
        // Check and reserve stock for each item
        foreach ($items as $item) {
            $product = Product::find($item['product_id']);
            $availableStock = $product->available_stock;
            
            // Check if we can reserve this quantity
            $currentReserved = $product->reserved_stock ?? 0;
            $neededStock = $item['quantity'];
            $canReserve = $availableStock >= $neededStock;
            
            if (!$canReserve) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient available stock for: ' . ($product->name ?? 'ID: ' . $item['product_id']) . 
                               '. Available: ' . $availableStock . ', Requested: ' . $neededStock,
                ], 422);
            }
        }
        
        // Reserve stock for all items (use transaction)
        DB::transaction(function () use ($items) {
            foreach ($items as $item) {
                $product = Product::find($item['product_id']);
                $currentReserved = $product->reserved_stock ?? 0;
                $product->update([
                    'reserved_stock' => $currentReserved + $item['quantity']
                ]);
            }
        });
        
        // Prepare cart data with full product details
        $cartData = [];
        
        foreach ($items as $item) {
            $product = Product::find($item['product_id']);
            $itemSubtotal = $product->sell_price * $item['quantity'];
            
            $subtotal += $itemSubtotal;
            $itemCount += $item['quantity'];
            
            // Store full product data for resume
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
        $holdName = $request->input('hold_name');
        $updateHeldSaleId = $request->input('update_held_sale_id');
        
        // Check if we should update an existing held sale
        if ($updateHeldSaleId) {
            $heldSale = HeldSale::find($updateHeldSaleId);
            if ($heldSale) {
                // Update existing held sale
                $heldSale->update([
                    'cart_data' => json_encode($cartData),
                    'subtotal' => $subtotal,
                    'discount' => $discount,
                    'tax' => $tax,
                    'total' => $total,
                    'item_count' => $itemCount,
                    'customer_id' => $request->input('customer_id'),
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => "Sale updated: {$heldSale->reference_no}",
                    'held_sale' => $heldSale,
                ]);
            }
        }
        
        // Create new held sale (original behavior)
        // Generate unique reference number
        $date = now()->format('Ymd');
        $count = HeldSale::whereDate('created_at', today())->count() + 1;
        
        // Use custom name if provided, otherwise use auto-generated reference
        if ($holdName && trim($holdName) !== '') {
            $referenceNo = "HS-" . strtoupper(trim($holdName));
        } else {
            $referenceNo = "HS-{$date}-" . str_pad($count, 3, '0', STR_PAD_LEFT);
        }
        
        $heldSale = HeldSale::create([
            'reference_no' => $referenceNo,
            'hold_name' => $holdName ?: null,
            'user_id' => Auth::id(),
            'customer_id' => $request->input('customer_id'),
            'subtotal' => $subtotal,
            'discount' => $discount,
            'tax' => $tax,
            'total' => $total,
            'item_count' => $itemCount,
            'cart_data' => json_encode($cartData),
        ]);

        return response()->json([
            'success' => true,
            'message' => "Sale held with reference: {$referenceNo}",
            'held_sale' => $heldSale,
        ]);
    }

    /**
     * Resume a held sale
     */
    public function resumeSale(HeldSale $heldSale)
    {
        $cartItems = $heldSale->cart_items;
        
        // Check stock availability for each item
        foreach ($cartItems as &$item) {
            $product = Product::find($item['product_id']);
            $item['available_stock'] = $product ? $product->stock_quantity : 0;
            $item['product_status'] = $product ? $product->status : false;
        }
        
        return response()->json([
            'success' => true,
            'held_sale' => $heldSale->load(['user', 'customer']),
            'cart_items' => $cartItems,
        ]);
    }

    /**
     * Delete (cancel) a held sale
     */
    public function deleteHeldSale(HeldSale $heldSale)
    {
        // Release reserved stock before deleting
        $cartItems = $heldSale->cart_items;
        if ($cartItems) {
            foreach ($cartItems as $item) {
                $product = Product::find($item['product_id']);
                if ($product) {
                    $currentReserved = $product->reserved_stock ?? 0;
                    $newReserved = max(0, $currentReserved - $item['quantity']);
                    $product->update(['reserved_stock' => $newReserved]);
                }
            }
        }
        $heldSale->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Held sale deleted successfully',
        ]);
    }

    /**
     * Get all held sales (for AJAX refresh)
     */
    public function getHeldSales()
    {
        $heldSales = HeldSale::with(['user', 'customer'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        return response()->json($heldSales);
    }

    /**
     * Complete a sale (checkout) - supports single or split payments
     */
    public function storeSale(Request $request)
    {
        $items = $request->input('items');
        $payments = $request->input('payments', []);
        
        // Validate items
        $request->validate([
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);
        
        // Validate single payment OR split payments
        if (empty($payments)) {
            // Single payment mode (backward compatibility)
            $request->validate([
                'payment_method' => 'required|in:cash,card,transfer',
                'paid_amount' => 'required|numeric|min:0',
            ]);
            
            $payments = [[
                'method' => $request->input('payment_method'),
                'amount' => $request->input('paid_amount'),
            ]];
        } else {
            // Split payment mode
            $request->validate([
                'payments' => 'required|array|min:1',
                'payments.*.method' => 'required|in:cash,card,transfer,gift_card,other',
                'payments.*.amount' => 'required|numeric|min:0.01',
            ]);
        }
        
        // Validate stock availability (considering reserved stock from held sales)
        foreach ($items as $item) {
            $product = Product::find($item['product_id']);
            if ($product && $item['quantity'] > $product->available_stock) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient available stock for product: ' . ($product->name ?? 'ID: ' . $item['product_id']) .
                               '. Available: ' . $product->available_stock . ', Requested: ' . $item['quantity'],
                ], 422);
            }
        }
        
        $subtotal = 0;
        
        foreach ($items as $item) {
            $product = Product::find($item['product_id']);
            $itemSubtotal = $product->sell_price * $item['quantity'];
            $subtotal += $itemSubtotal;
        }
        
        $discount = $request->input('discount', 0);
        $tax = $request->input('tax', 0);
        $total = $subtotal - $discount + $tax;
        
        // Calculate total paid from all payments
        $totalPaid = array_sum(array_column($payments, 'amount'));
        
        if ($totalPaid < $total) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient payment. Total: $' . number_format($total, 2) . ', Paid: $' . number_format($totalPaid, 2),
            ], 422);
        }
        
        // Use database transaction to ensure atomic operations
        return DB::transaction(function () use ($request, $items, $payments, $discount, $tax, $subtotal, $total, $totalPaid) {
            
            $invoiceNo = 'INV-' . str_pad(Sale::count() + 1, 5, '0', STR_PAD_LEFT);
            
            // Use primary payment method for the sale record
            $primaryMethod = $payments[0]['method'];
            if (count($payments) > 1) {
                $primaryMethod = 'split';
            }
            
            $sale = Sale::create([
                'invoice_no' => $invoiceNo,
                'customer_id' => $request->input('customer_id'),
                'user_id' => Auth::id(),
                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax' => $tax,
                'total' => $total,
                'payment_method' => $primaryMethod,
                'paid_amount' => $totalPaid,
                'change_amount' => $totalPaid - $total,
                'status' => 'completed',
            ]);
            
            // Save individual payments
            foreach ($payments as $payment) {
                Payment::create([
                    'sale_id' => $sale->id,
                    'payment_method' => $payment['method'],
                    'amount' => $payment['amount'],
                    'reference' => $payment['reference'] ?? null,
                    'created_at' => now(),
                ]);
            }
            
            // Release reserved stock when completing a resumed held sale
            $heldSaleId = $request->input('held_sale_id');
            if ($heldSaleId) {
                $heldSale = HeldSale::find($heldSaleId);
                if ($heldSale) {
                    $cartItems = $heldSale->cart_items;
                    if ($cartItems) {
                        foreach ($cartItems as $item) {
                            $product = Product::find($item['product_id']);
                            if ($product) {
                                $currentReserved = $product->reserved_stock ?? 0;
                                $newReserved = max(0, $currentReserved - $item['quantity']);
                                $product->update(['reserved_stock' => $newReserved]);
                            }
                        }
                    }
                    $heldSale->delete();
                }
            }
            
            $saleItems = [];
            
            foreach ($items as $item) {
                $product = Product::find($item['product_id']);
                $itemSubtotal = $product->sell_price * $item['quantity'];
                
                $saleItem = SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $product->sell_price,
                    'discount' => 0,
                    'tax' => 0,
                    'subtotal' => $itemSubtotal,
                ]);
                
                // Update stock
                $product->decrement('stock_quantity', $item['quantity']);
                
                $saleItems[] = [
                    'product' => $product,
                    'item' => $saleItem,
                ];
            }
            
            // Load relationships for receipt
            $sale->load(['customer', 'user', 'items.product', 'payments']);
            
            // Generate receipt URL
            $receiptUrl = route('sales.print', $sale->id);
            
            return response()->json([
                'success' => true,
                'sale' => $sale,
                'receipt_url' => $receiptUrl,
            ]);
        });
    }
}
