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

    /**
     * Connect a client to the logged-in user.
     */
    public function connectClient($clientId)
    {
        try {
            $userId = Auth::id();
            $client = Client::findOrFail($clientId);

            Log::info('Connecting user to existing client', [
                'user_id' => $userId,
                'client_id' => $clientId
            ]);

            // Check if the relationship already exists
            $existingRelationship = $client->users()->where('user_id', $userId)->exists();

            if (!$existingRelationship) {
                $client->users()->attach($userId);
                Log::info('User connected to existing client successfully', [
                    'user_id' => $userId,
                    'client_id' => $clientId
                ]);
                return response()->json([
                    'message' => 'Client connected successfully',
                    'client' => $client->load('users')
                ]);
            } else {
                Log::info('User-client relationship already exists', [
                    'user_id' => $userId,
                    'client_id' => $clientId
                ]);
                return response()->json([
                    'message' => 'Client already connected',
                    'client' => $client->load('users')
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error connecting client to user', [
                'user_id' => $userId ?? null,
                'client_id' => $clientId,
                'error' => $e->getMessage()
            ]);
            return response()->json(['message' => 'Error connecting client'], 500);
        }
    }

    /**
     * Disconnect a client from the logged-in user.
     */
    public function disconnectClient($clientId)
    {
        try {
            $userId = Auth::id();
            $client = Client::findOrFail($clientId);

            Log::info('Disconnecting user from client', [
                'user_id' => $userId,
                'client_id' => $clientId
            ]);

            $client->users()->detach($userId);

            Log::info('User disconnected from client successfully', [
                'user_id' => $userId,
                'client_id' => $clientId
            ]);

            return response()->json([
                'message' => 'Client disconnected successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error disconnecting client from user', [
                'user_id' => $userId ?? null,
                'client_id' => $clientId,
                'error' => $e->getMessage()
            ]);
            return response()->json(['message' => 'Error disconnecting client'], 500);
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

            // Connect the logged-in user to the client via pivot table
            $userId = Auth::id();
            Log::info('Connecting user to client', [
                'user_id' => $userId,
                'client_id' => $client->id
            ]);

            // Check if the relationship already exists
            $existingRelationship = $client->users()->where('user_id', $userId)->exists();

            if (!$existingRelationship) {
                $client->users()->attach($userId);
                Log::info('User connected to client successfully', [
                    'user_id' => $userId,
                    'client_id' => $client->id
                ]);
            } else {
                Log::info('User-client relationship already exists, skipping', [
                    'user_id' => $userId,
                    'client_id' => $client->id
                ]);
            }

            // Load the users relationship for the response
            $client->load('users');

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
            $client = Client::with(['intakes', 'proposals.intakeResponse.intake'])->findOrFail($id);
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

            // Get clients directly connected to the user via pivot table
            $clients = Client::whereHas('users', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->with(['users', 'intakes' => function ($query) use ($userId) {
                $query->where('user_id', $userId)
                    ->with('response');
            }])
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
