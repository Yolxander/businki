<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class PackageController extends Controller
{
    /**
     * Display a listing of the packages.
     */
    public function index(Request $request)
    {
        Log::info('Package Index Request:', [
            'user_id' => $request->user()->id,
            'query_params' => $request->all(),
            'method' => $request->method(),
            'url' => $request->fullUrl()
        ]);

        try {
            $user = $request->user();
            $profile = $user->profile;

            Log::info('User Profile Check:', [
                'user_id' => $user->id,
                'has_profile' => !is_null($profile),
                'profile_id' => $profile ? $profile->id : null
            ]);

            if (!$profile) {
                Log::warning('Package Index - Profile Not Found:', [
                    'user_id' => $user->id
                ]);
                return response()->json(['error' => 'Profile not found'], 404);
            }

            $query = Package::where('profile_id', $profile->id)->with('features');

            // Apply search filter
            if ($request->has('search') && !empty($request->search)) {
                Log::info('Applying Search Filter:', ['search_term' => $request->search]);
                $query->search($request->search);
            }

            // Apply type filter
            if ($request->has('type') && !empty($request->type)) {
                Log::info('Applying Type Filter:', ['type' => $request->type]);
                $query->byType($request->type);
            }

            // Apply billing cycle filter
            if ($request->has('billing_cycle') && !empty($request->billing_cycle)) {
                Log::info('Applying Billing Cycle Filter:', ['billing_cycle' => $request->billing_cycle]);
                $query->byBillingCycle($request->billing_cycle);
            }

            // Apply active filter
            if ($request->has('is_active')) {
                Log::info('Applying Active Filter:', ['is_active' => $request->boolean('is_active')]);
                $query->where('is_active', $request->boolean('is_active'));
            }

            $packages = $query->orderBy('created_at', 'desc')->paginate(15);

            // Transform the data to include features as an array
            $packages->getCollection()->transform(function ($package) {
                $package->features = $package->features->pluck('feature')->toArray();
                return $package;
            });

            Log::info('Package Index Success:', [
                'profile_id' => $profile->id,
                'total_packages' => $packages->total(),
                'current_page' => $packages->currentPage(),
                'per_page' => $packages->perPage(),
                'packages_count' => count($packages->items())
            ]);

            return response()->json([
                'data' => $packages->items(),
                'pagination' => [
                    'current_page' => $packages->currentPage(),
                    'last_page' => $packages->lastPage(),
                    'per_page' => $packages->perPage(),
                    'total' => $packages->total(),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Package Index Error:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $request->user()->id,
                'request_data' => $request->all()
            ]);
            return response()->json(['error' => 'Failed to fetch packages'], 500);
        }
    }

    /**
     * Store a newly created package in storage.
     */
    public function store(Request $request)
    {
        Log::info('Package Store Request:', [
            'user_id' => $request->user()->id,
            'request_data' => $request->all(),
            'method' => $request->method(),
            'url' => $request->fullUrl()
        ]);

        try {
            $validator = Validator::make($request->all(), Package::rules());

            if ($validator->fails()) {
                Log::warning('Package Store Validation Failed:', [
                    'user_id' => $request->user()->id,
                    'errors' => $validator->errors()->toArray(),
                    'request_data' => $request->all()
                ]);
                return response()->json(['errors' => $validator->errors()], 422);
            }

            Log::info('Package Store Validation Passed:', [
                'user_id' => $request->user()->id,
                'validated_data' => $validator->validated()
            ]);

            $user = $request->user();
            $profile = $user->profile;

            Log::info('User Profile Check for Store:', [
                'user_id' => $user->id,
                'has_profile' => !is_null($profile),
                'profile_id' => $profile ? $profile->id : null
            ]);

            if (!$profile) {
                Log::warning('Package Store - Profile Not Found:', [
                    'user_id' => $user->id
                ]);
                return response()->json(['error' => 'Profile not found'], 404);
            }

            $packageData = [
                'profile_id' => $profile->id,
                'name' => $request->name,
                'description' => $request->description,
                'type' => $request->type,
                'price' => $request->price,
                'billing_cycle' => $request->get('billing_cycle', 'One-time'),
                'is_active' => $request->get('is_active', true),
            ];

            Log::info('Creating Package:', [
                'profile_id' => $profile->id,
                'package_data' => $packageData,
                'features_count' => $request->has('features') ? count($request->features) : 0
            ]);

            $package = Package::create($packageData);

            // Handle features if provided
            if ($request->has('features') && is_array($request->features)) {
                Log::info('Creating Package Features:', [
                    'package_id' => $package->id,
                    'features' => $request->features
                ]);

                foreach ($request->features as $index => $feature) {
                    if (!empty($feature)) {
                        $package->features()->create([
                            'feature' => $feature,
                            'sort_order' => $index,
                        ]);
                    }
                }

                Log::info('Package Features Created:', [
                    'package_id' => $package->id,
                    'features_created' => $package->features()->count()
                ]);
            }

            // Load features for response
            $package->load('features');
            $package->features = $package->features->pluck('feature')->toArray();

            Log::info('Package Created Successfully:', [
                'package_id' => $package->id,
                'profile_id' => $profile->id,
                'package_data' => $package->toArray()
            ]);

            return response()->json([
                'message' => 'Package created successfully',
                'data' => $package
            ], 201);
        } catch (\Exception $e) {
            Log::error('Package Creation Error:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $request->user()->id,
                'request_data' => $request->all()
            ]);
            return response()->json(['error' => 'Failed to create package'], 500);
        }
    }

    /**
     * Display the specified package.
     */
    public function show(Request $request, $id)
    {
        Log::info('Package Show Request:', [
            'user_id' => $request->user()->id,
            'package_id' => $id,
            'method' => $request->method(),
            'url' => $request->fullUrl()
        ]);

        try {
            $user = $request->user();
            $profile = $user->profile;

            Log::info('User Profile Check for Show:', [
                'user_id' => $user->id,
                'has_profile' => !is_null($profile),
                'profile_id' => $profile ? $profile->id : null
            ]);

            if (!$profile) {
                Log::warning('Package Show - Profile Not Found:', [
                    'user_id' => $user->id
                ]);
                return response()->json(['error' => 'Profile not found'], 404);
            }

            $package = Package::where('profile_id', $profile->id)
                             ->where('id', $id)
                             ->with('features')
                             ->first();

            if (!$package) {
                Log::warning('Package Show - Package Not Found:', [
                    'user_id' => $user->id,
                    'profile_id' => $profile->id,
                    'package_id' => $id
                ]);
                return response()->json(['error' => 'Package not found'], 404);
            }

            // Transform features to array
            $package->features = $package->features->pluck('feature')->toArray();

            Log::info('Package Show Success:', [
                'package_id' => $package->id,
                'profile_id' => $profile->id,
                'features_count' => count($package->features),
                'package_data' => $package->toArray()
            ]);

            return response()->json(['data' => $package]);
        } catch (\Exception $e) {
            Log::error('Package Show Error:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $request->user()->id,
                'package_id' => $id
            ]);
            return response()->json(['error' => 'Failed to fetch package'], 500);
        }
    }

    /**
     * Update the specified package in storage.
     */
    public function update(Request $request, $id)
    {
        Log::info('Package Update Request:', [
            'user_id' => $request->user()->id,
            'package_id' => $id,
            'request_data' => $request->all(),
            'method' => $request->method(),
            'url' => $request->fullUrl()
        ]);

        try {
            $user = $request->user();
            $profile = $user->profile;

            Log::info('User Profile Check for Update:', [
                'user_id' => $user->id,
                'has_profile' => !is_null($profile),
                'profile_id' => $profile ? $profile->id : null
            ]);

            if (!$profile) {
                Log::warning('Package Update - Profile Not Found:', [
                    'user_id' => $user->id
                ]);
                return response()->json(['error' => 'Profile not found'], 404);
            }

            $package = Package::where('profile_id', $profile->id)
                             ->where('id', $id)
                             ->first();

            if (!$package) {
                Log::warning('Package Update - Package Not Found:', [
                    'user_id' => $user->id,
                    'profile_id' => $profile->id,
                    'package_id' => $id
                ]);
                return response()->json(['error' => 'Package not found'], 404);
            }

            Log::info('Package Found for Update:', [
                'package_id' => $package->id,
                'current_data' => $package->toArray(),
                'current_features_count' => $package->features()->count()
            ]);

            $validator = Validator::make($request->all(), Package::updateRules());

            if ($validator->fails()) {
                Log::warning('Package Update Validation Failed:', [
                    'package_id' => $package->id,
                    'user_id' => $user->id,
                    'errors' => $validator->errors()->toArray(),
                    'request_data' => $request->all()
                ]);
                return response()->json(['errors' => $validator->errors()], 422);
            }

            Log::info('Package Update Validation Passed:', [
                'package_id' => $package->id,
                'validated_data' => $validator->validated()
            ]);

            $updateData = $request->only([
                'name', 'description', 'type', 'price',
                'billing_cycle', 'is_active'
            ]);

            Log::info('Updating Package:', [
                'package_id' => $package->id,
                'update_data' => $updateData
            ]);

            $package->update($updateData);

            // Handle features update if provided
            if ($request->has('features')) {
                Log::info('Updating Package Features:', [
                    'package_id' => $package->id,
                    'new_features' => $request->features,
                    'old_features_count' => $package->features()->count()
                ]);

                // Delete existing features
                $deletedFeatures = $package->features()->delete();
                Log::info('Deleted Old Features:', [
                    'package_id' => $package->id,
                    'deleted_count' => $deletedFeatures
                ]);

                // Create new features
                if (is_array($request->features)) {
                    foreach ($request->features as $index => $feature) {
                        if (!empty($feature)) {
                            $package->features()->create([
                                'feature' => $feature,
                                'sort_order' => $index,
                            ]);
                        }
                    }
                }

                Log::info('Package Features Updated:', [
                    'package_id' => $package->id,
                    'new_features_count' => $package->features()->count()
                ]);
            }

            // Load features for response
            $package->load('features');
            $package->features = $package->features->pluck('feature')->toArray();

            Log::info('Package Updated Successfully:', [
                'package_id' => $package->id,
                'profile_id' => $profile->id,
                'updated_data' => $package->toArray()
            ]);

            return response()->json([
                'message' => 'Package updated successfully',
                'data' => $package
            ]);
        } catch (\Exception $e) {
            Log::error('Package Update Error:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $request->user()->id,
                'package_id' => $id,
                'request_data' => $request->all()
            ]);
            return response()->json(['error' => 'Failed to update package'], 500);
        }
    }

    /**
     * Remove the specified package from storage.
     */
    public function destroy(Request $request, $id)
    {
        Log::info('Package Destroy Request:', [
            'user_id' => $request->user()->id,
            'package_id' => $id,
            'method' => $request->method(),
            'url' => $request->fullUrl()
        ]);

        try {
            $user = $request->user();
            $profile = $user->profile;

            Log::info('User Profile Check for Destroy:', [
                'user_id' => $user->id,
                'has_profile' => !is_null($profile),
                'profile_id' => $profile ? $profile->id : null
            ]);

            if (!$profile) {
                Log::warning('Package Destroy - Profile Not Found:', [
                    'user_id' => $user->id
                ]);
                return response()->json(['error' => 'Profile not found'], 404);
            }

            $package = Package::where('profile_id', $profile->id)
                             ->where('id', $id)
                             ->first();

            if (!$package) {
                Log::warning('Package Destroy - Package Not Found:', [
                    'user_id' => $user->id,
                    'profile_id' => $profile->id,
                    'package_id' => $id
                ]);
                return response()->json(['error' => 'Package not found'], 404);
            }

            Log::info('Package Found for Destroy:', [
                'package_id' => $package->id,
                'current_data' => $package->toArray(),
                'features_count' => $package->features()->count()
            ]);

            // Soft delete by setting is_active to false
            $package->update(['is_active' => false]);

            Log::info('Package Deleted Successfully:', [
                'package_id' => $package->id,
                'profile_id' => $profile->id
            ]);

            return response()->json([
                'message' => 'Package deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Package Delete Error:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $request->user()->id,
                'package_id' => $id
            ]);
            return response()->json(['error' => 'Failed to delete package'], 500);
        }
    }

    /**
     * Get packages by type.
     */
    public function getByType(Request $request, $type)
    {
        Log::info('Package GetByType Request:', [
            'user_id' => $request->user()->id,
            'type' => $type,
            'method' => $request->method(),
            'url' => $request->fullUrl()
        ]);

        try {
            $user = $request->user();
            $profile = $user->profile;

            Log::info('User Profile Check for GetByType:', [
                'user_id' => $user->id,
                'has_profile' => !is_null($profile),
                'profile_id' => $profile ? $profile->id : null
            ]);

            if (!$profile) {
                Log::warning('Package GetByType - Profile Not Found:', [
                    'user_id' => $user->id
                ]);
                return response()->json(['error' => 'Profile not found'], 404);
            }

            $packages = Package::where('profile_id', $profile->id)
                              ->byType($type)
                              ->active()
                              ->with('features')
                              ->orderBy('created_at', 'desc')
                              ->get();

            // Transform features to array
            $packages->transform(function ($package) {
                $package->features = $package->features->pluck('feature')->toArray();
                return $package;
            });

            Log::info('Package GetByType Success:', [
                'profile_id' => $profile->id,
                'type' => $type,
                'packages_count' => $packages->count()
            ]);

            return response()->json(['data' => $packages]);
        } catch (\Exception $e) {
            Log::error('Package GetByType Error:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $request->user()->id,
                'type' => $type
            ]);
            return response()->json(['error' => 'Failed to fetch packages by type'], 500);
        }
    }

    /**
     * Get packages by billing cycle.
     */
    public function getByBillingCycle(Request $request, $billingCycle)
    {
        Log::info('Package GetByBillingCycle Request:', [
            'user_id' => $request->user()->id,
            'billing_cycle' => $billingCycle,
            'method' => $request->method(),
            'url' => $request->fullUrl()
        ]);

        try {
            $user = $request->user();
            $profile = $user->profile;

            Log::info('User Profile Check for GetByBillingCycle:', [
                'user_id' => $user->id,
                'has_profile' => !is_null($profile),
                'profile_id' => $profile ? $profile->id : null
            ]);

            if (!$profile) {
                Log::warning('Package GetByBillingCycle - Profile Not Found:', [
                    'user_id' => $user->id
                ]);
                return response()->json(['error' => 'Profile not found'], 404);
            }

            $packages = Package::where('profile_id', $profile->id)
                              ->byBillingCycle($billingCycle)
                              ->active()
                              ->with('features')
                              ->orderBy('created_at', 'desc')
                              ->get();

            // Transform features to array
            $packages->transform(function ($package) {
                $package->features = $package->features->pluck('feature')->toArray();
                return $package;
            });

            Log::info('Package GetByBillingCycle Success:', [
                'profile_id' => $profile->id,
                'billing_cycle' => $billingCycle,
                'packages_count' => $packages->count()
            ]);

            return response()->json(['data' => $packages]);
        } catch (\Exception $e) {
            Log::error('Package GetByBillingCycle Error:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $request->user()->id,
                'billing_cycle' => $billingCycle
            ]);
            return response()->json(['error' => 'Failed to fetch packages by billing cycle'], 500);
        }
    }
}
