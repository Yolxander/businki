<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class ListingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $listings = Listing::all();
        return response()->json([
            'success' => true,
            'data' => $listings
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'slug' => 'required|string|unique:listings,slug|max:255',
                'title' => 'required|string|max:255',
                'industry' => 'required|string|max:255',
                'type' => 'required|string|in:website,tool,software',
                'featured' => 'boolean',
                'image' => 'required|string',
                'description' => 'required|string',
                'frames' => 'required|array',
                'frames.*' => 'string',
                'features' => 'required|array',
                'features.*' => 'string',
                'services' => 'required|array',
                'services.*' => 'string',
                'price' => 'required|string|max:255',
                'demo' => 'string|max:255'
            ]);

            $listing = Listing::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Listing created successfully',
                'data' => $listing
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create listing',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $listing = Listing::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $listing
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Listing not found'
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $listing = Listing::findOrFail($id);

            $validated = $request->validate([
                'slug' => 'sometimes|string|unique:listings,slug,' . $id . '|max:255',
                'title' => 'sometimes|string|max:255',
                'industry' => 'sometimes|string|max:255',
                'type' => 'sometimes|string|in:website,tool,software',
                'featured' => 'sometimes|boolean',
                'image' => 'sometimes|string',
                'description' => 'sometimes|string',
                'frames' => 'sometimes|array',
                'frames.*' => 'string',
                'features' => 'sometimes|array',
                'features.*' => 'string',
                'services' => 'sometimes|array',
                'services.*' => 'string',
                'price' => 'sometimes|string|max:255',
                'demo' => 'sometimes|string|max:255'
            ]);

            $listing->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Listing updated successfully',
                'data' => $listing
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update listing',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $listing = Listing::findOrFail($id);
            $listing->delete();

            return response()->json([
                'success' => true,
                'message' => 'Listing deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete listing',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
