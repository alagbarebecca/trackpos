<?php
namespace Database\Seeders;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
class SetupRolesPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['name' => 'View Dashboard', 'slug' => 'view_dashboard', 'description' => 'Can view dashboard'],
            ['name' => 'View Products', 'slug' => 'view_products', 'description' => 'Can view products only'],
            ['name' => 'Manage Products', 'slug' => 'manage_products', 'description' => 'Can add, edit, delete products'],
            ['name' => 'Manage Categories', 'slug' => 'manage_categories', 'description' => 'Can manage categories'],
            ['name' => 'Manage Units', 'slug' => 'manage_units', 'description' => 'Can manage units'],
            ['name' => 'View POS', 'slug' => 'view_pos', 'description' => 'Can access POS'],
            ['name' => 'Create Sales', 'slug' => 'create_sales', 'description' => 'Can create sales transactions'],
            ['name' => 'Hold Sales', 'slug' => 'hold_sales', 'description' => 'Can hold/resume sales'],
            ['name' => 'View Sales', 'slug' => 'view_sales', 'description' => 'Can view sales records'],
            ['name' => 'Process Returns', 'slug' => 'process_returns', 'description' => 'Can process returns'],
            ['name' => 'Manage Stock', 'slug' => 'manage_stock', 'description' => 'Can adjust stock'],
            ['name' => 'Manage Transfers', 'slug' => 'manage_transfers', 'description' => 'Can manage transfers'],
            ['name' => 'Manage Waste', 'slug' => 'manage_waste', 'description' => 'Can manage waste'],
            ['name' => 'View Reports', 'slug' => 'view_reports', 'description' => 'Can view reports'],
            ['name' => 'View Customers', 'slug' => 'view_customers', 'description' => 'Can view customers only'],
            ['name' => 'Manage Customers', 'slug' => 'manage_customers', 'description' => 'Can manage customers'],
            ['name' => 'View Suppliers', 'slug' => 'view_suppliers', 'description' => 'Can view suppliers only'],
            ['name' => 'Manage Suppliers', 'slug' => 'manage_suppliers', 'description' => 'Can manage suppliers'],
            ['name' => 'View Purchases', 'slug' => 'view_purchases', 'description' => 'Can view purchases only'],
            ['name' => 'Manage Purchases', 'slug' => 'manage_purchases', 'description' => 'Can manage purchases'],
            ['name' => 'Manage Expenses', 'slug' => 'manage_expenses', 'description' => 'Can manage expenses'],
            ['name' => 'Manage Staff', 'slug' => 'manage_staff', 'description' => 'Can manage staff'],
            ['name' => 'View Timeclock', 'slug' => 'view_timeclock', 'description' => 'Can view timeclock'],
            ['name' => 'Manage Roles', 'slug' => 'manage_roles', 'description' => 'Can manage roles'],
            ['name' => 'Manage Users', 'slug' => 'manage_users', 'description' => 'Can manage users'],
            ['name' => 'Manage Settings', 'slug' => 'manage_settings', 'description' => 'Can manage system settings'],
            ['name' => 'View Audit Logs', 'slug' => 'view_audit_logs', 'description' => 'Can view audit logs'],
            ['name' => 'Manage Locations', 'slug' => 'manage_locations', 'description' => 'Can manage locations'],
            ['name' => 'Manage Loyalty', 'slug' => 'manage_loyalty', 'description' => 'Can manage loyalty program'],
            ['name' => 'Import/Export', 'slug' => 'import_export', 'description' => 'Can import/export data'],
            ['name' => 'Manage Z-Report', 'slug' => 'manage_z_report', 'description' => 'Can manage Z-Report'],
        ];

        $permissionIds = [];
        foreach ($permissions as $p) {
            $perm = Permission::updateOrCreate(['slug' => $p['slug']], $p);
            $permissionIds[$p['slug']] = $perm->id;
        }

        $adminRole = Role::updateOrCreate(['name' => 'Admin'], ['description' => 'Full system access']);
        $adminRole->permissions()->sync(array_values($permissionIds));

        $managerRole = Role::updateOrCreate(['name' => 'Manager'], ['description' => 'Can manage most operations']);
        $managerPerms = array_filter($permissionIds, fn($key) => !in_array($key, ['manage_roles', 'manage_users', 'view_audit_logs', 'manage_settings']), ARRAY_FILTER_USE_KEY);
        $managerRole->permissions()->sync(array_values($managerPerms));

        $cashierRole = Role::updateOrCreate(['name' => 'Cashier'], ['description' => 'POS operations only']);
        $cashierRole->permissions()->sync([
            $permissionIds['view_dashboard'], $permissionIds['view_pos'], $permissionIds['create_sales'], $permissionIds['hold_sales'],
            $permissionIds['process_returns'], $permissionIds['view_customers'], $permissionIds['manage_customers'], $permissionIds['view_products'],
        ]);

        $salesRepRole = Role::updateOrCreate(['name' => 'Sales Rep'], ['description' => 'Sales Representative with customer management']);
        $salesRepRole->permissions()->sync([
            $permissionIds['view_dashboard'], $permissionIds['view_pos'], $permissionIds['create_sales'], $permissionIds['hold_sales'],
            $permissionIds['view_sales'], $permissionIds['view_products'], $permissionIds['view_customers'], $permissionIds['manage_customers'],
        ]);

        $supervisorRole = Role::updateOrCreate(['name' => 'Supervisor'], ['description' => 'Supervisor with team and inventory oversight']);
        $supervisorRole->permissions()->sync([
            $permissionIds['view_dashboard'], $permissionIds['view_pos'], $permissionIds['create_sales'], $permissionIds['hold_sales'],
            $permissionIds['view_sales'], $permissionIds['process_returns'], $permissionIds['view_products'], $permissionIds['manage_products'],
            $permissionIds['manage_stock'], $permissionIds['manage_transfers'], $permissionIds['manage_waste'], $permissionIds['view_reports'],
            $permissionIds['view_customers'], $permissionIds['manage_customers'], $permissionIds['view_suppliers'], $permissionIds['manage_suppliers'],
            $permissionIds['view_purchases'], $permissionIds['manage_purchases'], $permissionIds['view_audit_logs'], $permissionIds['manage_z_report'],
        ]);

        $accountantRole = Role::updateOrCreate(['name' => 'Accountant'], ['description' => 'Accountant with financial access']);
        $accountantRole->permissions()->sync([
            $permissionIds['view_dashboard'], $permissionIds['view_reports'], $permissionIds['view_sales'],
            $permissionIds['view_purchases'], $permissionIds['manage_purchases'], $permissionIds['view_suppliers'], $permissionIds['manage_suppliers'],
            $permissionIds['manage_expenses'],
        ]);

        User::updateOrCreate(['email' => 'admin@pos.com'], ['username' => 'admin', 'name' => 'Administrator', 'password' => Hash::make('admin123'), 'status' => true])->roles()->sync([$adminRole->id]);
        User::updateOrCreate(['email' => 'manager@pos.com'], ['username' => 'manager', 'name' => 'Manager User', 'password' => Hash::make('manager123'), 'status' => true])->roles()->sync([$managerRole->id]);
        User::updateOrCreate(['email' => 'cashier@pos.com'], ['username' => 'cashier', 'name' => 'Cashier User', 'password' => Hash::make('cashier123'), 'status' => true])->roles()->sync([$cashierRole->id]);
        User::updateOrCreate(['email' => 'salesrep@pos.com'], ['username' => 'salesrep', 'name' => 'Sales Rep User', 'password' => Hash::make('salesrep123'), 'status' => true])->roles()->sync([$salesRepRole->id]);

        $this->command->info('Roles and Permissions seeded successfully!');
    }
}
