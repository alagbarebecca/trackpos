<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class CategoryController extends Controller
{
    public function index()
    {
        // Check if parent_id column exists
        if (Schema::hasColumn('categories', 'parent_id')) {
            // Get main categories (no parent)
            $categories = Category::whereNull('parent_id')
                ->with('subcategories')
                ->orderBy('order')
                ->get();
        } else {
            // Fallback for when column doesn't exist yet
            $categories = Category::orderBy('name')->get();
        }
        
        // Also get all categories for the dropdown
        $allCategories = Category::orderBy('name')->get();
        
        return view('categories.index', compact('categories', 'allCategories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'description' => 'nullable|string',
        ]);

        // Generate slug from name if not provided
        $slug = \Illuminate\Support\Str::slug($request->name);
        
        // Ensure slug is unique
        $baseSlug = $slug;
        $counter = 1;
        while (\App\Models\Category::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter++;
        }

        // Only include parent_id if column exists
        $data = $request->all();
        $data['slug'] = $slug;
        if (!Schema::hasColumn('categories', 'parent_id')) {
            unset($data['parent_id']);
        }

        Category::create($data);

        return redirect()->route('categories.index')->with('success', 'Category created successfully!');
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
            'description' => 'nullable|string',
        ]);

        $data = $request->all();
        
        // Generate slug from name if name changed
        if ($request->name !== $category->name) {
            $slug = \Illuminate\Support\Str::slug($request->name);
            
            // Ensure slug is unique (excluding current category)
            $baseSlug = $slug;
            $counter = 1;
            while (\App\Models\Category::where('slug', $slug)->where('id', '!=', $category->id)->exists()) {
                $slug = $baseSlug . '-' . $counter++;
            }
            $data['slug'] = $slug;
        }
        
        // Handle parent_id
        if (!Schema::hasColumn('categories', 'parent_id')) {
            unset($data['parent_id']);
        } else {
            // Prevent setting itself as parent
            if ($request->parent_id == $category->id) {
                return redirect()->route('categories.index')->with('error', 'Category cannot be its own parent!');
            }
        }

        $category->update($data);

        return redirect()->route('categories.index')->with('success', 'Category updated successfully!');
    }

    public function destroy(Category $category)
    {
        // Check if category has products
        if ($category->products()->count() > 0) {
            return redirect()->route('categories.index')->with('error', 'Cannot delete category with associated products!');
        }
        
        // Move subcategories to parent if column exists
        if (Schema::hasColumn('categories', 'parent_id') && $category->subcategories()->count() > 0) {
            $category->subcategories()->update(['parent_id' => $category->parent_id]);
        }

        $category->delete();

        return redirect()->route('categories.index')->with('success', 'Category deleted successfully!');
    }
}