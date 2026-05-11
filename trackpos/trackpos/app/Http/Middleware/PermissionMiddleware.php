<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PermissionMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        // Allow access if user is admin
        if ($request->user() && $request->user()->hasRole('Admin')) {
            return $next($request);
        }

        // Check if user has the permission
        if (!$request->user() || !$request->user()->hasPermission($permission)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Unauthorized. You do not have permission to perform this action.'
                ], 403);
            }
            
            // For AJAX requests, return error message
            if ($request->ajax()) {
                return response()->json([
                    'message' => 'You do not have permission to perform this action.'
                ], 403);
            }
            
            return redirect()->back()->with('error', 'You do not have permission to access this resource.');
        }

        return $next($request);
    }
}