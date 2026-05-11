<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserStatus
{
    /**
     * Handle an incoming request.
     * Check if user account is active
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        
        if ($user && !$user->status) {
            auth()->logout();
            return redirect()->route('login')->with('error', 'Your account has been deactivated.');
        }
        
        return $next($request);
    }
}