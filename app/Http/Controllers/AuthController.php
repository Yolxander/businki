<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function showLogin()
    {
        Log::info('Login page accessed', [
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'referer' => request()->header('referer'),
            'timestamp' => now()
        ]);

        return Inertia::render('Auth/Login');
    }

    public function login(Request $request)
    {
        Log::info('Login attempt started', [
            'email' => $request->email,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'method' => $request->getMethod(),
            'timestamp' => now()
        ]);

        try {
            $credentials = $request->validate([
                'email' => ['required', 'email'],
                'password' => ['required'],
            ]);

            Log::info('Login validation passed', [
                'email' => $credentials['email'],
                'password_provided' => !empty($credentials['password'])
            ]);

            if (Auth::attempt($credentials)) {
                Log::info('Login successful', [
                    'user_id' => Auth::id(),
                    'email' => $credentials['email'],
                    'ip_address' => $request->ip()
                ]);

                $request->session()->regenerate();

                return redirect()->intended('/dashboard');
            }

            Log::warning('Login failed - invalid credentials', [
                'email' => $credentials['email'],
                'ip_address' => $request->ip(),
                'password_provided' => !empty($credentials['password'])
            ]);

            return back()->withErrors([
                'email' => 'The provided credentials do not match our records.',
            ])->onlyInput('email');

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Login validation failed', [
                'email' => $request->email,
                'errors' => $e->errors(),
                'ip_address' => $request->ip()
            ]);
            throw $e;
        } catch (\Exception $e) {
            Log::error('Login failed with exception', [
                'email' => $request->email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ip_address' => $request->ip()
            ]);
            return back()->withErrors([
                'email' => 'An error occurred during login. Please try again.',
            ])->onlyInput('email');
        }
    }

    public function logout(Request $request)
    {
        Log::info('Logout attempt', [
            'user_id' => Auth::id(),
            'ip_address' => $request->ip(),
            'timestamp' => now()
        ]);

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        Log::info('Logout successful', [
            'ip_address' => $request->ip(),
            'timestamp' => now()
        ]);

        return redirect('/login');
    }
}
