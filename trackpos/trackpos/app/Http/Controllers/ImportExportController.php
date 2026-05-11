<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ImportExportController extends Controller
{
    /**
     * Import/Export hub
     */
    public function index()
    {
        return view('import-export.index');
    }

    /**
     * Export products to CSV
     */
    public function exportProducts(Request $request)
    {
        $query = Product::with(['category', 'brand']);

        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->status !== '' && $request->status !== null) {
            $query->where('status', $request->status);
        }

        $products = $query->orderBy('name')->get();

        $filename = 'products_' . now()->format('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($products) {
            $handle = fopen('php://output', 'w');
            
            // Header row
            fputcsv($handle, [
                'id', 'name', 'sku', 'barcode', 'category', 'brand',
                'cost_price', 'sell_price', 'stock_quantity', 'alert_quantity',
                'unit', 'status', 'created_at'
            ]);

            foreach ($products as $p) {
                fputcsv($handle, [
                    $p->id,
                    $p->name,
                    $p->sku,
                    $p->barcode,
                    $p->category->name ?? '',
                    $p->brand->name ?? '',
                    $p->cost_price,
                    $p->sell_price,
                    $p->stock_quantity,
                    $p->alert_quantity,
                    $p->unit ?? 'pcs',
                    $p->status ? 'active' : 'inactive',
                    $p->created_at,
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Import products from CSV
     */
    public function importProducts(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,txt|max:10240',
            'mode' => 'required|in:create,update,both',
        ]);

        $file = $request->file('file');
        $handle = fopen($file->path(), 'r');
        
        $header = fgetcsv($handle);
        $rows = 0;
        $created = 0;
        $updated = 0;

        DB::transaction(function() use ($handle, $request, &$rows, &$created, &$updated) {
            while (($data = fgetcsv($handle)) !== false) {
                $row = array_combine($header, $data);
                if (!$row) continue;

                $rows++;

                $categoryId = null;
                if (!empty($row['category'])) {
                    $category = Category::firstOrCreate(
                        ['name' => $row['category']],
                        ['slug' => str_slug($row['category'])]
                    );
                    $categoryId = $category->id;
                }

                $brandId = null;
                if (!empty($row['brand'])) {
                    $brand = Brand::firstOrCreate(['name' => $row['brand']]);
                    $brandId = $brand->id;
                }

                $data = [
                    'name' => $row['name'],
                    'sku' => $row['sku'] ?? null,
                    'barcode' => $row['barcode'] ?? null,
                    'category_id' => $categoryId,
                    'brand_id' => $brandId,
                    'cost_price' => $row['cost_price'] ?? 0,
                    'sell_price' => $row['sell_price'] ?? 0,
                    'stock_quantity' => $row['stock_quantity'] ?? 0,
                    'alert_quantity' => $row['alert_quantity'] ?? 10,
                    'unit' => $row['unit'] ?? 'pcs',
                    'status' => ($row['status'] ?? 'active') === 'active',
                ];

                if (!empty($row['id'])) {
                    // Update mode
                    if (in_array($request->mode, ['update', 'both'])) {
                        Product::where('id', $row['id'])->update($data);
                        $updated++;
                    }
                } elseif (!empty($row['sku'])) {
                    // Find by SKU
                    $product = Product::where('sku', $row['sku'])->first();
                    
                    if ($product && in_array($request->mode, ['update', 'both'])) {
                        $product->update($data);
                        $updated++;
                    } elseif (!$product && in_array($request->mode, ['create', 'both'])) {
                        Product::create($data);
                        $created++;
                    }
                } else {
                    // Create new
                    if (in_array($request->mode, ['create', 'both'])) {
                        Product::create($data);
                        $created++;
                    }
                }
            }
        });

        fclose($handle);

        return back()->with('success', "Imported: $created new, $updated updated (Total rows: $rows)");
    }

    /**
     * Export sales to CSV
     */
    public function exportSales(Request $request)
    {
        $query = \App\Models\Sale::with(['user', 'customer']);

        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $sales = $query->orderByDesc('created_at')->get();

        $filename = 'sales_' . now()->format('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($sales) {
            $handle = fopen('php://output', 'w');
            
            fputcsv($handle, ['invoice_no', 'customer', 'user', 'subtotal', 'discount', 'tax', 'total', 'payment_method', 'status', 'created_at']);

            foreach ($sales as $s) {
                fputcsv($handle, [
                    $s->invoice_no,
                    $s->customer->name ?? '',
                    $s->user->name ?? '',
                    $s->subtotal,
                    $s->discount,
                    $s->tax,
                    $s->total,
                    $s->payment_method,
                    $s->status,
                    $s->created_at,
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export customers to CSV
     */
    public function exportCustomers()
    {
        $customers = \App\Models\Customer::orderBy('name')->get();

        $filename = 'customers_' . now()->format('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($customers) {
            $handle = fopen('php://output', 'w');
            
            fputcsv($handle, ['name', 'phone', 'email', 'address', 'notes', 'created_at']);

            foreach ($customers as $c) {
                fputcsv($handle, [
                    $c->name,
                    $c->phone,
                    $c->email,
                    $c->address,
                    $c->notes,
                    $c->created_at,
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Sample CSV template
     */
    public function sampleTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="products_template.csv"',
        ];

        $callback = function() {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['id', 'name', 'sku', 'barcode', 'category', 'brand', 'cost_price', 'sell_price', 'stock_quantity', 'alert_quantity', 'unit', 'status']);
            fputcsv($handle, ['', 'Sample Product', 'SKU001', '', 'General', '', '10.00', '20.00', '100', '10', 'pcs', 'active']);
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}