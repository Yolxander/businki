<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    public function store(Request $request)
    {
        // Convert settings to JSON string if it's an array
        if (is_array($request->settings)) {
            $request->merge(['settings' => json_encode($request->settings)]);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'website' => 'nullable|url|max:255',
            'role' => 'required|string|in:web dev,designer',
            'description' => 'required|string',
            'settings' => 'nullable|json'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $profile = Profile::create([
                'user_id' => Auth::id(),
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'website' => $request->website,
                'role' => $request->role,
                'description' => $request->description,
                'settings' => $request->settings ?? json_encode([])
            ]);

            return response()->json($profile, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to create profile', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, Profile $profile)
    {
        if ($profile->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Convert settings to JSON string if it's an array
        if (is_array($request->settings)) {
            $request->merge(['settings' => json_encode($request->settings)]);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'website' => 'nullable|url|max:255',
            'role' => 'sometimes|required|string|in:web dev,designer',
            'description' => 'sometimes|required|string',
            'settings' => 'nullable|json'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $profile->update($request->all());
            return response()->json($profile);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to update profile', 'error' => $e->getMessage()], 500);
        }
    }

    public function show(Profile $profile)
    {
        $profile->load(['contactInfo', 'notificationPreferences']);
        return response()->json($profile);
    }

    public function showBySlug($slug)
    {
        try {
            $profile = Profile::where('slug', $slug)
                ->with(['contactInfo', 'notificationPreferences'])
                ->firstOrFail();
            return response()->json($profile);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Profile not found'], 404);
        }
    }
}
