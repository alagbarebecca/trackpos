<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', '{{ $appName }}')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { 
            --primary: #7c3aed; /* Purple - primary brand color */
            --primary-hover: #6d28d9; 
            --primary-light: #a78bfa;
            --secondary: #059669; 
            --warning: #f59e0b; 
            --danger: #dc2626; 
            --info: #0891b2; 
            --bg-light: #f8fafc; 
            --bg-dark: #0f172a; 
            --surface-light: #ffffff; 
            --surface-dark: #1e293b; 
            --text-primary: #1e293b; 
            --text-secondary: #64748b; 
            --text-muted: #94a3b8; 
            --border: #e2e8f0; 
            --sidebar-width: 260px; 
            --sidebar-collapsed: 70px; 
            --topbar-height: 64px; 
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', system-ui, sans-serif; background: var(--bg-light); color: var(--text-primary); }
        
        /* Sidebar - Purple Dark Theme */
        .sidebar { position: fixed; left: 0; top: 0; bottom: 0; width: var(--sidebar-width); background: linear-gradient(180deg, #1e1b4b 0%, #0f0d1a 100%); display: flex; flex-direction: column; z-index: 1000; transition: width 0.3s ease; box-shadow: 2px 0 10px rgba(0,0,0,0.2); }
        .sidebar-header { height: var(--topbar-height); display: flex; align-items: center; padding: 0 20px; border-bottom: 1px solid rgba(255,255,255,0.1); flex-shrink: 0; }
        .sidebar-header .logo { display: flex; align-items: center; gap: 10px; text-decoration: none; color: #fff; font-weight: 700; font-size: 20px; }
        .sidebar-header .logo i { color: #a78bfa; }
        .sidebar-nav { flex: 1; overflow-y: auto; padding: 10px 0; }
        .nav-group { margin-bottom: 5px; }
        .nav-group-title { padding: 8px 20px 6px; font-size: 11px; font-weight: 600; text-transform: uppercase; color: #64748b; letter-spacing: 0.5px; }
        .nav-link { display: flex; align-items: center; gap: 12px; padding: 10px 20px; color: #94a3b8; text-decoration: none; font-size: 14px; font-weight: 500; border-left: 3px solid transparent; transition: all 0.15s ease; }
        .nav-link:hover { background: rgba(124, 58, 237, 0.15); color: #fff; }
        .nav-link.active { background: linear-gradient(90deg, rgba(124, 58, 237, 0.2) 0%, transparent 100%); color: #a78bfa; border-left-color: #7c3aed; }
        .nav-link i { width: 20px; text-align: center; font-size: 16px; }
        .nav-badge { margin-left: auto; padding: 2px 8px; font-size: 11px; border-radius: 10px; background: var(--danger); color: white; }
        
        /* Main Content Area */
        .main-wrapper { margin-left: var(--sidebar-width); min-height: 100vh; transition: margin 0.3s ease; background: var(--bg-light); }
        .main-wrapper.expanded { margin-left: var(--sidebar-collapsed); }
        
        /* Topbar - Matching Purple Dark Theme */
        .topbar { height: var(--topbar-height); background: linear-gradient(180deg, #1e1b4b 0%, #0f0d1a 100%); border-bottom: none; display: flex; align-items: center; justify-content: space-between; padding: 0 24px; position: sticky; top: 0; z-index: 100; box-shadow: 0 2px 10px rgba(0,0,0,0.15); }
        .topbar-left { display: flex; align-items: center; gap: 16px; }
        .topbar-left span { color: #fff; font-weight: 600; font-size: 16px; }
        .topbar-right { display: flex; align-items: center; gap: 16px; }
        .topbar-btn { background: rgba(255,255,255,0.1); border: none; padding: 8px 12px; color: #94a3b8; cursor: pointer; border-radius: 8px; font-size: 16px; transition: all 0.2s; }
        .topbar-btn:hover { background: rgba(124, 58, 237, 0.3); color: #fff; }
        
        /* Content & Cards */
        .content { padding: 24px; }
        .card { background: var(--surface-light); border: 1px solid var(--border); border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
        .card-header { padding: 16px 20px; border-bottom: 1px solid var(--border); font-weight: 600; background: transparent; }
        .card-body { padding: 20px; }
        
        /* Table Styling */
        .table { border-collapse: separate; border-spacing: 0; }
        .table th { font-weight: 600; font-size: 12px; text-transform: uppercase; color: var(--text-muted); border-bottom: 2px solid var(--border); padding: 14px 16px; background: #f8fafc; }
        .table td { padding: 14px 16px; vertical-align: middle; border-bottom: 1px solid var(--border); }
        .table tr:hover { background: #f8fafc; }
        
        /* Buttons */
        .btn-primary { background: var(--primary); border-color: var(--primary); }
        .btn-primary:hover { background: var(--primary-hover); border-color: var(--primary-hover); }
        
        /* User Dropdown */
        .user-dropdown { position: relative; }
        .user-dropdown .dropdown-toggle { display: flex; align-items: center; gap: 8px; padding: 8px 14px; border-radius: 8px; text-decoration: none; color: #fff; background: rgba(255,255,255,0.1); border: none; cursor: pointer; transition: all 0.2s; }
        .user-dropdown .dropdown-toggle:hover { background: rgba(59, 130, 246, 0.2); }
        .user-dropdown .dropdown-menu { position: absolute; top: 100%; right: 0; margin-top: 8px; background: var(--surface-light); border: 1px solid var(--border); border-radius: 10px; box-shadow: 0 10px 40px rgba(0,0,0,0.15); min-width: 200px; z-index: 1000; display: none; }
        .user-dropdown .dropdown-menu.show { display: block; }
        .user-dropdown .dropdown-menu .dropdown-item { display: block; padding: 12px 16px; color: var(--text-primary); text-decoration: none; transition: background 0.15s; }
        .user-dropdown .dropdown-menu .dropdown-item:hover { background: var(--bg-light); }
        
        /* Alert Messages */
        .alert-success { background: #dcfce7; border: none; color: #166534; border-radius: 8px; }
        .alert-danger { background: #fee2e2; border: none; color: #991b1b; border-radius: 8px; }
        
        /* Responsive */
        @media (max-width: 768px) { 
            .sidebar { width: var(--sidebar-collapsed); } 
            .sidebar .nav-text, .sidebar .nav-group-title { display: none; } 
            .main-wrapper { margin-left: var(--sidebar-collapsed); } 
        }
    </style>
</head>
<body class="{{ request()->routeIs('pos.*') ? 'pos-page' : '' }}">
    @auth
    <aside class="sidebar" id="sidebar">
        <?php
$appName = \App\Models\Setting::where('key', 'app_name')->first()?->value ?: 'TrackPOS';
$currencySymbol = \App\Models\Setting::where('key', 'currency_symbol')->first()?->value ?: '$';
?>
        <div class="sidebar-header">
            <a href="{{ route('dashboard') }}" class="logo">
                <i class="fas fa-cash-register"></i>
                <span class="nav-text">{{ $appName }}</span>
            </a>
        </div>
        <nav class="sidebar-nav">
            {{-- Overview - visible to all --}}
            <div class="nav-group">
                <div class="nav-group-title">Overview</div>
                <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="fas fa-th-large"></i>
                    <span class="nav-text">Dashboard</span>
                </a>
            </div>
            
            {{-- Sales & POS - view_pos or view_sales --}}
            @canany(['view_pos', 'view_sales', 'create_sales'])
            <div class="nav-group">
                <div class="nav-group-title">Sales & Operations</div>
                @can('view_pos')
                <a href="{{ route('pos.index') }}" class="nav-link {{ request()->routeIs('pos.index') ? 'active' : '' }}">
                    <i class="fas fa-calculator"></i>
                    <span class="nav-text">POS Terminal</span>
                </a>
                @endcan
                @can('view_sales')
                <a href="{{ route('sales.index') }}" class="nav-link {{ request()->routeIs('sales.index') ? 'active' : '' }}">
                    <i class="fas fa-shopping-bag"></i>
                    <span class="nav-text">Sales</span>
                </a>
                @endcan
            </div>
            @endcanany
            
            {{-- Inventory - manage_products --}}
            @can('manage_products')
            <div class="nav-group">
                <div class="nav-group-title">Inventory</div>
                @can('manage_categories')
                <a href="{{ route('categories.index') }}" class="nav-link {{ request()->routeIs('categories.index') ? 'active' : '' }}">
                    <i class="fas fa-tags"></i>
                    <span class="nav-text">Categories</span>
                </a>
                @endcan
                <a href="{{ route('products.index') }}" class="nav-link {{ request()->routeIs('products.index') ? 'active' : '' }}">
                    <i class="fas fa-boxes"></i>
                    <span class="nav-text">Products</span>
                </a>
                @can('manage_stock')
                <a href="{{ route('stock-adjustments.index') }}" class="nav-link {{ request()->routeIs('stock-adjustments.index') ? 'active' : '' }}">
                    <i class="fas fa-clipboard-list"></i>
                    <span class="nav-text">Stock Adjustments</span>
                </a>
                @endcan
                <a href="{{ route('products.low-stock') }}" class="nav-link {{ request()->routeIs('products.low-stock') ? 'active' : '' }}">
                    <i class="fas fa-exclamation-triangle" style="color: var(--warning);"></i>
                    <span class="nav-text">Low Stock Alert</span>
                </a>
                @can('view_reports')
                <a href="{{ route('reports.stock') }}" class="nav-link {{ request()->routeIs('reports.stock') ? 'active' : '' }}">
                    <i class="fas fa-warehouse"></i>
                    <span class="nav-text">Stock Report</span>
                </a>
                @endcan
                @can('manage_units')
                <a href="{{ route('units.index') }}" class="nav-link {{ request()->routeIs('units.index') ? 'active' : '' }}">
                    <i class="fas fa-balance-scale"></i>
                    <span class="nav-text">Units</span>
                </a>
                @endcan
            </div>
            @endcan
            
            {{-- People - customers, loyalty, suppliers --}}
            @canany(['manage_customers', 'manage_loyalty', 'manage_suppliers'])
            <div class="nav-group">
                <div class="nav-group-title">People</div>
                @can('manage_customers')
                <a href="{{ route('customers.index') }}" class="nav-link {{ request()->routeIs('customers.index') ? 'active' : '' }}">
                    <i class="fas fa-users"></i>
                    <span class="nav-text">Customers</span>
                </a>
                @endcan
                @can('manage_loyalty')
                <a href="{{ route('loyalty.index') }}" class="nav-link {{ request()->routeIs('loyalty.index') ? 'active' : '' }}">
                    <i class="fas fa-gift"></i>
                    <span class="nav-text">Loyalty</span>
                </a>
                @endcan
                @can('manage_suppliers')
                <a href="{{ route('suppliers.index') }}" class="nav-link {{ request()->routeIs('suppliers.index') ? 'active' : '' }}">
                    <i class="fas fa-truck"></i>
                    <span class="nav-text">Suppliers</span>
                </a>
                @endcan
            </div>
            @endcanany
            
            {{-- Stock operations --}}
            @can('manage_purchases')
            <a href="{{ route('purchases.index') }}" class="nav-link {{ request()->routeIs('purchases.index') ? 'active' : '' }}">
                <i class="fas fa-truck-loading"></i>
                <span class="nav-text">Purchases</span>
            </a>
            @endcan
            @can('manage_transfers')
            <a href="{{ route('transfers.index') }}" class="nav-link {{ request()->routeIs('transfers.index') ? 'active' : '' }}">
                <i class="fas fa-exchange-alt"></i>
                <span class="nav-text">Stock Transfer</span>
            </a>
            @endcan
            @can('manage_locations')
            <a href="{{ route('locations.index') }}" class="nav-link {{ request()->routeIs('locations.index') ? 'active' : '' }}">
                <i class="fas fa-store"></i>
                <span class="nav-text">Locations</span>
            </a>
            @endcan
            @can('import_export')
            <a href="{{ route('import-export.index') }}" class="nav-link {{ request()->routeIs('import-export.index') ? 'active' : '' }}">
                <i class="fas fa-file-import"></i>
                <span class="nav-text">Import/Export</span>
            </a>
            @endcan
            @can('manage_waste')
            <a href="{{ route('waste.index') }}" class="nav-link {{ request()->routeIs('waste.index') ? 'active' : '' }}">
                <i class="fas fa-trash-alt"></i>
                <span class="nav-text">Waste</span>
            </a>
            @endcan
            
            {{-- Reports --}}
            @can('view_reports')
            <div class="nav-group">
                <div class="nav-group-title">Reports</div>
                @can('manage_z_report')
                <a href="{{ route('reports.z-report') }}" class="nav-link {{ request()->routeIs('reports.z-report') ? 'active' : '' }}">
                    <i class="fas fa-calculator"></i>
                    <span class="nav-text">End of Day Report</span>
                </a>
                @endcan
                <a href="{{ route('reports.daily-sales') }}" class="nav-link {{ request()->routeIs('reports.daily-sales') ? 'active' : '' }}">
                    <i class="fas fa-clock"></i>
                    <span class="nav-text">Sales Report</span>
                </a>
                <a href="{{ route('reports.product-sales') }}" class="nav-link {{ request()->routeIs('reports.product-sales') ? 'active' : '' }}">
                    <i class="fas fa-chart-bar"></i>
                    <span class="nav-text">Product Report</span>
                </a>
                <a href="{{ route('reports.tax') }}" class="nav-link {{ request()->routeIs('reports.tax') ? 'active' : '' }}">
                    <i class="fas fa-file-invoice-dollar"></i>
                    <span class="nav-text">Tax Report</span>
                </a>
            </div>
            @endcan
            
            @can('manage_expenses')
            <a href="{{ route('expenses.index') }}" class="nav-link {{ request()->routeIs('expenses.index', 'expenses.categories') ? 'active' : '' }}">
                <i class="fas fa-receipt"></i>
                <span class="nav-text">Expenses</span>
            </a>
            @endcan
            
            {{-- Settings & Admin --}}
            @canany(['manage_settings', 'manage_roles', 'manage_users', 'manage_staff', 'view_audit_logs'])
            <div class="nav-group">
                <div class="nav-group-title">Settings</div>
                @can('manage_settings')
                <a href="{{ route('settings.index') }}" class="nav-link {{ request()->routeIs('settings.index') ? 'active' : '' }}">
                    <i class="fas fa-cog"></i>
                    <span class="nav-text">Settings</span>
                </a>
                @endcan
                @can('manage_roles')
                <a href="{{ route('roles.index') }}" class="nav-link {{ request()->routeIs('roles.index') ? 'active' : '' }}">
                    <i class="fas fa-user-tag"></i>
                    <span class="nav-text">Roles</span>
                </a>
                @endcan
                @can('manage_users')
                <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.index') ? 'active' : '' }}">
                    <i class="fas fa-user-cog"></i>
                    <span class="nav-text">User Management</span>
                </a>
                @endcan
                @can('manage_staff')
                <a href="{{ route('staff.index') }}" class="nav-link {{ request()->routeIs('staff.index') ? 'active' : '' }}">
                    <i class="fas fa-users-cog"></i>
                    <span class="nav-text">Staff</span>
                </a>
                @endcan
                @can('view_timeclock')
                <a href="{{ route('staff.timeclock') }}" class="nav-link {{ request()->routeIs('staff.timeclock') ? 'active' : '' }}">
                    <i class="fas fa-clock"></i>
                    <span class="nav-text">Time Clock</span>
                </a>
                @endcan
                @can('view_audit_logs')
                <a href="{{ route('audit-logs.index') }}" class="nav-link {{ request()->routeIs('audit-logs.index') ? 'active' : '' }}">
                    <i class="fas fa-history"></i>
                    <span class="nav-text">Audit Logs</span>
                </a>
                @endcan
            </div>
            @endcanany
            
            {{-- Returns --}}
            @can('process_returns')
            <a href="{{ route('returns.index') }}" class="nav-link {{ request()->routeIs('returns.index') ? 'active' : '' }}">
                <i class="fas fa-undo"></i>
                <span class="nav-text">Returns</span>
            </a>
            @endcan
        </nav>
    </aside>
    
    <div class="main-wrapper" id="mainContent">
        <header class="topbar">
            <div class="topbar-left">
                <button class="topbar-btn" id="toggleSidebar"><i class="fas fa-bars"></i></button>
                <span style="font-weight: 600;">@yield('page-title', 'Dashboard')</span>
            </div>
            <div class="topbar-right">
                <button class="topbar-btn" id="themeToggle"><i class="fas fa-moon"></i></button>
                <div class="user-dropdown">
                    <button class="dropdown-toggle" onclick="this.nextElementSibling.classList.toggle('show')">
                        <i class="fas fa-user-circle fa-lg"></i>
                        <span>{{ Auth::user()->name }}</span>
                        <i class="fas fa-chevron-down ms-2" style="font-size: 12px;"></i>
                    </button>
                    <div class="dropdown-menu">
                        <div style="padding: 12px 16px; border-bottom: 1px solid var(--border);">
                            <div style="font-weight: 600;">{{ Auth::user()->name }}</div>
                            <div style="font-size: 12px; color: var(--text-muted);">{{ ucfirst(Auth::user()->role) }}</div>
                        </div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item" style="width: 100%; text-align: left; border: none; background: none; cursor: pointer;">
                                <i class="fas fa-sign-out-alt me-2"></i> Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>
        
        @if(session('success'))<div class="alert alert-success alert-dismissible fade show" style="margin: 16px 24px 0;">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
        @if(session('error'))<div class="alert alert-danger alert-dismissible fade show" style="margin: 16px 24px 0;">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
        
        <main class="content">@yield('content')</main>
    </div>
    @else
    @yield('content')
    @endauth
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        var themeToggle = document.getElementById('themeToggle');
        var body = document.body;
        if (themeToggle) {
            if (localStorage.getItem('theme') === 'dark') { body.classList.add('dark-mode'); themeToggle.innerHTML = '<i class="fas fa-sun"></i>'; }
            themeToggle.addEventListener('click', function() { body.classList.toggle('dark-mode'); if (body.classList.contains('dark-mode')) { localStorage.setItem('theme', 'dark'); themeToggle.innerHTML = '<i class="fas fa-sun"></i>'; } else { localStorage.setItem('theme', 'light'); themeToggle.innerHTML = '<i class="fas fa-moon"></i>'; } });
        }
        var toggleSidebar = document.getElementById('toggleSidebar');
        if (toggleSidebar) {
            toggleSidebar.addEventListener('click', function() { document.getElementById('sidebar').classList.toggle('collapsed'); document.getElementById('mainContent').classList.toggle('expanded'); });
        }
        document.addEventListener('click', function(e) { if (!e.target.closest('.user-dropdown')) { document.querySelectorAll('.user-dropdown .dropdown-menu').forEach(function(el) { el.classList.remove('show'); }); } });
    </script>
    @yield('scripts')
</body>
</html>