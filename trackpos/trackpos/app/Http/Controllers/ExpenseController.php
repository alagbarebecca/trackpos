<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ExpenseController extends Controller
{
    /**
     * Display a listing of the expenses.
     */
    public function index(Request $request)
    {
        $query = Expense::with(['category', 'user']);
        
        // Filter by date range
        if ($request->start_date && $request->end_date) {
            $query->whereBetween('date', [
                Carbon::parse($request->start_date)->startOfDay(),
                Carbon::parse($request->end_date)->endOfDay()
            ]);
        } elseif ($request->start_date) {
            $query->where('date', '>=', Carbon::parse($request->start_date));
        } elseif ($request->end_date) {
            $query->where('date', '<=', Carbon::parse($request->end_date));
        }
        
        // Filter by category
        if ($request->category_id) {
            $query->where('expense_category_id', $request->category_id);
        }
        
        // Filter by user
        if ($request->user_id) {
            $query->where('user_id', $request->user_id);
        }
        
        $expenses = $query->orderByDesc('date')->orderByDesc('id')->paginate(20);
        $categories = ExpenseCategory::orderBy('name')->get();
        
        // Calculate totals
        $totalExpenses = $query->sum('amount');
        
        return view('expenses.index', compact('expenses', 'categories', 'totalExpenses'));
    }

    /**
     * Store a newly created expense.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'expense_category_id' => 'required|exists:expense_categories,id',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:500',
            'date' => 'required|date',
            'reference' => 'nullable|string|max:100',
        ]);

        $validated['user_id'] = auth()->id();
        
        Expense::create($validated);
        
        return redirect()->route('expenses.index')
            ->with('success', 'Expense recorded successfully!');
    }

    /**
     * Update the specified expense.
     */
    public function update(Request $request, Expense $expense)
    {
        $validated = $request->validate([
            'expense_category_id' => 'required|exists:expense_categories,id',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:500',
            'date' => 'required|date',
            'reference' => 'nullable|string|max:100',
        ]);

        $expense->update($validated);
        
        return redirect()->route('expenses.index')
            ->with('success', 'Expense updated successfully!');
    }

    /**
     * Remove the specified expense.
     */
    public function destroy(Expense $expense)
    {
        $expense->delete();
        
        return redirect()->route('expenses.index')
            ->with('success', 'Expense deleted successfully!');
    }

    /**
     * Display expense categories management.
     */
    public function categories()
    {
        $categories = ExpenseCategory::orderBy('name')->get();
        return view('expenses.categories', compact('categories'));
    }

    /**
     * Store a newly created category.
     */
    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:expense_categories,name',
            'description' => 'nullable|string|max:500',
        ]);

        ExpenseCategory::create($validated);
        
        return redirect()->route('expenses.categories')
            ->with('success', 'Category created successfully!');
    }

    /**
     * Update a category.
     */
    public function updateCategory(Request $request, ExpenseCategory $expenseCategory)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:expense_categories,name,' . $expenseCategory->id,
            'description' => 'nullable|string|max:500',
        ]);

        $expenseCategory->update($validated);
        
        return redirect()->route('expenses.categories')
            ->with('success', 'Category updated successfully!');
    }

    /**
     * Delete a category.
     */
    public function destroyCategory(ExpenseCategory $expenseCategory)
    {
        if ($expenseCategory->expenses()->count() > 0) {
            return redirect()->route('expenses.categories')
                ->with('error', 'Cannot delete category with associated expenses!');
        }
        
        $expenseCategory->delete();
        
        return redirect()->route('expenses.categories')
            ->with('success', 'Category deleted successfully!');
    }
}