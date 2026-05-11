<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Unit;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductImportController extends Controller
{
    public function showImportForm()
    {
        return view('products.import');
    }
    
    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
        ]);
        
        $file = $request->file('csv_file');
        $handle = fopen($file->getRealPath(), 'r');
        
        // Get headers
        $headers = fgetcsv($handle);
        if (!$headers) {
            return back()->with('error', 'CSV file is empty');
        }
        
        // Normalize headers (trim, lowercase)
        $headers = array_map(function($h) {
            return strtolower(trim($h));
        }, $headers);
        
        // Map columns
        $columnMap = [
            'name' => ['name', 'product_name', 'product'],
            'code' => ['code', 'sku', 'product_code', 'item_code'],
            'barcode' => ['barcode', 'bar_code'],
            'category' => ['category', 'category_name'],
            'unit' => ['unit', 'unit_name', 'uom'],
            'brand' => ['brand', 'brand_name'],
            'cost_price' => ['cost', 'cost_price', 'purchase_price', 'price'],
            'sell_price' => ['sell_price', 'selling_price', 'price', 'sales_price'],
            'stock' => ['stock', 'quantity', 'qty', 'stock_quantity'],
            'alert_quantity' => ['alert', 'alert_qty', 'alert_quantity', 'min_stock'],
        ];
        
        $columnIndexes = [];
        foreach ($columnMap as $field => $aliases) {
            $columnIndexes[$field] = null;
            foreach ($headers as $index => $header) {
                if (in_array($header, $aliases)) {
                    $columnIndexes[$field] = $index;
                    break;
                }
            }
        }
        
        // Check required fields
        if ($columnIndexes['name'] === null) {
            return back()->with('error', 'Required column "name" not found in CSV');
        }
        
        $rowNumber = 1;
        $successCount = 0;
        $errorRows = [];
        
        DB::beginTransaction();
        
        try {
            while (($row = fgetcsv($handle)) !== false) {
                $rowNumber++;
                
                // Skip empty rows
                if (empty(array_filter($row))) {
                    continue;
                }
                
                try {
                    // Get field values
                    $name = isset($columnIndexes['name']) && isset($row[$columnIndexes['name']]) ? trim($row[$columnIndexes['name']]) : '';
                    $code = isset($columnIndexes['code']) && isset($row[$columnIndexes['code']]) ? trim($row[$columnIndexes['code']]) : null;
                    $barcode = isset($columnIndexes['barcode']) && isset($row[$columnIndexes['barcode']]) ? trim($row[$columnIndexes['barcode']]) : null;
                    $categoryName = isset($columnIndexes['category']) && isset($row[$columnIndexes['category']]) ? trim($row[$columnIndexes['category']]) : null;
                    $unitName = isset($columnIndexes['unit']) && isset($row[$columnIndexes['unit']]) ? trim($row[$columnIndexes['unit']]) : null;
                    $brandName = isset($columnIndexes['brand']) && isset($row[$columnIndexes['brand']]) ? trim($row[$columnIndexes['brand']]) : null;
                    $costPrice = isset($columnIndexes['cost_price']) && isset($row[$columnIndexes['cost_price']]) ? (float) $row[$columnIndexes['cost_price']] : 0;
                    $sellPrice = isset($columnIndexes['sell_price']) && isset($row[$columnIndexes['sell_price']]) ? (float) $row[$columnIndexes['sell_price']] : 0;
                    $stock = isset($columnIndexes['stock']) && isset($row[$columnIndexes['stock']]) ? (int) $row[$columnIndexes['stock']] : 0;
                    $alertQty = isset($columnIndexes['alert_quantity']) && isset($row[$columnIndexes['alert_quantity']]) ? (int) $row[$columnIndexes['alert_quantity']] : 5;
                    
                    if (empty($name)) {
                        $errorRows[] = "Row $rowNumber: Product name is required";
                        continue;
                    }
                    
                    // Find or create category
                    $categoryId = 1; // Default
                    if ($categoryName) {
                        $category = Category::firstOrCreate(['name' => $categoryName]);
                        $categoryId = $category->id;
                    }
                    
                    // Find or create unit
                    $unitId = 1; // Default
                    if ($unitName) {
                        $unit = Unit::firstOrCreate(['name' => $unitName]);
                        $unitId = $unit->id;
                    }
                    
                    // Find or create brand
                    $brandId = null;
                    if ($brandName) {
                        $brand = Brand::firstOrCreate(['name' => $brandName]);
                        $brandId = $brand->id;
                    }
                    
                    // Generate code if not provided
                    if (empty($code)) {
                        $code = 'PRD-' . strtoupper(uniqid());
                    }
                    
                    // Check if product exists
                    $existingProduct = Product::where('code', $code)->first();
                    
                    if ($existingProduct) {
                        // Update existing
                        $existingProduct->update([
                            'name' => $name,
                            'barcode' => $barcode,
                            'category_id' => $categoryId,
                            'unit_id' => $unitId,
                            'brand_id' => $brandId,
                            'cost_price' => $costPrice,
                            'sell_price' => $sellPrice,
                            'stock_quantity' => DB::raw('stock_quantity + ' . $stock),
                            'alert_quantity' => $alertQty,
                        ]);
                    } else {
                        // Create new
                        Product::create([
                            'name' => $name,
                            'code' => $code,
                            'barcode' => $barcode,
                            'category_id' => $categoryId,
                            'unit_id' => $unitId,
                            'brand_id' => $brandId,
                            'cost_price' => $costPrice,
                            'sell_price' => $sellPrice,
                            'stock_quantity' => $stock,
                            'alert_quantity' => $alertQty,
                            'status' => 'active',
                        ]);
                    }
                    
                    $successCount++;
                    
                } catch (\Exception $e) {
                    $errorRows[] = "Row $rowNumber: " . $e->getMessage();
                }
            }
            
            DB::commit();
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }
        
        fclose($handle);
        
        $message = "Successfully imported $successCount products.";
        if (!empty($errorRows)) {
            $message .= " Errors: " . implode('; ', array_slice($errorRows, 0, 5));
            if (count($errorRows) > 5) {
                $message .= " (+" . (count($errorRows) - 5) . " more)";
            }
        }
        
        return back()->with('success', $message);
    }
    
    public function downloadTemplate()
    {
        $headers = ['name', 'code', 'barcode', 'category', 'unit', 'brand', 'cost_price', 'sell_price', 'stock', 'alert_quantity'];
        $sampleData = [
            ['Sample Product', 'PRD-001', '123456789', 'Electronics', 'Piece', 'BrandX', '10.00', '15.00', '100', '10'],
        ];
        
        $callback = function() use ($headers, $sampleData) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);
            foreach ($sampleData as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        };
        
        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="product_import_template.csv"',
        ]);
    }
}