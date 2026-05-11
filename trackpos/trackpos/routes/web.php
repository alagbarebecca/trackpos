<?php

use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\ZReportController;
use App\Http\Controllers\OfflineSyncController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductImportController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\CustomerReportController;
use App\Http\Controllers\PaymentReportController;
use App\Http\Controllers\ReturnController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\StockAdjustmentController;
use App\Http\Controllers\TaxReportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\Install\InstallController;
use App\Http\Controllers\ImportExportController;
use Illuminate\Support\Facades\Route;

// Install Routes (must be outside auth middleware)
Route::get('/', [InstallController::class, 'index'])->middleware('web');

Route::get('/install/step1', [InstallController::class, 'step1'])->name('install.step1');
Route::get('/install/step2', [InstallController::class, 'step2'])->name('install.step2');
Route::post('/install/step3', [InstallController::class, 'step3'])->name('install.step3');
Route::get('/install/step3-progress', [InstallController::class, 'step3Progress'])->name('install.step3-progress');
Route::get('/install/step4', [InstallController::class, 'step4'])->name('install.step4');
Route::post('/install/complete', [InstallController::class, 'complete'])->name('install.complete');

// Quick role setup route (remove after use)
Route::get('/setup-roles', function () {
    try {
        // Run the roles seeder
        \Illuminate\Support\Facades\Artisan::call('db:seed', ['--class' => 'SetupRolesPermissionsSeeder']);
        return response()->json(['success' => true, 'message' => 'Roles created successfully!']);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => $e->getMessage()]);
    }
});

