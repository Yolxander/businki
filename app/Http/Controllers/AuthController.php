<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        Log::info('User registration attempt started', [
            'email' => $request->email,
            'name' => $request->name,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        try {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|confirmed|min:6',
        ]);

            Log::info('Registration validation passed', ['email' => $validated['email']]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

            Log::info('User created successfully', [
                'user_id' => $user->id,
                'email' => $user->email,
                'name' => $user->name
            ]);

        // Automatically log in the user & return token
        $token = $user->createToken('auth_token')->plainTextToken;

            Log::info('Registration token created', [
                'user_id' => $user->id,
                'token_prefix' => substr($token, 0, 10) . '...'
            ]);

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Registration validation failed', [
                'email' => $request->email,
                'errors' => $e->errors(),
                'ip_address' => $request->ip()
            ]);
            throw $e;
        } catch (\Exception $e) {
            Log::error('Registration failed with exception', [
                'email' => $request->email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ip_address' => $request->ip()
            ]);
            return response()->json(['message' => 'Registration failed'], 500);
        }
    }

    public function login(Request $request)
    {
        Log::info('Login attempt started', [
            'email' => $request->email,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'method' => $request->getMethod(),
            'all_headers' => $request->headers->all()
        ]);

        if ($request->getMethod() === 'OPTIONS') {
            Log::info('OPTIONS request handled for login');
            return response()->noContent();
        }

        try {
        // Validate credentials
            Log::info('Starting login validation', ['email' => $request->email]);

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

            Log::info('Login validation passed', ['email' => $credentials['email']]);

            // Check if user exists before attempting auth
            $user = User::where('email', $credentials['email'])->first();
            if (!$user) {
                Log::warning('Login failed - user not found', [
                    'email' => $credentials['email'],
                    'ip_address' => $request->ip()
                ]);
                return response()->json(['message' => 'Invalid credentials'], 401);
            }

            Log::info('User found for login attempt', [
                'user_id' => $user->id,
                'email' => $user->email,
                'user_created_at' => $user->created_at
            ]);

        // Attempt to authenticate
            Log::info('Attempting authentication', ['email' => $credentials['email']]);

        if (!Auth::attempt($credentials)) {
                Log::warning('Authentication failed - invalid credentials', [
                    'email' => $credentials['email'],
                    'user_id' => $user->id,
                    'ip_address' => $request->ip(),
                    'password_provided' => !empty($credentials['password'])
                ]);
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

            Log::info('Authentication successful', ['email' => $credentials['email']]);

            $authenticatedUser = Auth::user();

            Log::info('Retrieved authenticated user', [
                'user_id' => $authenticatedUser ? $authenticatedUser->id : null,
                'email' => $authenticatedUser ? $authenticatedUser->email : null
            ]);

            if (!$authenticatedUser) {
                Log::error('User not found after successful authentication', [
                    'email' => $credentials['email'],
                    'auth_check' => Auth::check(),
                    'auth_id' => Auth::id()
                ]);
            return response()->json(['message' => 'User not found after auth'], 500);
        }

        // Ensure Personal Access Token trait is active
            if (!method_exists($authenticatedUser, 'createToken')) {
                Log::error('createToken method not available on user model', [
                    'user_id' => $authenticatedUser->id,
                    'user_class' => get_class($authenticatedUser),
                    'available_methods' => get_class_methods($authenticatedUser)
                ]);
            return response()->json(['message' => 'Token creation unavailable'], 500);
        }

            Log::info('Creating authentication token', ['user_id' => $authenticatedUser->id]);

            $token = $authenticatedUser->createToken('auth_token')->plainTextToken;

            Log::info('Token created successfully', [
                'user_id' => $authenticatedUser->id,
                'token_prefix' => substr($token, 0, 10) . '...'
            ]);

        // Load the user's profile
            Log::info('Loading user profile', ['user_id' => $authenticatedUser->id]);

            try {
                $authenticatedUser->load('profile');
                Log::info('User profile loaded successfully', [
                    'user_id' => $authenticatedUser->id,
                    'has_profile' => !is_null($authenticatedUser->profile)
                ]);
            } catch (\Exception $e) {
                Log::warning('Failed to load user profile', [
                    'user_id' => $authenticatedUser->id,
                    'error' => $e->getMessage()
                ]);
                // Continue without profile if it fails
            }

            Log::info('Login completed successfully', [
                'user_id' => $authenticatedUser->id,
                'email' => $authenticatedUser->email
            ]);

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
                'user' => $authenticatedUser,
        ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Login validation failed', [
                'email' => $request->email,
                'errors' => $e->errors(),
                'ip_address' => $request->ip()
            ]);
            throw $e;
        } catch (\Exception $e) {
            Log::error('Login failed with unexpected exception', [
                'email' => $request->email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ip_address' => $request->ip(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            return response()->json(['message' => 'Login failed due to server error'], 500);
        }
    }

    public function logout(Request $request)
    {
        Log::info('Logout attempt started', [
            'user_id' => $request->user() ? $request->user()->id : null,
            'ip_address' => $request->ip()
        ]);

        try {
            $user = $request->user();
            if (!$user) {
                Log::warning('Logout attempted without authenticated user');
                return response()->json(['message' => 'No authenticated user'], 401);
            }

            $currentToken = $user->currentAccessToken();
            if (!$currentToken) {
                Log::warning('No current access token found for logout', [
                    'user_id' => $user->id
                ]);
                return response()->json(['message' => 'No active token'], 400);
            }

            Log::info('Deleting access token', [
                'user_id' => $user->id,
                'token_id' => $currentToken->id
            ]);

            $currentToken->delete();

            Log::info('Logout completed successfully', [
                'user_id' => $user->id
            ]);

        return response()->json(['message' => 'Logged out']);

        } catch (\Exception $e) {
            Log::error('Logout failed with exception', [
                'user_id' => $request->user() ? $request->user()->id : null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ip_address' => $request->ip()
            ]);
            return response()->json(['message' => 'Logout failed'], 500);
        }
    }
}
