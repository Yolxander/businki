<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class SubscriptionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        try {
            $subscriptions = Subscription::with('client')
                ->where('user_id', Auth::id())
                ->get();

            Log::info('Subscriptions retrieved successfully', [
                'user_id' => Auth::id(),
                'count' => $subscriptions->count()
            ]);

            return response()->json([
                'status' => 'success',
                'data' => $subscriptions
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve subscriptions', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve subscriptions'
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            Log::info('Creating new subscription', [
                'user_id' => Auth::id(),
                'request_data' => $request->except(['description'])
            ]);

            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'client_id' => 'required|exists:clients,id',
                'service_type' => 'required|string|max:255',
                'billing_cycle' => 'required|in:monthly,quarterly,yearly',
                'amount' => 'required|numeric|min:0',
                'description' => 'nullable|string',
                'start_date' => 'required|date',
                'end_date' => 'nullable|date|after:start_date',
                'status' => 'required|in:active,paused,cancelled,expired'
            ]);

            $validated['user_id'] = Auth::id();
            $validated['next_billing'] = $validated['start_date'];
            $validated['total_billed'] = 0;
            $validated['payments_received'] = 0;
            $validated['billing_history'] = [];

            $subscription = Subscription::create($validated);

            Log::info('Subscription created successfully', [
                'user_id' => Auth::id(),
                'subscription_id' => $subscription->id,
                'client_id' => $subscription->client_id
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Subscription created successfully',
                'data' => $subscription->load('client')
            ], 201);
        } catch (\Exception $e) {
            Log::error('Failed to create subscription', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'request_data' => $request->except(['description'])
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create subscription',
                'errors' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Subscription $subscription): JsonResponse
    {
        try {
            // Ensure user can only access their own subscriptions
            if ($subscription->user_id !== Auth::id()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized access'
                ], 403);
            }

            Log::info('Subscription viewed', [
                'user_id' => Auth::id(),
                'subscription_id' => $subscription->id
            ]);

            return response()->json([
                'status' => 'success',
                'data' => $subscription->load('client')
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to view subscription', [
                'user_id' => Auth::id(),
                'subscription_id' => $subscription->id,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to view subscription'
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Subscription $subscription): JsonResponse
    {
        try {
            // Ensure user can only update their own subscriptions
            if ($subscription->user_id !== Auth::id()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized access'
                ], 403);
            }

            Log::info('Updating subscription', [
                'user_id' => Auth::id(),
                'subscription_id' => $subscription->id,
                'request_data' => $request->except(['description'])
            ]);

            $validated = $request->validate([
                'title' => 'sometimes|required|string|max:255',
                'client_id' => 'sometimes|required|exists:clients,id',
                'service_type' => 'sometimes|required|string|max:255',
                'billing_cycle' => 'sometimes|required|in:monthly,quarterly,yearly',
                'amount' => 'sometimes|required|numeric|min:0',
                'description' => 'nullable|string',
                'start_date' => 'sometimes|required|date',
                'end_date' => 'nullable|date|after:start_date',
                'status' => 'sometimes|required|in:active,paused,cancelled,expired',
                'next_billing' => 'nullable|date',
                'total_billed' => 'sometimes|required|numeric|min:0',
                'payments_received' => 'sometimes|required|integer|min:0'
            ]);

            $subscription->update($validated);

            Log::info('Subscription updated successfully', [
                'user_id' => Auth::id(),
                'subscription_id' => $subscription->id
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Subscription updated successfully',
                'data' => $subscription->load('client')
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update subscription', [
                'user_id' => Auth::id(),
                'subscription_id' => $subscription->id,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update subscription',
                'errors' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Subscription $subscription): JsonResponse
    {
        try {
            // Ensure user can only delete their own subscriptions
            if ($subscription->user_id !== Auth::id()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized access'
                ], 403);
            }

            Log::info('Deleting subscription', [
                'user_id' => Auth::id(),
                'subscription_id' => $subscription->id
            ]);

            $subscription->delete();

            Log::info('Subscription deleted successfully', [
                'user_id' => Auth::id(),
                'subscription_id' => $subscription->id
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Subscription deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to delete subscription', [
                'user_id' => Auth::id(),
                'subscription_id' => $subscription->id,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete subscription'
            ], 500);
        }
    }

    /**
     * Get subscription statistics for the authenticated user.
     */
    public function stats(): JsonResponse
    {
        try {
            $userId = Auth::id();

            $totalRevenue = Subscription::where('user_id', $userId)->sum('total_billed');
            $activeSubscriptions = Subscription::where('user_id', $userId)->where('status', 'active')->count();
            $totalSubscriptions = Subscription::where('user_id', $userId)->count();
            $dueThisMonth = Subscription::where('user_id', $userId)
                ->where('status', 'active')
                ->where('next_billing', '<=', now()->endOfMonth())
                ->count();

            Log::info('Subscription stats retrieved', [
                'user_id' => $userId,
                'total_revenue' => $totalRevenue,
                'active_subscriptions' => $activeSubscriptions,
                'total_subscriptions' => $totalSubscriptions,
                'due_this_month' => $dueThisMonth
            ]);

            return response()->json([
                'status' => 'success',
                'data' => [
                    'total_revenue' => $totalRevenue,
                    'active_subscriptions' => $activeSubscriptions,
                    'total_subscriptions' => $totalSubscriptions,
                    'due_this_month' => $dueThisMonth
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve subscription stats', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve subscription statistics'
            ], 500);
        }
    }

    /**
     * Get clients for subscription creation.
     */
    public function getClients(): JsonResponse
    {
        try {
            $clients = Client::whereHas('users', function ($query) {
                $query->where('user_id', Auth::id());
            })->get(['id', 'first_name', 'last_name', 'company_name']);

            return response()->json([
                'status' => 'success',
                'data' => $clients
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve clients for subscription', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve clients'
            ], 500);
        }
    }
}
