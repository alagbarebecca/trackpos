<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    protected $maxAttempts = 5;
    protected $decaySeconds = 60;

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required',
        ]);

        $login = $request->input('login');
        $password = $request->input('password');

        // Rate limiting check - use login as throttle key
        $throttleKey = Str::lower($login) . '|' . $request->ip();
        
        if (RateLimiter::tooManyAttempts($throttleKey, $this->maxAttempts)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return back()->withErrors([
                'login' => 'Too many login attempts. Please try again in ' . ceil($seconds / 60) . ' minute(s).'
            ])->withInput();
        }

        // Check if login is email or username
        $loginInput = $request->input('login');
        
        // Determine if login is email or username
        if (filter_var($loginInput, FILTER_VALIDATE_EMAIL)) {
            $user = User::where('email', $loginInput)->first();
        } else {
            // Try username first, then name (full name)
            $user = User::where('username', $loginInput)->first();
            if (!$user) {
                $user = User::where('name', $loginInput)->first();
            }
        }

        if (!$user) {
            RateLimiter::hit($throttleKey, $this->decaySeconds);
            return back()->withErrors(['login' => 'Invalid credentials'])->withInput();
        }

        if (!Hash::check($password, $user->password)) {
            RateLimiter::hit($throttleKey, $this->decaySeconds);
            return back()->withErrors(['login' => 'Invalid credentials'])->withInput();
        }

        // Check status - allow login if status is null or true (not false)
        if ($user->status === false || $user->status === 0) {
            return back()->withErrors(['login' => 'Your account has been deactivated'])->withInput();
        }

        // Clear rate limiter on successful login
        RateLimiter::clear($throttleKey);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