// Auth Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected Routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/sales-rep/eod', [DashboardController::class, 'salesRepEod'])->name('sales-rep.eod');
    Route::get('/sales-rep/eod/print', [DashboardController::class, 'salesRepEodPrint'])->name('sales-rep.eod.print');
    
    // Audit Logs - require view_audit_logs permission
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index')->middleware('can:view_audit_logs');

    // Returns - require process_returns permission
    Route::resource('returns', ReturnController::class)->except(['edit', 'update'])->middleware('can:process_returns');

    // Z-Report - require manage_z_report permission
    Route::get('/reports/z-report', [ZReportController::class, 'index'])->name('reports.z-report')->middleware('can:manage_z_report');
    Route::get('/reports/z-report/print', [ZReportController::class, 'print'])->name('reports.z-report.print')->middleware('can:manage_z_report');

    // Stock Adjustments - require manage_stock permission
    Route::resource('stock-adjustments', StockAdjustmentController::class)->except(['edit', 'update'])->middleware('can:manage_stock');

    // POS Routes - require view_pos permission
    Route::middleware('can:view_pos')->group(function() {
        Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
        Route::get('/pos/search', [PosController::class, 'searchProducts']);
        Route::get('/pos/barcode', [PosController::class, 'findByBarcode']);
    });
    
    // Create Sales - require create_sales permission
    Route::post('/pos/sale', [PosController::class, 'storeSale'])->name('pos.sale')->middleware('can:create_sales');
    Route::post('/pos/sync-sale', [OfflineSyncController::class, 'syncSale'])->name('pos.sync-sale');
    Route::post('/pos/sync-held-sale', [OfflineSyncController::class, 'syncHeldSale'])->name('pos.sync-held-sale');
    Route::post('/pos/batch-sync', [OfflineSyncController::class, 'batchSync'])->name('pos.batch-sync');
    
    // Hold Sale Routes
    Route::post('/pos/hold', [PosController::class, 'holdSale'])->name('pos.hold');
    Route::get('/pos/held-sales', [PosController::class, 'getHeldSales']);
    Route::get('/pos/resume/{heldSale}', [PosController::class, 'resumeSale']);
    Route::delete('/pos/held-sale/{heldSale}', [PosController::class, 'deleteHeldSale']);
    
    // Products Routes - require manage_products permission
    Route::get('/products', [ProductController::class, 'index'])->name('products.index')->middleware('can:manage_products');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store')->middleware('can:manage_products');
    Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update')->middleware('can:manage_products');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy')->middleware('can:manage_products');
    Route::get('/products/low-stock', [ProductController::class, 'lowStock'])->name('products.low-stock')->middleware('can:manage_products');
    Route::get('/products/import', [ProductImportController::class, 'showImportForm'])->name('products.import')->middleware('can:manage_products');
    Route::post('/products/import', [ProductImportController::class, 'import'])->name('products.import.store')->middleware('can:manage_products');
    Route::get('/products/import/template', [ProductImportController::class, 'downloadTemplate'])->name('products.import.template')->middleware('can:manage_products');
    
    // Categories Routes - require manage_categories permission
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index')->middleware('can:manage_categories');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store')->middleware('can:manage_categories');
    Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update')->middleware('can:manage_categories');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy')->middleware('can:manage_categories');
    
    // Sales Routes
    Route::get('/sales', [SalesController::class, 'index'])->name('sales.index')->middleware('can:view_sales');
    Route::get('/sales/{sale}', [SalesController::class, 'show'])->name('sales.show')->middleware('can:view_sales');
    
    // Purchases Routes
    Route::get('/purchases', [PurchaseController::class, 'index'])->name('purchases.index')->middleware('can:manage_purchases');
    
    // Customers Routes
    Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index')->middleware('can:manage_customers');
    Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store')->middleware('can:manage_customers');
    Route::put('/customers/{customer}', [CustomerController::class, 'update'])->name('customers.update')->middleware('can:manage_customers');
    Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy')->middleware('can:manage_customers');
    
    // Suppliers Routes
    Route::get('/suppliers', [SupplierController::class, 'index'])->name('suppliers.index')->middleware('can:manage_suppliers');
    Route::post('/suppliers', [SupplierController::class, 'store'])->name('suppliers.store')->middleware('can:manage_suppliers');
    Route::put('/suppliers/{supplier}', [SupplierController::class, 'update'])->name('suppliers.update')->middleware('can:manage_suppliers');
    Route::delete('/suppliers/{supplier}', [SupplierController::class, 'destroy'])->name('suppliers.destroy')->middleware('can:manage_suppliers');
    
    // Reports Routes - require view_reports permission
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index')->middleware('can:view_reports');
    Route::get('/reports/daily-sales', [ReportController::class, 'dailySales'])->name('reports.daily-sales')->middleware('can:view_reports');
    Route::get('/reports/daily-sales/print', [ReportController::class, 'printDailySales'])->name('reports.print-daily')->middleware('can:view_reports');
    Route::get('/reports/daily-sales/export', [ReportController::class, 'exportDailySales'])->name('reports.export-daily')->middleware('can:view_reports');
    Route::get('/reports/product-sales', [ReportController::class, 'productSales'])->name('reports.product-sales')->middleware('can:view_reports');
    Route::get('/reports/stock', [ReportController::class, 'stockReport'])->name('reports.stock')->middleware('can:view_reports');
    Route::get('/reports/profit-loss', [ReportController::class, 'profitLoss'])->name('reports.profit-loss')->middleware('can:view_reports');
    Route::get('/reports/tax', [TaxReportController::class, 'index'])->name('reports.tax')->middleware('can:view_reports');
    Route::get('/reports/tax/print', [TaxReportController::class, 'print'])->name('reports.tax.print')->middleware('can:view_reports');
    Route::get('/reports/tax/export', [TaxReportController::class, 'export'])->name('reports.tax.export')->middleware('can:view_reports');
    Route::get('/reports/customer-sales', [CustomerReportController::class, 'index'])->name('reports.customer-sales')->middleware('can:view_reports');
    Route::get('/reports/customer-sales/export', [CustomerReportController::class, 'export'])->name('reports.customer-sales.export')->middleware('can:view_reports');
    Route::get('/reports/payment-method', [PaymentReportController::class, 'index'])->name('reports.payment-method')->middleware('can:view_reports');
    Route::get('/reports/payment-method/export', [PaymentReportController::class, 'export'])->name('reports.payment-method.export')->middleware('can:view_reports');
    
    // Users Routes - require manage_users permission
    Route::get('/users', [UserController::class, 'index'])->name('users.index')->middleware('can:manage_users');
    Route::post('/users', [UserController::class, 'store'])->name('users.store')->middleware('can:manage_users');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    
    // Roles Routes - require manage_roles permission
    Route::get('/roles', [RoleController::class, 'index'])->name('roles.index')->middleware('can:manage_roles');
    Route::post('/roles', [RoleController::class, 'store'])->name('roles.store')->middleware('can:manage_roles');
    Route::put('/roles/{role}', [RoleController::class, 'update'])->name('roles.update')->middleware('can:manage_roles');
    Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy')->middleware('can:manage_roles');
    Route::post('/roles/assign-permissions/{role}', [RoleController::class, 'assignPermissions'])->name('roles.assign-permissions')->middleware('can:manage_roles');
    Route::post('/roles/assign-user', [RoleController::class, 'assignToUser'])->name('roles.assign-user')->middleware('can:manage_roles');
    
    // Permissions Routes - require manage_roles permission
    Route::get('/permissions', [PermissionController::class, 'index'])->name('permissions.index')->middleware('can:manage_roles');
    Route::post('/permissions', [PermissionController::class, 'store'])->name('permissions.store')->middleware('can:manage_roles');
    Route::put('/permissions/{permission}', [PermissionController::class, 'update'])->name('permissions.update')->middleware('can:manage_roles');
    Route::delete('/permissions/{permission}', [PermissionController::class, 'destroy'])->name('permissions.destroy')->middleware('can:manage_roles');
    
    // Settings Route - require manage_settings permission
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index')->middleware('can:manage_settings');
    Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update')->middleware('can:manage_settings');
    
    // Units routes - require manage_units permission
    Route::resource('units', \App\Http\Controllers\UnitController::class)->except(['show'])->middleware('can:manage_units');
    
    // Loyalty routes - require manage_loyalty permission
    Route::resource('loyalty', \App\Http\Controllers\LoyaltyController::class)->except(['show'])->middleware('can:manage_loyalty');
    
    // Purchase Orders route - require manage_purchases permission
    Route::resource('purchase-orders', \App\Http\Controllers\PurchaseOrderController::class)->except(['show'])->middleware('can:manage_purchases');
    
    // Transfers route - require manage_transfers permission
    Route::resource('transfers', \App\Http\Controllers\TransferController::class)->except(['show'])->middleware('can:manage_transfers');
    
    // Locations route - require manage_locations permission
    Route::resource('locations', \App\Http\Controllers\LocationController::class)->except(['show'])->middleware('can:manage_locations');
    
    // Import/Export route - require import_export permission
    Route::resource('import-export', ImportExportController::class)->except(['show'])->middleware('can:import_export');
    
    // Import/Export extra routes
    Route::get('/import-export/template', [ImportExportController::class, 'sampleTemplate'])->name('import-export.template')->middleware('can:import_export');
    Route::get('/import-export/products', [ImportExportController::class, 'exportProducts'])->name('import-export.products')->middleware('can:import_export');
    Route::post('/import-export/products/import', [ImportExportController::class, 'importProducts'])->name('import-export.products.import');
    
    // Waste route
    Route::resource('waste', \App\Http\Controllers\WasteController::class)->except(['show'])->middleware('can:manage_waste');
    
    // Expenses route
    Route::resource('expenses', \App\Http\Controllers\ExpenseController::class)->except(['show']);
    Route::get('/expenses/categories', [\App\Http\Controllers\ExpenseController::class, 'categories'])->name('expenses.categories');
    
    // Staff route
    Route::resource('staff', \App\Http\Controllers\StaffController::class)->except(['show']);
    
    // Staff timeclock
    Route::get('/staff/timeclock', [\App\Http\Controllers\StaffController::class, 'timeclock'])->name('staff.timeclock');
    Route::post('/staff/clock-in', [\App\Http\Controllers\StaffController::class, 'clockIn'])->name('staff.clock-in');
    Route::post('/staff/clock-out', [\App\Http\Controllers\StaffController::class, 'clockOut'])->name('staff.clock-out');
    
    // Staff schedule
    Route::get('/staff/schedule', [\App\Http\Controllers\StaffController::class, 'schedule'])->name('staff.schedule');
    Route::get('/staff/weekly-schedule', [\App\Http\Controllers\StaffController::class, 'weeklySchedule'])->name('staff.weekly-schedule');
});

// Sales Routes (additional)
Route::get('/sales/{sale}/print', [SalesController::class, 'printReceipt'])->name('sales.print');
Route::get('/sales/search', [SalesController::class, 'search']);
