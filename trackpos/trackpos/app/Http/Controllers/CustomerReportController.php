<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerReportController extends Controller
{
    use \App\Http\Controllers\ExportCsvTrait;
    
    public function index(Request $request)
    {
        $customerId = $request->customer_id;
        $dateFrom = $request->date_from ?? now()->startOfMonth()->toDateString();
        $dateTo = $request->date_to ?? now()->endOfMonth()->toDateString();
        
        $customers = Customer::orderBy('name')->get();
        
        $salesQuery = Sale::with(['customer', 'user'])
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->where('status', 'completed');
        
        if ($customerId) {
            $salesQuery->where('customer_id', $customerId);
        }
        
        $sales = $salesQuery->orderBy('created_at', 'desc')->get();
        
        // Customer summary
        $customerSummary = $sales->groupBy('customer_id')->map(function($custSales) {
            return [
                'name' => $custSales->first()->customer?->name ?? 'Walk-in',
                'sales_count' => $custSales->count(),
                'total_spent' => $custSales->sum('total'),
                'last_purchase' => $custSales->max('created_at'),
            ];
        })->sortByDesc('total_spent')->take(10);
        
        return view('reports.customer-sales', compact('sales', 'customers', 'customerId', 'dateFrom', 'dateTo', 'customerSummary'));
    }
    
    public function export(Request $request)
    {
        $customerId = $request->customer_id;
        $dateFrom = $request->date_from ?? now()->startOfMonth()->toDateString();
        $dateTo = $request->date_to ?? now()->endOfMonth()->toDateString();
        
        $salesQuery = Sale::with(['customer'])
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->where('status', 'completed');
        
        if ($customerId) {
            $salesQuery->where('customer_id', $customerId);
        }
        
        $sales = $salesQuery->orderBy('created_at', 'desc')->get();
        
        $headers = ['Invoice', 'Date', 'Customer', 'Subtotal', 'Discount', 'Tax', 'Total', 'Payment Method'];
        
        $rows = $sales->map(function($sale) {
            return [
                $sale->invoice_no,
                $sale->created_at->format('Y-m-d H:i'),
                $sale->customer?->name ?? 'Walk-in',
                number_format($sale->subtotal, 2),
                number_format($sale->discount, 2),
                number_format($sale->tax, 2),
                number_format($sale->total, 2),
                $sale->payment_method
            ];
        })->toArray();
        
        $filename = 'customer-sales-' . $dateFrom . '-to-' . $dateTo . '.csv';
        
        return $this->exportToCsv($filename, $headers, $rows);
    }
}