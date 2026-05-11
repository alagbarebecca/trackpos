<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\LocationProduct;
use App\Models\Product;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    /**
     * List locations
     */
    public function index()
    {
        $locations = Location::orderByDesc('is_default')->orderBy('name')->get();
        return view('locations.index', compact('locations'));
    }

    /**
     * Create location
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:20',
            'type' => 'required|in:store,warehouse,depot',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email',
            'status' => 'nullable|boolean',
        ]);

        if ($request->is_default) {
            Location::where('is_default', true)->update(['is_default' => false]);
            $validated['is_default'] = true;
        }

        $location = Location::create($validated);

        return redirect()->route('locations.index')->with('success', 'Location created!');
    }

    /**
     * Show location inventory
     */
    public function show(Location $location)
    {
        $location->load(['products.product']);

        $products = $location->products()->with('product')->orderByDesc('stock_quantity')->paginate(20);

        return view('locations.show', compact('location', 'products'));
    }

    /**
     * Update location
     */
    public function update(Request $request, Location $location)
    {
        $validated = $request->validate([
            'name' => 'string|max:255',
            'code' => 'nullable|string|max:20',
            'type' => 'in:store,warehouse,depot',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email',
            'status' => 'nullable|boolean',
        ]);

        if ($request->is_default && !$location->is_default) {
            Location::where('is_default', true)->update(['is_default' => false]);
            $validated['is_default'] = true;
        }

        $location->update($validated);

        return redirect()->route('locations.index')->with('success', 'Location updated!');
    }

    /**
     * Delete location
     */
    public function destroy(Location $location)
    {
        if ($location->is_default) {
            return back()->with('error', 'Cannot delete default location!');
        }

        $location->delete();

        return redirect()->route('locations.index')->with('success', 'Location deleted!');
    }

    /**
     * Adjust stock at location
     */
    public function adjustStock(Request $request, Location $location)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer',
            'type' => 'required|in:add,subtract,set',
            'notes' => 'nullable|string',
        ]);

        $lp = LocationProduct::firstOrCreate(
            ['location_id' => $location->id, 'product_id' => $validated['product_id']],
            ['stock_quantity' => 0, 'reserved_quantity' => 0]
        );

        $qty = $validated['quantity'];
        
        if ($validated['type'] === 'add') {
            $lp->increment('stock_quantity', $qty);
        } elseif ($validated['type'] === 'subtract') {
            $lp->decrement('stock_quantity', $qty);
        } else {
            $lp->update(['stock_quantity' => $qty]);
        }

        return back()->with('success', 'Stock adjusted!');
    }
}