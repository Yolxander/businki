<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class ServiceController extends Controller
{
    /**
     * Display a listing of the services.
     */
    public function index(Request $request)
    {
        Log::info('Service Index Request:', [
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
                Log::warning('Service Index - Profile Not Found:', [
                    'user_id' => $user->id
                ]);
                return response()->json(['error' => 'Profile not found'], 404);
            }

            $query = Service::where('profile_id', $profile->id);

            // Apply search filter
            if ($request->has('search') && !empty($request->search)) {
                Log::info('Applying Search Filter:', ['search_term' => $request->search]);
                $query->search($request->search);
            }

            // Apply category filter
            if ($request->has('category') && !empty($request->category)) {
                Log::info('Applying Category Filter:', ['category' => $request->category]);
                $query->byCategory($request->category);
            }

            // Apply pricing type filter
            if ($request->has('pricing_type') && !empty($request->pricing_type)) {
                Log::info('Applying Pricing Type Filter:', ['pricing_type' => $request->pricing_type]);
                $query->byPricingType($request->pricing_type);
            }

            // Apply active filter
            if ($request->has('is_active')) {
                Log::info('Applying Active Filter:', ['is_active' => $request->boolean('is_active')]);
                $query->where('is_active', $request->boolean('is_active'));
            }

            $services = $query->orderBy('created_at', 'desc')->paginate(15);

            Log::info('Service Index Success:', [
                'profile_id' => $profile->id,
                'total_services' => $services->total(),
                'current_page' => $services->currentPage(),
                'per_page' => $services->perPage(),
                'services_count' => count($services->items())
            ]);

            return response()->json([
                'data' => $services->items(),
                'pagination' => [
                    'current_page' => $services->currentPage(),
                    'last_page' => $services->lastPage(),
                    'per_page' => $services->perPage(),
                    'total' => $services->total(),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Service Index Error:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $request->user()->id,
                'request_data' => $request->all()
            ]);
            return response()->json(['error' => 'Failed to fetch services'], 500);
        }
    }

    /**
     * Store a newly created service in storage.
     */
    public function store(Request $request)
    {
        Log::info('Service Store Request:', [
            'user_id' => $request->user()->id,
            'request_data' => $request->all(),
            'method' => $request->method(),
            'url' => $request->fullUrl()
        ]);

        try {
            $validator = Validator::make($request->all(), Service::rules());

            if ($validator->fails()) {
                Log::warning('Service Store Validation Failed:', [
                    'user_id' => $request->user()->id,
                    'errors' => $validator->errors()->toArray(),
                    'request_data' => $request->all()
                ]);
                return response()->json(['errors' => $validator->errors()], 422);
            }

            Log::info('Service Store Validation Passed:', [
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
                Log::warning('Service Store - Profile Not Found:', [
                    'user_id' => $user->id
                ]);
                return response()->json(['error' => 'Profile not found'], 404);
            }

            $serviceData = [
                'profile_id' => $profile->id,
                'name' => $request->name,
                'description' => $request->description,
                'category' => $request->category,
                'pricing_type' => $request->pricing_type,
                'hourly_rate' => $request->hourly_rate,
                'one_time_price' => $request->one_time_price,
                'project_price' => $request->project_price,
                'monthly_price' => $request->monthly_price,
                'duration' => $request->duration,
                'is_active' => $request->get('is_active', true),
            ];

            Log::info('Creating Service:', [
                'profile_id' => $profile->id,
                'service_data' => $serviceData
            ]);

            $service = Service::create($serviceData);

            Log::info('Service Created Successfully:', [
                'service_id' => $service->id,
                'profile_id' => $profile->id,
                'service_data' => $service->toArray()
            ]);

            return response()->json([
                'message' => 'Service created successfully',
                'data' => $service
            ], 201);
        } catch (\Exception $e) {
            Log::error('Service Creation Error:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $request->user()->id,
                'request_data' => $request->all()
            ]);
            return response()->json(['error' => 'Failed to create service'], 500);
        }
    }

    /**
     * Display the specified service.
     */
    public function show(Request $request, $id)
    {
        Log::info('Service Show Request:', [
            'user_id' => $request->user()->id,
            'service_id' => $id,
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
                Log::warning('Service Show - Profile Not Found:', [
                    'user_id' => $user->id
                ]);
                return response()->json(['error' => 'Profile not found'], 404);
            }

            $service = Service::where('profile_id', $profile->id)
                             ->where('id', $id)
                             ->first();

            if (!$service) {
                Log::warning('Service Show - Service Not Found:', [
                    'user_id' => $user->id,
                    'profile_id' => $profile->id,
                    'service_id' => $id
                ]);
                return response()->json(['error' => 'Service not found'], 404);
            }

            Log::info('Service Show Success:', [
                'service_id' => $service->id,
                'profile_id' => $profile->id,
                'service_data' => $service->toArray()
            ]);

            return response()->json(['data' => $service]);
        } catch (\Exception $e) {
            Log::error('Service Show Error:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $request->user()->id,
                'service_id' => $id
            ]);
            return response()->json(['error' => 'Failed to fetch service'], 500);
        }
    }

    /**
     * Update the specified service in storage.
     */
    public function update(Request $request, $id)
    {
        Log::info('Service Update Request:', [
            'user_id' => $request->user()->id,
            'service_id' => $id,
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
                Log::warning('Service Update - Profile Not Found:', [
                    'user_id' => $user->id
                ]);
                return response()->json(['error' => 'Profile not found'], 404);
            }

            $service = Service::where('profile_id', $profile->id)
                             ->where('id', $id)
                             ->first();

            if (!$service) {
                Log::warning('Service Update - Service Not Found:', [
                    'user_id' => $user->id,
                    'profile_id' => $profile->id,
                    'service_id' => $id
                ]);
                return response()->json(['error' => 'Service not found'], 404);
            }

            Log::info('Service Found for Update:', [
                'service_id' => $service->id,
                'current_data' => $service->toArray()
            ]);

            $validator = Validator::make($request->all(), Service::updateRules());

            if ($validator->fails()) {
                Log::warning('Service Update Validation Failed:', [
                    'service_id' => $service->id,
                    'user_id' => $user->id,
                    'errors' => $validator->errors()->toArray(),
                    'request_data' => $request->all()
                ]);
                return response()->json(['errors' => $validator->errors()], 422);
            }

            Log::info('Service Update Validation Passed:', [
                'service_id' => $service->id,
                'validated_data' => $validator->validated()
            ]);

            $updateData = $request->only([
                'name', 'description', 'category', 'pricing_type',
                'hourly_rate', 'one_time_price', 'project_price', 'monthly_price',
                'duration', 'is_active'
            ]);

            Log::info('Updating Service:', [
                'service_id' => $service->id,
                'update_data' => $updateData
            ]);

            $service->update($updateData);

            Log::info('Service Updated Successfully:', [
                'service_id' => $service->id,
                'profile_id' => $profile->id,
                'updated_data' => $service->fresh()->toArray()
            ]);

            return response()->json([
                'message' => 'Service updated successfully',
                'data' => $service->fresh()
            ]);
        } catch (\Exception $e) {
            Log::error('Service Update Error:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $request->user()->id,
                'service_id' => $id,
                'request_data' => $request->all()
            ]);
            return response()->json(['error' => 'Failed to update service'], 500);
        }
    }

    /**
     * Remove the specified service from storage.
     */
    public function destroy(Request $request, $id)
    {
        Log::info('Service Destroy Request:', [
            'user_id' => $request->user()->id,
            'service_id' => $id,
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
                Log::warning('Service Destroy - Profile Not Found:', [
                    'user_id' => $user->id
                ]);
                return response()->json(['error' => 'Profile not found'], 404);
            }

            $service = Service::where('profile_id', $profile->id)
                             ->where('id', $id)
                             ->first();

            if (!$service) {
                Log::warning('Service Destroy - Service Not Found:', [
                    'user_id' => $user->id,
                    'profile_id' => $profile->id,
                    'service_id' => $id
                ]);
                return response()->json(['error' => 'Service not found'], 404);
            }

            Log::info('Service Found for Destroy:', [
                'service_id' => $service->id,
                'current_data' => $service->toArray()
            ]);

            // Soft delete by setting is_active to false
            $service->update(['is_active' => false]);

            Log::info('Service Deleted Successfully:', [
                'service_id' => $service->id,
                'profile_id' => $profile->id
            ]);

            return response()->json([
                'message' => 'Service deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Service Delete Error:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $request->user()->id,
                'service_id' => $id
            ]);
            return response()->json(['error' => 'Failed to delete service'], 500);
        }
    }

    /**
     * Get services by category.
     */
    public function getByCategory(Request $request, $category)
    {
        Log::info('Service GetByCategory Request:', [
            'user_id' => $request->user()->id,
            'category' => $category,
            'method' => $request->method(),
            'url' => $request->fullUrl()
        ]);

        try {
            $user = $request->user();
            $profile = $user->profile;

            Log::info('User Profile Check for GetByCategory:', [
                'user_id' => $user->id,
                'has_profile' => !is_null($profile),
                'profile_id' => $profile ? $profile->id : null
            ]);

            if (!$profile) {
                Log::warning('Service GetByCategory - Profile Not Found:', [
                    'user_id' => $user->id
                ]);
                return response()->json(['error' => 'Profile not found'], 404);
            }

            $services = Service::where('profile_id', $profile->id)
                              ->byCategory($category)
                              ->active()
                              ->orderBy('created_at', 'desc')
                              ->get();

            Log::info('Service GetByCategory Success:', [
                'profile_id' => $profile->id,
                'category' => $category,
                'services_count' => $services->count()
            ]);

            return response()->json(['data' => $services]);
        } catch (\Exception $e) {
            Log::error('Service GetByCategory Error:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $request->user()->id,
                'category' => $category
            ]);
            return response()->json(['error' => 'Failed to fetch services by category'], 500);
        }
    }

    /**
     * Get services by pricing type.
     */
    public function getByPricingType(Request $request, $pricingType)
    {
        Log::info('Service GetByPricingType Request:', [
            'user_id' => $request->user()->id,
            'pricing_type' => $pricingType,
            'method' => $request->method(),
            'url' => $request->fullUrl()
        ]);

        try {
            $user = $request->user();
            $profile = $user->profile;

            Log::info('User Profile Check for GetByPricingType:', [
                'user_id' => $user->id,
                'has_profile' => !is_null($profile),
                'profile_id' => $profile ? $profile->id : null
            ]);

            if (!$profile) {
                Log::warning('Service GetByPricingType - Profile Not Found:', [
                    'user_id' => $user->id
                ]);
                return response()->json(['error' => 'Profile not found'], 404);
            }

            $services = Service::where('profile_id', $profile->id)
                              ->byPricingType($pricingType)
                              ->active()
                              ->orderBy('created_at', 'desc')
                              ->get();

            Log::info('Service GetByPricingType Success:', [
                'profile_id' => $profile->id,
                'pricing_type' => $pricingType,
                'services_count' => $services->count()
            ]);

            return response()->json(['data' => $services]);
        } catch (\Exception $e) {
            Log::error('Service GetByPricingType Error:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $request->user()->id,
                'pricing_type' => $pricingType
            ]);
            return response()->json(['error' => 'Failed to fetch services by pricing type'], 500);
        }
    }
}
