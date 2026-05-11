<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            SetupRolesPermissionsSeeder::class,
        ]);
        
        // Create Units
        $units = [
            ['name' => 'Piece', 'short_name' => 'pcs'],
            ['name' => 'Kilogram', 'short_name' => 'kg'],
            ['name' => 'Gram', 'short_name' => 'g'],
            ['name' => 'Liter', 'short_name' => 'L'],
            ['name' => 'Milliliter', 'short_name' => 'mL'],
            ['name' => 'Box', 'short_name' => 'box'],
            ['name' => 'Pack', 'short_name' => 'pack'],
        ];

        foreach ($units as $unit) {
            Unit::create($unit);
        }

        // Create Categories
        $categories = [
            ['name' => 'Electronics', 'slug' => 'electronics', 'description' => 'Electronic devices and accessories'],
            ['name' => 'Food & Beverages', 'slug' => 'food-beverages', 'description' => 'Food and drink products'],
            ['name' => 'Clothing', 'slug' => 'clothing', 'description' => 'Apparel and fashion'],
            ['name' => 'Home & Garden', 'slug' => 'home-garden', 'description' => 'Home and garden products'],
            ['name' => 'Health & Beauty', 'slug' => 'health-beauty', 'description' => 'Health and beauty products'],
            ['name' => 'Sports', 'slug' => 'sports', 'description' => 'Sports equipment and accessories'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }

        // Create Brands
        $brands = [
            ['name' => 'Samsung', 'slug' => 'samsung'],
            ['name' => 'Apple', 'slug' => 'apple'],
            ['name' => 'Sony', 'slug' => 'sony'],
            ['name' => 'Nike', 'slug' => 'nike'],
            ['name' => 'Adidas', 'slug' => 'adidas'],
            ['name' => 'LG', 'slug' => 'lg'],
        ];

        foreach ($brands as $brand) {
            Brand::create($brand);
        }

        // Create Users with roles
        $adminUser = User::create([
            'name' => 'Admin User', 
            'email' => 'admin@trackpos.com', 
            'password' => Hash::make('admin123'), 
            'status' => true
        ]);
        $adminUser->roles()->attach(Role::where('name', 'Admin')->first()->id);

        $managerUser = User::create([
            'name' => 'Manager User', 
            'email' => 'manager@trackpos.com', 
            'password' => Hash::make('manager123'), 
            'status' => true
        ]);
        $managerUser->roles()->attach(Role::where('name', 'Manager')->first()->id);

        $cashierUser = User::create([
            'name' => 'Cashier User', 
            'email' => 'cashier@trackpos.com', 
            'password' => Hash::make('cashier123'), 
            'status' => true
        ]);
        $cashierUser->roles()->attach(Role::where('name', 'Cashier')->first()->id);

        // Create Customers
        $customers = [
            ['name' => 'John Doe', 'email' => 'john@example.com', 'phone' => '1234567890', 'address' => '123 Main St', 'city' => 'New York'],
            ['name' => 'Jane Smith', 'email' => 'jane@example.com', 'phone' => '9876543210', 'address' => '456 Oak Ave', 'city' => 'Los Angeles'],
            ['name' => 'Bob Johnson', 'email' => 'bob@example.com', 'phone' => '5551234567', 'address' => '789 Pine Rd', 'city' => 'Chicago'],
        ];

        foreach ($customers as $customer) {
            Customer::create($customer);
        }

        // Create Suppliers
        $suppliers = [
            ['name' => 'ABC Distributors', 'email' => 'abc@supplier.com', 'phone' => '1112223333', 'address' => '100 Supply St', 'city' => 'Miami', 'contact_person' => 'Mike Wilson'],
            ['name' => 'XYZ Supplies', 'email' => 'xyz@supplier.com', 'phone' => '4445556666', 'address' => '200 Warehouse Rd', 'city' => 'Seattle', 'contact_person' => 'Sarah Davis'],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::create($supplier);
        }

        // Create Products
        $products = [
            ['name' => 'Samsung Galaxy Phone', 'sku' => 'SGP001', 'barcode' => '1234567890123', 'category_id' => 1, 'brand_id' => 1, 'unit_id' => 1, 'cost_price' => 500.00, 'sell_price' => 699.99, 'tax_rate' => 10, 'stock_quantity' => 50, 'min_stock_level' => 10],
            ['name' => 'iPhone Case', 'sku' => 'IPC001', 'barcode' => '1234567890124', 'category_id' => 1, 'brand_id' => 2, 'unit_id' => 1, 'cost_price' => 10.00, 'sell_price' => 24.99, 'tax_rate' => 10, 'stock_quantity' => 100, 'min_stock_level' => 20],
            ['name' => 'Nike Running Shoes', 'sku' => 'NRS001', 'barcode' => '1234567890125', 'category_id' => 6, 'brand_id' => 4, 'unit_id' => 1, 'cost_price' => 50.00, 'sell_price' => 89.99, 'tax_rate' => 10, 'stock_quantity' => 30, 'min_stock_level' => 5],
            ['name' => 'Adidas T-Shirt', 'sku' => 'ATS001', 'barcode' => '1234567890126', 'category_id' => 3, 'brand_id' => 5, 'unit_id' => 1, 'cost_price' => 15.00, 'sell_price' => 29.99, 'tax_rate' => 10, 'stock_quantity' => 75, 'min_stock_level' => 15],
            ['name' => 'Organic Coffee Beans', 'sku' => 'OCB001', 'barcode' => '1234567890127', 'category_id' => 2, 'brand_id' => null, 'unit_id' => 2, 'cost_price' => 8.00, 'sell_price' => 15.99, 'tax_rate' => 5, 'stock_quantity' => 200, 'min_stock_level' => 50],
            ['name' => 'LED TV 55 inch', 'sku' => 'LTV001', 'barcode' => '1234567890128', 'category_id' => 1, 'brand_id' => 1, 'unit_id' => 1, 'cost_price' => 400.00, 'sell_price' => 549.99, 'tax_rate' => 15, 'stock_quantity' => 20, 'min_stock_level' => 5],
            ['name' => 'Face Cream', 'sku' => 'FCR001', 'barcode' => '1234567890129', 'category_id' => 5, 'brand_id' => null, 'unit_id' => 1, 'cost_price' => 12.00, 'sell_price' => 24.99, 'tax_rate' => 10, 'stock_quantity' => 80, 'min_stock_level' => 20],
            ['name' => 'Garden Hose', 'sku' => 'GHS001', 'barcode' => '1234567890130', 'category_id' => 4, 'brand_id' => null, 'unit_id' => 1, 'cost_price' => 20.00, 'sell_price' => 39.99, 'tax_rate' => 10, 'stock_quantity' => 25, 'min_stock_level' => 10],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }

        // Create a sample Sale
        $sale = Sale::create([
            'invoice_no' => 'INV-0001',
            'customer_id' => 1,
            'user_id' => 3,
            'subtotal' => 724.98,
            'discount' => 0,
            'tax' => 72.50,
            'total' => 797.48,
            'payment_method' => 'cash',
            'paid_amount' => 800.00,
            'change_amount' => 2.52,
            'status' => 'completed',
        ]);

        SaleItem::create([
            'sale_id' => $sale->id,
            'product_id' => 1,
            'quantity' => 1,
            'unit_price' => 699.99,
            'discount' => 0,
            'tax' => 70.00,
            'subtotal' => 769.99,
        ]);

        SaleItem::create([
            'sale_id' => $sale->id,
            'product_id' => 2,
            'quantity' => 2,
            'unit_price' => 24.99,
            'discount' => 0,
            'tax' => 5.00,
            'subtotal' => 54.98,
        ]);
    }
}
