<?php

namespace App\Http\Controllers\Api;

use App\Models\Provider;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProviderController extends Controller
{
    // GET /api/providers/{id}
    public function show($id)
    {
        $provider = Provider::with(['providerType', 'user'])->find($id);

        if (!$provider) {
            return response()->json(['message' => 'Provider not found'], 404);
        }

        return response()->json($provider);
    }

    // PUT /api/providers/{id}
    public function update(Request $request, $id)
    {
        $provider = Provider::find($id);

        if (!$provider) {
            return response()->json(['message' => 'Provider not found'], 404);
        }

        $validated = $request->validate([
            'provider_type_id' => 'sometimes|exists:provider_types,id',
            'user_id' => 'sometimes|exists:users,id',
            'name' => 'sometimes|string',
            'email' => 'sometimes|email',
            'phone' => 'nullable|string',
            'website' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $provider->update($validated);

        return response()->json($provider);
    }

    // GET /api/providers/{id}/dashboard
    public function dashboard($id)
    {
        $provider = Provider::withCount([
            'clients',
            'projects',
            'tasks',
            'subtasks'
        ])->find($id);

        if (!$provider) {
            return response()->json(['message' => 'Provider not found'], 404);
        }

        return response()->json([
            'provider_id' => $provider->id,
            'provider_name' => $provider->name,
            'totals' => [
                'clients' => $provider->clients_count,
                'projects' => $provider->projects_count,
                'tasks' => $provider->tasks_count,
                'subtasks' => $provider->subtasks_count,
            ],
        ]);
    }
}
