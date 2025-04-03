<?php

namespace App\Http\Controllers\Api;

use App\Models\Client;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ClientController extends Controller
{
    // GET /api/clients?provider_id=1
    public function index(Request $request)
    {
        $providerId = $request->query('provider_id');

        if (!$providerId) {
            return response()->json(['message' => 'provider_id is required'], 400);
        }

        $clients = Client::where('provider_id', $providerId)->get();

        return response()->json($clients);
    }

    // GET /api/clients/{id}
    public function show($id)
    {
        $client = Client::find($id);

        if (!$client) {
            return response()->json(['message' => 'Client not found'], 404);
        }

        return response()->json($client);
    }

    // POST /api/clients
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'description' => 'nullable|string',
            'provider_id' => 'required|exists:providers,id',
            'user_id' => 'nullable|exists:users,id',
        ]);

        $client = Client::create($validated);

        return response()->json($client, 201);
    }

    // PUT /api/clients/{id}
    public function update(Request $request, $id)
    {
        $client = Client::find($id);

        if (!$client) {
            return response()->json(['message' => 'Client not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'description' => 'nullable|string',
            'provider_id' => 'sometimes|exists:providers,id',
            'user_id' => 'nullable|exists:users,id',
        ]);

        $client->update($validated);

        return response()->json($client);
    }

    // DELETE /api/clients/{id}
    public function destroy($id)
    {
        $client = Client::find($id);

        if (!$client) {
            return response()->json(['message' => 'Client not found'], 404);
        }

        $client->delete();

        return response()->json(['message' => 'Client deleted successfully']);
    }
}
