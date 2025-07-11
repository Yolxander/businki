<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class ProfileController extends Controller
{
    public function store(Request $request)
    {
        Log::info('Profile Store Request:', [
            'user_id' => Auth::id(),
            'request_data' => $request->all(),
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        // Convert settings to JSON string if it's an array
        if (is_array($request->settings)) {
            Log::info('Converting settings array to JSON:', [
                'user_id' => Auth::id(),
                'original_settings' => $request->settings
            ]);
            $request->merge(['settings' => json_encode($request->settings)]);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'website' => 'nullable|string|max:255',
            'role' => 'required|string|in:web dev,designer',
            'description' => 'required|string',
            'settings' => 'nullable|json'
        ]);

        // Custom validation for website URL
        if ($request->filled('website')) {
            $website = $request->website;
            // Add protocol if missing
            if (!preg_match('/^https?:\/\//', $website)) {
                $website = 'https://' . $website;
            }

            if (!filter_var($website, FILTER_VALIDATE_URL)) {
                $validator->errors()->add('website', 'The website field must be a valid URL.');
            } else {
                // Update the request with the properly formatted URL
                $request->merge(['website' => $website]);
            }
        }

        if ($validator->fails()) {
            Log::warning('Profile Store Validation Failed:', [
                'user_id' => Auth::id(),
                'errors' => $validator->errors()->toArray(),
                'request_data' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        Log::info('Profile Store Validation Passed:', [
            'user_id' => Auth::id(),
            'validated_data' => $validator->validated()
        ]);

        try {
            $profileData = [
                'user_id' => Auth::id(),
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'website' => $request->website,
                'role' => $request->role,
                'description' => $request->description,
                'settings' => $request->settings ?? json_encode([])
            ];

            Log::info('Creating Profile:', [
                'user_id' => Auth::id(),
                'profile_data' => $profileData
            ]);

            $profile = Profile::create($profileData);

            Log::info('Profile Created Successfully:', [
                'user_id' => Auth::id(),
                'profile_id' => $profile->id,
                'profile_data' => $profile->toArray()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Profile created successfully',
                'data' => $profile
            ], 201);
        } catch (\Exception $e) {
            Log::error('Profile Creation Error:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
                'request_data' => $request->all(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to create profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, Profile $profile)
    {
        Log::info('Profile Update Request:', [
            'user_id' => Auth::id(),
            'profile_id' => $profile->id,
            'request_data' => $request->all(),
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        if ($profile->user_id !== Auth::id()) {
            Log::warning('Profile Update - Unauthorized Access:', [
                'user_id' => Auth::id(),
                'profile_id' => $profile->id,
                'profile_user_id' => $profile->user_id
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to profile'
            ], 403);
        }

        Log::info('Profile Update - Authorization Check Passed:', [
            'user_id' => Auth::id(),
            'profile_id' => $profile->id
        ]);

        // Convert settings to JSON string if it's an array
        if (is_array($request->settings)) {
            Log::info('Converting settings array to JSON for update:', [
                'user_id' => Auth::id(),
                'profile_id' => $profile->id,
                'original_settings' => $request->settings
            ]);
            $request->merge(['settings' => json_encode($request->settings)]);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'website' => 'nullable|string|max:255',
            'role' => 'sometimes|required|string|in:web dev,designer',
            'description' => 'sometimes|required|string',
            'settings' => 'nullable|json'
        ]);

        // Custom validation for website URL
        if ($request->filled('website')) {
            $website = $request->website;
            // Add protocol if missing
            if (!preg_match('/^https?:\/\//', $website)) {
                $website = 'https://' . $website;
            }

            if (!filter_var($website, FILTER_VALIDATE_URL)) {
                $validator->errors()->add('website', 'The website field must be a valid URL.');
            } else {
                // Update the request with the properly formatted URL
                $request->merge(['website' => $website]);
            }
        }

        if ($validator->fails()) {
            Log::warning('Profile Update Validation Failed:', [
                'user_id' => Auth::id(),
                'profile_id' => $profile->id,
                'errors' => $validator->errors()->toArray(),
                'request_data' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        Log::info('Profile Update Validation Passed:', [
            'user_id' => Auth::id(),
            'profile_id' => $profile->id,
            'validated_data' => $validator->validated()
        ]);

        try {
            Log::info('Updating Profile:', [
                'user_id' => Auth::id(),
                'profile_id' => $profile->id,
                'current_data' => $profile->toArray(),
                'update_data' => $request->all()
            ]);

            $profile->update($request->all());

            Log::info('Profile Updated Successfully:', [
                'user_id' => Auth::id(),
                'profile_id' => $profile->id,
                'updated_data' => $profile->fresh()->toArray()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'data' => $profile
            ]);
        } catch (\Exception $e) {
            Log::error('Profile Update Error:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
                'profile_id' => $profile->id,
                'request_data' => $request->all(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to update profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Profile $profile)
    {
        Log::info('Profile Show Request:', [
            'user_id' => Auth::id(),
            'profile_id' => $profile->id,
            'method' => request()->method(),
            'url' => request()->fullUrl(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);

        try {
            Log::info('Loading Profile Relationships:', [
                'user_id' => Auth::id(),
                'profile_id' => $profile->id,
                'relationships' => ['contactInfo', 'notificationPreferences']
            ]);

            $profile->load(['contactInfo', 'notificationPreferences']);

            Log::info('Profile Retrieved Successfully:', [
                'user_id' => Auth::id(),
                'profile_id' => $profile->id,
                'has_contact_info' => !is_null($profile->contactInfo),
                'has_notification_preferences' => !is_null($profile->notificationPreferences)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Profile retrieved successfully',
                'data' => $profile
            ]);
        } catch (\Exception $e) {
            Log::error('Profile Show Error:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
                'profile_id' => $profile->id,
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve profile'
            ], 500);
        }
    }

    public function showBySlug($slug)
    {
        Log::info('Profile Show By Slug Request:', [
            'slug' => $slug,
            'method' => request()->method(),
            'url' => request()->fullUrl(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);

        try {
            Log::info('Searching Profile by Slug:', [
                'slug' => $slug
            ]);

            $profile = Profile::where('slug', $slug)
                ->with(['contactInfo', 'notificationPreferences'])
                ->firstOrFail();

            Log::info('Profile Found by Slug:', [
                'slug' => $slug,
                'profile_id' => $profile->id,
                'user_id' => $profile->user_id,
                'has_contact_info' => !is_null($profile->contactInfo),
                'has_notification_preferences' => !is_null($profile->notificationPreferences)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Profile retrieved successfully',
                'data' => $profile
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('Profile Not Found by Slug:', [
                'slug' => $slug,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Profile not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Profile Show By Slug Error:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'slug' => $slug,
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve profile'
            ], 500);
        }
    }
}
