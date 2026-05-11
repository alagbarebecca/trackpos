<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\File;

class CheckInstalled
{
    /**
     * Handle an incoming request.
     * Redirect to installer if not installed
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Allow installer routes
        if ($request->routeIs('install.*')) {
            // If already installed, redirect to login
            try {
                if (File::exists(storage_path('installed'))) {
                    return redirect()->route('login');
                }
                if (Schema::hasTable('users') && DB::table('users')->exists()) {
                    return redirect()->route('login');
                }
            } catch (\Exception $e) {
                // Continue - not installed yet
            }
            return $next($request);
        }
        
        // Allow login route
        if ($request->routeIs('login')) {
            return $next($request);
        }
        
        // Check if installed
        try {
            $installed = false;
            
            // Check storage/installed file
            if (File::exists(storage_path('installed'))) {
                $installed = true;
            }
            
            // Check users table
            if (Schema::hasTable('users') && DB::table('users')->exists()) {
                $installed = true;
            }
            
            if (!$installed) {
                session()->forget('login');
                return redirect()->route('install.step1');
            }
        } catch (\Exception $e) {
            // Database not configured - redirect to installer
            return redirect()->route('install.step1');
        }

        return $next($request);
    }
}