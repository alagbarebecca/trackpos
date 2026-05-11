<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Blade;
use App\Models\Setting;
use Auth;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Custom @can directive - checks user permission
        Blade::directive('can', function ($permission) {
            return "<?php if(auth()->check() && auth()->user()->hasPermission({$permission})): ?>";
        });

        // Custom @endcan directive
        Blade::directive('endcan', function () {
            return "<?php endif; ?>";
        });

        // Custom @canany directive
        Blade::directive('canany', function ($permissions) {
            $permissions = str_replace(['[', ']', "'", '"'], '', $permissions);
            $perms = explode(',', $permissions);
            $php = "<?php if(auth()->check() && (";
            foreach ($perms as $i => $perm) {
                $perm = trim($perm);
                if ($i > 0) $php .= " || ";
                $php .= "auth()->user()->hasPermission('{$perm}')";
            }
            $php .= ")): ?>";
            return $php;
        });

        // Custom @endcanany directive
        Blade::directive('endcanany', function () {
            return "<?php endif; ?>";
        });

        // Share settings globally to all views (only if table exists)
        try {
            // Check if settings table exists - wrap in separate try-catch
            $hasSettingsTable = false;
            try {
                $hasSettingsTable = Schema::hasTable('settings');
            } catch (\Exception $e) {
                $hasSettingsTable = false;
            }
            
            if ($hasSettingsTable) {
                $settings = Setting::pluck('value', 'key')->toArray();
                
                View::share('appName', $settings['app_name'] ?? 'Loop POS');
                View::share('currencySymbol', $settings['currency_symbol'] ?? '$');
                View::share('defaultTaxRate', $settings['default_tax_rate'] ?? 0);
                View::share('companyName', $settings['company_name'] ?? '');
                View::share('companyAddress', $settings['company_address'] ?? '');
                View::share('companyPhone', $settings['company_phone'] ?? '');
                View::share('companyEmail', $settings['company_email'] ?? '');
                View::share('companyTaxId', $settings['company_tax_id'] ?? '');
            } else {
                // Default values when settings table doesn't exist yet
                View::share('appName', 'Loop POS');
                View::share('currencySymbol', '$');
                View::share('defaultTaxRate', 0);
                View::share('companyName', '');
                View::share('companyAddress', '');
                View::share('companyPhone', '');
                View::share('companyEmail', '');
                View::share('companyTaxId', '');
            }
        } catch (\Exception $e) {
            // Database doesn't exist yet - use default values
            View::share('appName', 'Loop POS');
            View::share('currencySymbol', '$');
            View::share('defaultTaxRate', 0);
            View::share('companyName', '');
            View::share('companyAddress', '');
            View::share('companyPhone', '');
            View::share('companyEmail', '');
            View::share('companyTaxId', '');
        }
    }
}
