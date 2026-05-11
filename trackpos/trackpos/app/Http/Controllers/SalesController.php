<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\Request;

class SalesController extends Controller
{
    public function index(Request $request)
    {
        $query = Sale::with(['customer', 'user']);

        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $sales = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('sales.index', compact('sales'));
    }

    public function show(Sale $sale)
    {
        $sale->load(['customer', 'user', 'items.product']);
        return view('sales.show', compact('sale'));
    }

    public function printReceipt(Sale $sale)
    {
        $sale->load(['customer', 'user', 'items.product']);
        return view('sales.receipt', compact('sale'));
    }

    public function search(Request $request)
    {
        $query = $request->get('q');
        
        $sales = Sale::with(['customer', 'user'])
            ->where('invoice_no', 'like', "%{$query}%")
            ->orWhereHas('customer', function($q) use ($query) {
                $q->where('name', 'like', "%{$query}%");
            })
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        return response()->json($sales);
    }
}
