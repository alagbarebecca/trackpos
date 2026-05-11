<?php

namespace App\Http\Controllers\Install;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;

class InstallController extends Controller
{
    /**
     * Check if already installed
     */
    public function isInstalled()
    {
        try {
            return DB::connection()->getPdo() && 
                   Schema::hasTable('users') && 
                   DB::table('users')->exists();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Handle the redirect based on installation status
     */
    public function index()
    {
        // Check if database file exists and where it's redirecting
        file_put_contents(storage_path('test.txt'), 'index called '.date('Y-m-d H:i:s')."\n", FILE_APPEND);
        
        if ($this->isInstalled()) {
            file_put_contents(storage_path('test.txt'), 'redirecting to login'."\n", FILE_APPEND);
            return redirect('/login');
        }
        file_put_contents(storage_path('test.txt'), 'redirecting to install step1'."\n", FILE_APPEND);
        return redirect('/install/step1');
    }

    /**
     * Step 1: Requirements check
     */
    public function step1()
    {
        if ($this->isInstalled()) {
            return redirect('/');
        }

        $requirements = [
            'php_version' => [
                'name' => 'PHP Version',
                'status' => version_compare(PHP_VERSION, '8.1.0', '>='),
                'message' => 'PHP 8.1+ required (Current: ' . PHP_VERSION . ')'
            ],
            'pdo_extension' => [
                'name' => 'PDO Extension',
                'status' => extension_loaded('pdo'),
                'message' => 'PDO extension required'
            ],
            'mbstring_extension' => [
                'name' => 'Mbstring Extension',
                'status' => extension_loaded('mbstring'),
                'message' => 'Mbstring extension required'
            ],
            'xml_extension' => [
                'name' => 'XML Extension',
                'status' => extension_loaded('xml'),
                'message' => 'XML extension required'
            ],
            'json_extension' => [
                'name' => 'JSON Extension',
                'status' => extension_loaded('json'),
                'message' => 'JSON extension required'
            ],
            'storage_writable' => [
                'name' => 'Storage Directory',
                'status' => is_writable(base_path('storage')),
                'message' => 'storage/ must be writable'
            ],
            'bootstrap_cache_writable' => [
                'name' => 'Bootstrap Cache',
                'status' => is_writable(base_path('bootstrap/cache')),
                'message' => 'bootstrap/cache/ must be writable'
            ],
        ];

        $allPassed = collect($requirements)->every(fn($req) => $req['status']);

        return view('install.step1', compact('requirements', 'allPassed'));
    }

    /**
     * Step 2: Database configuration
     */
    public function step2()
    {
        if ($this->isInstalled()) {
            return redirect('/');
        }

        $error = null;
        $dbConfig = [
            'DB_HOST' => '127.0.0.1',
            'DB_PORT' => '3306',
            'DB_DATABASE' => '',
            'DB_USERNAME' => '',
            'DB_PASSWORD' => '',
        ];

        // Priority: 1) old input from failed submission, 2) .env file, 3) defaults
        foreach ($dbConfig as $key => $default) {
            // Check for old input first (from previous failed submission)
            if (old($key)) {
                $dbConfig[$key] = old($key);
            } elseif (file_exists(base_path('.env'))) {
                // Then check .env file
                $dbConfig[$key] = env($key, $default);
            }
        }

        return view('install.step2', compact('dbConfig', 'error'));
    }

    /**
     * Step 3: Test database and install
     */
    public function step3(Request $request)
    {
        if ($this->isInstalled()) {
            return redirect('/');
        }

        // Determine if using SQLite or MySQL
        $dbPath = $request->DB_DATABASE ?? '';
        $isSqlite = empty($request->DB_HOST) || str_ends_with(strtolower($dbPath), '.sqlite') || str_ends_with(strtolower($dbPath), '.sqlite3');
        
        if ($isSqlite) {
            // For SQLite, validate database path
            $request->validate([
                'DB_DATABASE' => 'required',
            ]);
            
            // Write .env file with SQLite settings
            $envContent = file_exists(base_path('.env')) 
                ? file_get_contents(base_path('.env')) 
                : file_get_contents(base_path('.env.example'));
            
            $envMappings = [
                'DB_CONNECTION' => 'DB_CONNECTION=sqlite',
                'DB_DATABASE' => 'DB_DATABASE=' . $request->DB_DATABASE,
            ];
            
            foreach ($envMappings as $key => $value) {
                $envContent = preg_replace("/^$key=.*$/m", $value, $envContent);
                if (!preg_match("/^$key=.*$/m", $envContent)) {
                    $envContent .= "\n" . $value;
                }
            }
            
            file_put_contents(base_path('.env'), $envContent);
        } else {
            // MySQL validation
            $request->validate([
                'DB_HOST' => 'required',
                'DB_DATABASE' => 'required',
                'DB_USERNAME' => 'required',
            ]);

            // Test database connection
            try {
                config([
                    'database.connections.mysql.driver' => 'mysql',
                    'database.connections.mysql.host' => $request->DB_HOST,
                    'database.connections.mysql.port' => $request->DB_PORT ?? 3306,
                    'database.connections.mysql.database' => $request->DB_DATABASE,
                    'database.connections.mysql.username' => $request->DB_USERNAME,
                    'database.connections.mysql.password' => $request->DB_PASSWORD ?? '',
                ]);

                DB::connection('mysql')->getPdo();
            } catch (\Exception $e) {
                return back()->with('error', 'Cannot connect to database: ' . $e->getMessage())->withInput();
            }

            // Write .env file with MySQL settings
            $envContent = file_exists(base_path('.env')) 
                ? file_get_contents(base_path('.env')) 
                : file_get_contents(base_path('.env.example'));

            $envMappings = [
                'DB_CONNECTION' => 'DB_CONNECTION=mysql',
                'DB_HOST' => 'DB_HOST=' . $request->DB_HOST,
                'DB_PORT' => 'DB_PORT=' . ($request->DB_PORT ?? 3306),
                'DB_DATABASE' => 'DB_DATABASE=' . $request->DB_DATABASE,
                'DB_USERNAME' => 'DB_USERNAME=' . $request->DB_USERNAME,
                'DB_PASSWORD' => 'DB_PASSWORD=' . ($request->DB_PASSWORD ?? ''),
            ];

            foreach ($envMappings as $key => $value) {
                $envContent = preg_replace("/^$key=.*$/m", $value, $envContent);
                if (!preg_match("/^$key=.*$/m", $envContent)) {
                    $envContent .= "\n" . $value;
                }
            }

            file_put_contents(base_path('.env'), $envContent);
        }

        // Clear config cache
        Artisan::call('config:clear');

        // Generate application key if not set
        $envContent = file_get_contents(base_path('.env'));
        if (!preg_match('/^APP_KEY=.*$/m', $envContent) || strpos($envContent, 'APP_KEY=base64:') === false) {
            Artisan::call('key:generate', ['--force' => true]);
            $envContent = file_get_contents(base_path('.env'));
        }

        // Run migrations
        try {
            Artisan::call('migrate', ['--force' => true]);
        } catch (\Exception $e) {
            // If migration fails, try running without force
            try {
                Artisan::call('migrate');
            } catch (\Exception $e2) {
                return back()->with('error', 'Migration failed: ' . $e2->getMessage());
            }
        }

        // Seed default data
        $this->seedDefaults();

        // Redirect to admin creation
        return redirect()->route('install.step4');
    }

    /**
     * Show step 3 progress page (while migrations run)
     */
    public function step3Progress()
    {
        return view('install.step3');
    }

    /**
     * Seed default data
     */
    protected function seedDefaults()
    {
        // Create default units
        DB::table('units')->insertOrIgnore([
            ['name' => 'Piece', 'short_name' => 'pcs', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Kilogram', 'short_name' => 'kg', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Gram', 'short_name' => 'g', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Liter', 'short_name' => 'L', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Milliliter', 'short_name' => 'ml', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Cup', 'short_name' => 'cup', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Pack', 'short_name' => 'pack', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Box', 'short_name' => 'box', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Create default expense categories
        DB::table('expense_categories')->insertOrIgnore([
            ['name' => 'Rent', 'description' => 'Office/shop rent expenses', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Utilities', 'description' => 'Electricity, water, internet', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Supplies', 'description' => 'Office and cleaning supplies', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Salaries', 'description' => 'Staff salaries and wages', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Maintenance', 'description' => 'Equipment and premises maintenance', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Marketing', 'description' => 'Advertising and promotions', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Transport', 'description' => 'Delivery and transportation costs', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Miscellaneous', 'description' => 'Other miscellaneous expenses', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Create default settings
        $settings = [
            'app_name' => 'Loop POS',
            'currency_symbol' => '$',
            'language' => 'en',
            'default_tax_rate' => '0',
            'company_name' => '',
            'company_address' => '',
            'company_phone' => '',
            'company_email' => '',
            'company_tax_id' => '',
            'invoice_logo' => '',
            'invoice_footer' => 'Thank you for your business!',
            'default_discount_type' => 'percentage',
            'default_discount_value' => '0',
            'bulk_discount_threshold' => '10',
            'bulk_discount_percent' => '5',
        ];

        foreach ($settings as $key => $value) {
            DB::table('settings')->updateOrInsert(['key' => $key], ['value' => $value]);
        }

        // Create default roles
        DB::table('roles')->insertOrIgnore([
            ['name' => 'Admin', 'description' => 'Full access to all features', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Manager', 'description' => 'Manage products, sales, and reports', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Cashier', 'description' => 'Process sales only', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Create default permissions
        $permissions = [
            // Products
            ['name' => 'View Products', 'slug' => 'view_products', 'description' => 'View product list'],
            ['name' => 'Manage Products', 'slug' => 'manage_products', 'description' => 'Add, edit, delete products'],
            
            // Categories
            ['name' => 'View Categories', 'slug' => 'view_categories', 'description' => 'View categories'],
            ['name' => 'Manage Categories', 'slug' => 'manage_categories', 'description' => 'Add, edit, delete categories'],
            
            // Units
            ['name' => 'Manage Units', 'slug' => 'manage_units', 'description' => 'Add, edit, delete units'],
            
            // Sales
            ['name' => 'View Sales', 'slug' => 'view_sales', 'description' => 'View sales records'],
            ['name' => 'Create Sales', 'slug' => 'create_sales', 'description' => 'Create new sales'],
            
            // Purchases
            ['name' => 'View Purchases', 'slug' => 'view_purchases', 'description' => 'View purchase orders'],
            ['name' => 'Manage Purchases', 'slug' => 'manage_purchases', 'description' => 'Create purchase orders'],
            
            // Customers
            ['name' => 'View Customers', 'slug' => 'view_customers', 'description' => 'View customer list'],
            ['name' => 'Manage Customers', 'slug' => 'manage_customers', 'description' => 'Add, edit, delete customers'],
            
            // Suppliers
            ['name' => 'View Suppliers', 'slug' => 'view_suppliers', 'description' => 'View supplier list'],
            ['name' => 'Manage Suppliers', 'slug' => 'manage_suppliers', 'description' => 'Add, edit, delete suppliers'],
            
            // Expenses
            ['name' => 'View Expenses', 'slug' => 'view_expenses', 'description' => 'View expense records'],
            ['name' => 'Manage Expenses', 'slug' => 'manage_expenses', 'description' => 'Add, edit, delete expenses'],
            
            // Reports
            ['name' => 'View Reports', 'slug' => 'view_reports', 'description' => 'View all reports'],
            
            // Stock
            ['name' => 'Manage Stock', 'slug' => 'manage_stock', 'description' => 'Stock adjustments and transfers'],
            
            // Transfers
            ['name' => 'Manage Transfers', 'slug' => 'manage_transfers', 'description' => 'Manage stock transfers'],
            
            // Returns
            ['name' => 'Process Returns', 'slug' => 'process_returns', 'description' => 'Process returns'],
            
            // Waste
            ['name' => 'Manage Waste', 'slug' => 'manage_waste', 'description' => 'Manage waste'],
            
            // POS
            ['name' => 'View POS', 'slug' => 'view_pos', 'description' => 'Access POS screen'],
            
            // Locations
            ['name' => 'Manage Locations', 'slug' => 'manage_locations', 'description' => 'Manage locations'],
            
            // Loyalty
            ['name' => 'Manage Loyalty', 'slug' => 'manage_loyalty', 'description' => 'Manage loyalty program'],
            
            // Import/Export
            ['name' => 'Import Export', 'slug' => 'import_export', 'description' => 'Import export data'],
            
            // Z-Report
            ['name' => 'Manage Z-Report', 'slug' => 'manage_z_report', 'description' => 'Manage Z-Report'],
            
            // Roles
            ['name' => 'Manage Roles', 'slug' => 'manage_roles', 'description' => 'Manage roles'],
            
            // Users
            ['name' => 'View Users', 'slug' => 'view_users', 'description' => 'View user list'],
            ['name' => 'Manage Users', 'slug' => 'manage_users', 'description' => 'Add, edit, delete users'],
            
            // Settings
            ['name' => 'Manage Settings', 'slug' => 'manage_settings', 'description' => 'Manage system settings'],
            
            // Audit
            ['name' => 'View Audit Logs', 'slug' => 'view_audit_logs', 'description' => 'View system logs'],
        ];

        foreach ($permissions as $perm) {
            DB::table('permissions')->updateOrInsert(['slug' => $perm['slug']], $perm);
        }

        // Assign all permissions to admin role
        $adminRole = DB::table('roles')->where('name', 'Admin')->first();
        if ($adminRole) {
            $perms = DB::table('permissions')->get();
            $rolePermData = $perms->map(fn($p) => [
                'role_id' => $adminRole->id,
                'permission_id' => $p->id,
            ])->toArray();
            
            DB::table('role_permission')->insertOrIgnore($rolePermData);
        }
    }

    /**
     * Step 4: Create admin account
     */
    public function step4()
    {
        if ($this->isInstalled()) {
            return redirect('/');
        }

        // Check if admin already exists
        $adminExists = DB::table('users')->exists();

        return view('install.step4', compact('adminExists'));
    }

    /**
     * Step 5: Complete installation
     */
    public function complete(Request $request)
    {
        if ($this->isInstalled()) {
            return redirect('/');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        // Create admin user
        $userId = DB::table('users')->insertGetId([
            'username' => $request->username,
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'status' => true,
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Assign admin role
        $adminRole = DB::table('roles')->where('name', 'Admin')->first();
        if ($adminRole) {
            DB::table('user_role')->insert([
                'user_id' => $userId,
                'role_id' => $adminRole->id,
            ]);
        }

        // Create installed flag
        File::put(storage_path('installed'), json_encode([
            'installed_at' => now()->toDateTimeString(),
            'version' => '1.0.0',
        ]));

        return redirect('/login')->with('success', 'Installation complete! Please login.');
    }
}