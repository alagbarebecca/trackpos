<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use Illuminate\Http\Request;

class ApiProductController extends Controller
{
    /**
     * List products (paginated)
     * GET /api/products
     */
    public function index(Request $request)
    {
        $query = Product::with(['category', 'brand']);
        
        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }
        
        if ($request->status !== null) {
            $query->where('status', $request->status);
        }
        
        $products = $query->orderBy('name')->paginate($request->per_page ?? 20);
        
        return response()->json($products);
    }

    /**
     * Get single product
     * GET /api/products/{id}
     */
    public function show(Product $product)
    {
        $product->load(['category', 'brand']);
        return response()->json($product);
    }

    /**
     * Create product
     * POST /api/products
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:50|unique:products',
            'category_id' => 'nullable|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'cost_price' => 'nullable|numeric|min:0',
            'sell_price' => 'nullable|numeric|min:0',
            'stock_quantity' => 'nullable|numeric|min:0',
            'alert_quantity' => 'nullable|numeric|min:0',
            'unit' => 'nullable|string|max:20',
            'status' => 'nullable|boolean',
        ]);

        $product = Product::create($validated);
        
        return response()->json([
            'success' => true,
            'product' => $product,
        ], 201);
    }

    /**
     * Update product
     * PUT /api/products/{id}
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'string|max:255',
            'sku' => 'string|max:50|unique:products,sku,' . $product->id,
            'category_id' => 'nullable|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'cost_price' => 'nullable|numeric|min:0',
            'sell_price' => 'nullable|numeric|min:0',
            'stock_quantity' => 'nullable|numeric|min:0',
            'alert_quantity' => 'nullable|numeric|min:0',
            'unit' => 'nullable|string|max:20',
            'status' => 'nullable|boolean',
        ]);

        $product->update($validated);
        
        return response()->json([
            'success' => true,
            'product' => $product->fresh(),
        ]);
    }

    /**
     * Delete product
     * DELETE /api/products/{id}
     */
    public function destroy(Product $product)
    {
        $product->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Product deleted',
        ]);
    }

    /**
     * Search products
     * GET /api/products/search?q=query
     */
    public function search(Request $request)
    {
        $query = $request->q;
        
        $products = Product::where('name', 'like', "%{$query}%")
            ->orWhere('sku', 'like', "%{$query}%")
            ->orWhere('barcode', 'like', "%{$query}%")
            ->orderBy('name')
            ->limit(20)
            ->get();
        
        return response()->json($products);
    }

    /**
     * Get low stock products
     * GET /api/products/low-stock
     */
    public function lowStock()
    {
        $products = Product::whereColumn('stock_quantity', '<=', 'alert_quantity')
            ->where('status', true)
            ->orderBy('stock_quantity')
            ->get();
        
        return response()->json($products);
    }

    /**
     * Update stock
     * POST /api/products/{id}/stock
     */
    public function updateStock(Request $request, Product $product)
    {
        $validated = $request->validate([
            'quantity' => 'required|numeric',
            'type' => 'required|in:add,subtract,set',
        ]);

        $type = $validated['type'];
        $qty = $validated['quantity'];

        if ($type === 'add') {
            $product->increment('stock_quantity', $qty);
        } elseif ($type === 'subtract') {
            $product->decrement('stock_quantity', $qty);
        } else {
            $product->update(['stock_quantity' => $qty]);
        }

        return response()->json([
            'success' => true,
            'product' => $product->fresh(),
        ]);
    }
}