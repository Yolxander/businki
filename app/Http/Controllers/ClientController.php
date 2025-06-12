<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class ClientController extends Controller
{
    public function index()
    {
        try {
            Log::info('Fetching all clients');
            $clients = Client::with('intakes')->get();
            return response()->json($clients);
        } catch (\Exception $e) {
            Log::error('Error fetching clients', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['message' => 'Error fetching clients'], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            Log::info('Creating new client', ['request_data' => $request->all()]);

            $validator = Validator::make($request->all(), [
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|email|unique:clients',
                'phone' => 'nullable|string|max:20',
                'company_name' => 'nullable|string|max:255',
                'address' => 'nullable|string|max:255',
                'city' => 'nullable|string|max:255',
                'state' => 'nullable|string|max:255',
                'zip_code' => 'nullable|string|max:20',
            ]);

            if ($validator->fails()) {
                Log::warning('Client validation failed', ['errors' => $validator->errors()->toArray()]);
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $client = Client::create($request->all());
            Log::info('Client created successfully', ['client_id' => $client->id]);
            return response()->json($client, 201);
        } catch (\Exception $e) {
            Log::error('Failed to create client', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['message' => 'Failed to create client', 'error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            Log::info('Fetching client details', ['client_id' => $id]);
            $client = Client::with('intakes')->findOrFail($id);
            return response()->json($client);
        } catch (\Exception $e) {
            Log::warning('Client not found', ['client_id' => $id]);
            return response()->json(['message' => 'Client not found'], 404);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            Log::info('Updating client', ['client_id' => $id, 'request_data' => $request->all()]);
            $client = Client::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'first_name' => 'sometimes|required|string|max:255',
                'last_name' => 'sometimes|required|string|max:255',
                'email' => 'sometimes|required|email|unique:clients,email,' . $id,
                'phone' => 'nullable|string|max:20',
                'company_name' => 'nullable|string|max:255',
                'address' => 'nullable|string|max:255',
                'city' => 'nullable|string|max:255',
                'state' => 'nullable|string|max:255',
                'zip_code' => 'nullable|string|max:20',
            ]);

            if ($validator->fails()) {
                Log::warning('Client update validation failed', [
                    'client_id' => $id,
                    'errors' => $validator->errors()->toArray()
                ]);
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $client->update($request->all());
            Log::info('Client updated successfully', ['client_id' => $client->id]);
            return response()->json($client);
        } catch (\Exception $e) {
            Log::error('Error updating client', [
                'client_id' => $id,
                'error' => $e->getMessage()
            ]);
            return response()->json(['message' => 'Error updating client'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            Log::info('Deleting client', ['client_id' => $id]);
            $client = Client::findOrFail($id);
            $client->delete();
            Log::info('Client deleted successfully', ['client_id' => $id]);
            return response()->json(null, 204);
        } catch (\Exception $e) {
            Log::error('Error deleting client', [
                'client_id' => $id,
                'error' => $e->getMessage()
            ]);
            return response()->json(['message' => 'Error deleting client'], 500);
        }
    }

    public function getClientsByUser()
    {
        try {
            $userId = Auth::id();
            Log::info('Fetching clients for user', ['user_id' => $userId]);

            $clients = Client::whereHas('intakes', function ($query) use ($userId) {
                $query->where('user_id', $userId)
                    ->whereHas('response');
            })
            ->with(['intakes' => function ($query) use ($userId) {
                $query->where('user_id', $userId)
                    ->with('response');
            }])
            ->distinct()
            ->get();

            Log::info('Successfully fetched clients for user', [
                'user_id' => $userId,
                'client_count' => $clients->count()
            ]);

            return response()->json($clients);
        } catch (\Exception $e) {
            Log::error('Error fetching clients for user', [
                'user_id' => $userId ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['message' => 'Error fetching clients'], 500);
        }
    }
}
