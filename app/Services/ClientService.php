<?php

namespace App\Services;

use App\Models\Client;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;

class ClientService
{
    /**
     * Create a new client
     */
    public function createClient(array $data): array
    {
        try {
            // Validate required fields
            $requiredFields = ['first_name', 'last_name', 'email'];
            $missingFields = [];

            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    $missingFields[] = $field;
                }
            }

            if (!empty($missingFields)) {
                $fieldNames = [
                    'first_name' => 'first name',
                    'last_name' => 'last name',
                    'email' => 'email address'
                ];

                $missingFieldNames = array_map(function($field) use ($fieldNames) {
                    return $fieldNames[$field] ?? $field;
                }, $missingFields);

                $message = "I need a few more details to create the client. ";
                if (count($missingFieldNames) === 1) {
                    $message .= "Please provide the " . $missingFieldNames[0] . ".";
                } else {
                    $message .= "Please provide: " . implode(', ', $missingFieldNames) . ".";
                }

                return [
                    'success' => false,
                    'message' => $message,
                    'data' => null,
                    'missing_fields' => $missingFields,
                    'current_field' => $missingFields[0], // Set the first missing field as current
                    'requires_interaction' => true
                ];
            }

            // Check if client already exists
            $existingClient = Client::where('email', $data['email'])->first();
            if ($existingClient) {
                return [
                    'success' => false,
                    'message' => "A client with email {$data['email']} already exists.",
                    'data' => $existingClient
                ];
            }

            // Create client
            $client = Client::create($data);

            // Associate with current user
            $user = Auth::user();
            if ($user) {
                $client->users()->attach($user->id);
            }

            return [
                'success' => true,
                'message' => "Client {$client->full_name} created successfully.",
                'data' => $client
            ];

        } catch (Exception $e) {
            Log::error('Client creation failed', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);

            return [
                'success' => false,
                'message' => 'Failed to create client. Please try again.',
                'data' => null
            ];
        }
    }

    /**
     * Get client by ID or search criteria
     */
    public function getClient($identifier): array
    {
        try {
            $client = null;

            // Try to find by ID first
            if (is_numeric($identifier)) {
                $client = Client::find($identifier);
            }

            // If not found by ID, search by name or email
            if (!$client) {
                $client = Client::where('email', $identifier)
                    ->orWhere('first_name', 'LIKE', "%{$identifier}%")
                    ->orWhere('last_name', 'LIKE', "%{$identifier}%")
                    ->orWhere('company_name', 'LIKE', "%{$identifier}%")
                    ->first();
            }

            if (!$client) {
                return [
                    'success' => false,
                    'message' => "Client not found with identifier: {$identifier}",
                    'data' => null
                ];
            }

            return [
                'success' => true,
                'message' => "Found client: {$client->full_name}",
                'data' => $client
            ];

        } catch (Exception $e) {
            Log::error('Client retrieval failed', [
                'error' => $e->getMessage(),
                'identifier' => $identifier
            ]);

            return [
                'success' => false,
                'message' => 'Failed to retrieve client. Please try again.',
                'data' => null
            ];
        }
    }

    /**
     * Update client information
     */
    public function updateClient($identifier, array $data): array
    {
        try {
            // Get client first
            $clientResult = $this->getClient($identifier);
            if (!$clientResult['success']) {
                return $clientResult;
            }

            $client = $clientResult['data'];

            // Update client
            $client->update($data);

            return [
                'success' => true,
                'message' => "Client {$client->full_name} updated successfully.",
                'data' => $client
            ];

        } catch (Exception $e) {
            Log::error('Client update failed', [
                'error' => $e->getMessage(),
                'identifier' => $identifier,
                'data' => $data
            ]);

            return [
                'success' => false,
                'message' => 'Failed to update client. Please try again.',
                'data' => null
            ];
        }
    }

    /**
     * Delete client
     */
    public function deleteClient($identifier): array
    {
        try {
            // Get client first
            $clientResult = $this->getClient($identifier);
            if (!$clientResult['success']) {
                return $clientResult;
            }

            $client = $clientResult['data'];
            $clientName = $client->full_name;

            // Delete client
            $client->delete();

            return [
                'success' => true,
                'message' => "Client {$clientName} deleted successfully.",
                'data' => null
            ];

        } catch (Exception $e) {
            Log::error('Client deletion failed', [
                'error' => $e->getMessage(),
                'identifier' => $identifier
            ]);

            return [
                'success' => false,
                'message' => 'Failed to delete client. Please try again.',
                'data' => null
            ];
        }
    }

    /**
     * List all clients with optional filters
     */
    public function listClients(array $filters = []): array
    {
        try {
            $query = Client::query();

            // Apply filters
            if (!empty($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            if (!empty($filters['industry'])) {
                $query->where('industry', $filters['industry']);
            }

            if (!empty($filters['search'])) {
                $search = $filters['search'];
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'LIKE', "%{$search}%")
                      ->orWhere('last_name', 'LIKE', "%{$search}%")
                      ->orWhere('company_name', 'LIKE', "%{$search}%")
                      ->orWhere('email', 'LIKE', "%{$search}%");
                });
            }

            // Get clients
            $clients = $query->orderBy('created_at', 'desc')->get();

            return [
                'success' => true,
                'message' => "Found " . $clients->count() . " clients.",
                'data' => $clients
            ];

        } catch (Exception $e) {
            Log::error('Client listing failed', [
                'error' => $e->getMessage(),
                'filters' => $filters
            ]);

            return [
                'success' => false,
                'message' => 'Failed to list clients. Please try again.',
                'data' => null
            ];
        }
    }

    /**
     * Get client statistics
     */
    public function getClientStats(): array
    {
        try {
            $totalClients = Client::count();
            $activeClients = Client::where('status', 'active')->count();
            $recentClients = Client::where('created_at', '>=', now()->subDays(30))->count();

            return [
                'success' => true,
                'message' => "Client statistics retrieved successfully.",
                'data' => [
                    'total_clients' => $totalClients,
                    'active_clients' => $activeClients,
                    'recent_clients' => $recentClients
                ]
            ];

        } catch (Exception $e) {
            Log::error('Client statistics failed', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to retrieve client statistics.',
                'data' => null
            ];
        }
    }
}
