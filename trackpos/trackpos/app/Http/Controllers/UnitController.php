<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    /**
     * Display a listing of the units.
     */
    public function index(Request $request)
    {
        $query = Unit::query();
        
        if ($request->search) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('short_name', 'like', '%' . $request->search . '%');
        }
        
        $units = $query->orderBy('name')->paginate(15);
        
        return view('units.index', compact('units'));
    }

    /**
     * Store a newly created unit.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:units,name',
            'short_name' => 'required|string|max:20',
        ]);

        Unit::create($validated);
        
        return redirect()->route('units.index')
            ->with('success', 'Unit created successfully!');
    }

    /**
     * Update the specified unit.
     */
    public function update(Request $request, Unit $unit)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:units,name,' . $unit->id,
            'short_name' => 'required|string|max:20',
        ]);

        $unit->update($validated);
        
        return redirect()->route('units.index')
            ->with('success', 'Unit updated successfully!');
    }

    /**
     * Remove the specified unit.
     */
    public function destroy(Unit $unit)
    {
        if ($unit->products()->count() > 0) {
            return redirect()->route('units.index')
                ->with('error', 'Cannot delete unit with associated products!');
        }
        
        $unit->delete();
        
        return redirect()->route('units.index')
            ->with('success', 'Unit deleted successfully!');
    }
}