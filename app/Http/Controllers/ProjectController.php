<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Client;
use App\Models\IntakeResponse;
use App\Models\Proposal;
use App\Models\Intake;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        try {
            $projects = Project::with('proposal','tasks.subtasks')->get();
            Log::info('Projects retrieved successfully', [
                'user_id' => auth()->id(),
                'count' => $projects->count()
            ]);
            return response()->json($projects);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve projects', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            return response()->json(['error' => 'Failed to retrieve projects'], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            Log::info('Creating new project', [
                'user_id' => auth()->id(),
                'request_data' => $request->except(['notes'])
            ]);

            $validated = $request->validate([
                'proposal_id' => 'required|exists:proposals,id',
                'title' => 'required|string',
                'status' => 'required|string|in:not_started,in_progress,paused,done',
                'current_phase' => 'required|string',
                'kickoff_date' => 'required|date',
                'expected_delivery' => 'required|date',
                'notes' => 'nullable|string'
            ]);

            $project = Project::create($validated);

            Log::info('Project created successfully', [
                'user_id' => auth()->id(),
                'project_id' => $project->id,
                'proposal_id' => $project->proposal_id
            ]);

            return response()->json($project, 201);
        } catch (\Exception $e) {
            Log::error('Failed to create project', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'request_data' => $request->except(['notes'])
            ]);
            return response()->json(['error' => 'Failed to create project'], 422);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project): JsonResponse
    {
        try {
            Log::info('Project viewed', [
                'user_id' => auth()->id(),
                'project_id' => $project->id
            ]);
            return response()->json($project);
        } catch (\Exception $e) {
            Log::error('Failed to view project', [
                'user_id' => auth()->id(),
                'project_id' => $project->id,
                'error' => $e->getMessage()
            ]);
            return response()->json(['error' => 'Failed to view project'], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project): JsonResponse
    {
        try {
            Log::info('Updating project', [
                'user_id' => auth()->id(),
                'project_id' => $project->id,
                'request_data' => $request->except(['notes'])
            ]);

            $validated = $request->validate([
                'proposal_id' => 'sometimes|required|exists:proposals,id',
                'title' => 'sometimes|required|string',
                'status' => 'sometimes|required|string|in:not_started,in_progress,paused,done',
                'current_phase' => 'sometimes|required|string',
                'kickoff_date' => 'sometimes|required|date',
                'expected_delivery' => 'sometimes|required|date',
                'notes' => 'nullable|string'
            ]);

            $project->update($validated);

            Log::info('Project updated successfully', [
                'user_id' => auth()->id(),
                'project_id' => $project->id,
                'updated_fields' => array_keys($validated)
            ]);

            return response()->json($project);
        } catch (\Exception $e) {
            Log::error('Failed to update project', [
                'user_id' => auth()->id(),
                'project_id' => $project->id,
                'error' => $e->getMessage()
            ]);
            return response()->json(['error' => 'Failed to update project'], 422);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project): JsonResponse
    {
        try {
            Log::info('Deleting project', [
                'user_id' => auth()->id(),
                'project_id' => $project->id
            ]);

            $project->delete();

            Log::info('Project deleted successfully', [
                'user_id' => auth()->id(),
                'project_id' => $project->id
            ]);

            return response()->json(null, 204);
        } catch (\Exception $e) {
            Log::error('Failed to delete project', [
                'user_id' => auth()->id(),
                'project_id' => $project->id,
                'error' => $e->getMessage()
            ]);
            return response()->json(['error' => 'Failed to delete project'], 500);
        }
    }

        /**
     * Create a new client, proposal, and project in one transaction.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function newClientProject(Request $request): JsonResponse
    {
        $requestId = uniqid('new_client_project_');

        try {
            Log::info("[$requestId] Starting new client project creation", [
                'user_id' => auth()->id(),
                'user_email' => auth()->user()?->email,
                'request_method' => $request->method(),
                'request_url' => $request->fullUrl(),
                'request_headers' => $request->headers->all(),
                'request_data' => $request->except(['client.phone', 'client.address', 'client.city', 'client.state', 'client.zip_code', 'project.notes']),
                'timestamp' => now()->toISOString()
            ]);

            // Log any problematic fields for debugging
            if ($request->has('client.zip_code')) {
                Log::debug("[$requestId] Zip code field detected", [
                    'zip_code_length' => strlen($request->input('client.zip_code')),
                    'zip_code_value' => $request->input('client.zip_code'),
                    'timestamp' => now()->toISOString()
                ]);
            }

            // Sanitize data before validation
            $sanitizedData = $request->all();
            if (isset($sanitizedData['client']['zip_code']) && strlen($sanitizedData['client']['zip_code']) > 10) {
                Log::warning("[$requestId] Zip code truncated", [
                    'original_length' => strlen($sanitizedData['client']['zip_code']),
                    'original_value' => $sanitizedData['client']['zip_code'],
                    'truncated_value' => substr($sanitizedData['client']['zip_code'], 0, 10),
                    'timestamp' => now()->toISOString()
                ]);
                $sanitizedData['client']['zip_code'] = substr($sanitizedData['client']['zip_code'], 0, 10);
            }

            // Log validation start
            Log::debug("[$requestId] Starting validation", [
                'validation_rules_count' => 15,
                'timestamp' => now()->toISOString()
            ]);

                        // Validate the complete request structure using sanitized data
            $validator = validator($sanitizedData, [
                // Client data
                'client.first_name' => 'required|string|max:255',
                'client.last_name' => 'required|string|max:255',
                'client.email' => 'required|email|unique:clients,email',
                'client.phone' => 'nullable|string|max:20',
                'client.address' => 'nullable|string|max:255',
                'client.city' => 'nullable|string|max:255',
                'client.state' => 'nullable|string|max:255',
                'client.zip_code' => 'nullable|string|max:10',

                // Intake response data (project requirements)
                'intake_response.full_name' => 'required|string|max:255',
                'intake_response.company_name' => 'required|string|max:255',
                'intake_response.email' => 'required|email',
                'intake_response.project_description' => 'required|string',
                'intake_response.budget_range' => 'required|string|max:255',
                'intake_response.deadline' => 'required|date',
                'intake_response.project_type' => 'required|string|max:255',
                'intake_response.project_examples' => 'nullable|array',

                // Proposal data
                'proposal.title' => 'nullable|string|max:255',
                'proposal.scope' => 'required|string',
                'proposal.deliverables' => 'required|array',
                'proposal.timeline' => 'required|array',
                'proposal.price' => 'required|numeric|min:0',
                'proposal.status' => 'sometimes|string|in:draft,sent,accepted,rejected',

                // Project data
                'project.title' => 'required|string|max:255',
                'project.status' => 'sometimes|string|in:not_started,in_progress,paused,done,draft',
                'project.current_phase' => 'required|string|max:255',
                'project.kickoff_date' => 'required|date',
                'project.expected_delivery' => 'required|date|after:project.kickoff_date',
                'project.notes' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                throw new \Illuminate\Validation\ValidationException($validator);
            }

            $validated = $validator->validated();

            Log::info("[$requestId] Validation passed successfully", [
                'validated_fields_count' => count($validated),
                'client_data_present' => isset($validated['client']),
                'intake_response_data_present' => isset($validated['intake_response']),
                'proposal_data_present' => isset($validated['proposal']),
                'project_data_present' => isset($validated['project']),
                'timestamp' => now()->toISOString()
            ]);

            // Log database transaction start
            Log::debug("[$requestId] Starting database transaction", [
                'transaction_start_time' => now()->toISOString()
            ]);

            // Use database transaction to ensure data consistency
            $result = DB::transaction(function () use ($validated, $requestId) {
                Log::debug("[$requestId] Inside database transaction", [
                    'step' => 'transaction_started',
                    'timestamp' => now()->toISOString()
                ]);

                // Create or find intake for the user
                Log::debug("[$requestId] Creating/finding intake", [
                    'step' => 'intake_creation',
                    'user_id' => auth()->id(),
                    'timestamp' => now()->toISOString()
                ]);

                // Generate a unique link for the intake
                $uniqueLink = 'intake_' . auth()->id() . '_' . time() . '_' . uniqid();

                $intake = Intake::create([
                    'user_id' => auth()->id(),
                    'client_id' => null, // Will be updated after client creation
                    'expiration_date' => now()->addDays(30), // 30 days from now
                    'status' => 'pending',
                    'link' => $uniqueLink
                ]);

                Log::info("[$requestId] Intake created successfully", [
                    'intake_id' => $intake->id,
                    'intake_link' => $intake->link,
                    'user_id' => $intake->user_id,
                    'expiration_date' => $intake->expiration_date,
                    'timestamp' => now()->toISOString()
                ]);

                // Create client
                Log::debug("[$requestId] Creating client", [
                    'step' => 'client_creation',
                    'client_email' => $validated['client']['email'],
                    'client_name' => $validated['client']['first_name'] . ' ' . $validated['client']['last_name'],
                    'timestamp' => now()->toISOString()
                ]);

                $client = Client::create([
                    'first_name' => $validated['client']['first_name'],
                    'last_name' => $validated['client']['last_name'],
                    'email' => $validated['client']['email'],
                    'phone' => $validated['client']['phone'] ?? null,
                    'address' => $validated['client']['address'] ?? null,
                    'city' => $validated['client']['city'] ?? null,
                    'state' => $validated['client']['state'] ?? null,
                    'zip_code' => $validated['client']['zip_code'] ?? null,
                ]);

                Log::info("[$requestId] Client created successfully", [
                    'client_id' => $client->id,
                    'client_email' => $client->email,
                    'client_full_name' => $client->full_name,
                    'timestamp' => now()->toISOString()
                ]);

                // Update intake with client_id
                Log::debug("[$requestId] Updating intake with client_id", [
                    'step' => 'intake_client_update',
                    'intake_id' => $intake->id,
                    'client_id' => $client->id,
                    'timestamp' => now()->toISOString()
                ]);

                $intake->update(['client_id' => $client->id]);

                Log::info("[$requestId] Intake updated with client", [
                    'intake_id' => $intake->id,
                    'client_id' => $intake->client_id,
                    'timestamp' => now()->toISOString()
                ]);

                // Create intake response
                Log::debug("[$requestId] Creating intake response", [
                    'step' => 'intake_response_creation',
                    'intake_id' => $intake->id,
                    'company_name' => $validated['intake_response']['company_name'],
                    'project_type' => $validated['intake_response']['project_type'],
                    'timestamp' => now()->toISOString()
                ]);

                $intakeResponse = IntakeResponse::create([
                    'intake_id' => $intake->id,
                    'full_name' => $validated['intake_response']['full_name'],
                    'company_name' => $validated['intake_response']['company_name'],
                    'email' => $validated['intake_response']['email'],
                    'project_description' => $validated['intake_response']['project_description'],
                    'budget_range' => $validated['intake_response']['budget_range'],
                    'deadline' => $validated['intake_response']['deadline'],
                    'project_type' => $validated['intake_response']['project_type'],
                    'project_examples' => $validated['intake_response']['project_examples'] ?? null,
                ]);

                Log::info("[$requestId] Intake response created successfully", [
                    'intake_response_id' => $intakeResponse->id,
                    'company_name' => $intakeResponse->company_name,
                    'project_type' => $intakeResponse->project_type,
                    'deadline' => $intakeResponse->deadline,
                    'timestamp' => now()->toISOString()
                ]);

                // Create proposal
                Log::debug("[$requestId] Creating proposal", [
                    'step' => 'proposal_creation',
                    'intake_response_id' => $intakeResponse->id,
                    'proposal_price' => $validated['proposal']['price'],
                    'proposal_status' => $validated['proposal']['status'] ?? 'draft',
                    'deliverables_count' => count($validated['proposal']['deliverables']),
                    'timeline_phases_count' => count($validated['proposal']['timeline']),
                    'timestamp' => now()->toISOString()
                ]);

                // Generate proposal title from provided title, project title, or company name
                $proposalTitle = $validated['proposal']['title'] ?? $validated['project']['title'] ?? $validated['intake_response']['company_name'] . ' Project';

                $proposal = Proposal::create([
                    'intake_response_id' => $intakeResponse->id,
                    'title' => $proposalTitle,
                    'scope' => $validated['proposal']['scope'],
                    'deliverables' => $validated['proposal']['deliverables'],
                    'timeline' => $validated['proposal']['timeline'],
                    'price' => $validated['proposal']['price'],
                    'status' => $validated['proposal']['status'] ?? 'draft',
                    'user_id' => auth()->id(),
                ]);

                Log::info("[$requestId] Proposal created successfully", [
                    'proposal_id' => $proposal->id,
                    'proposal_price' => $proposal->price,
                    'proposal_status' => $proposal->status,
                    'user_id' => $proposal->user_id,
                    'timestamp' => now()->toISOString()
                ]);

                // Create project
                Log::debug("[$requestId] Creating project", [
                    'step' => 'project_creation',
                    'proposal_id' => $proposal->id,
                    'project_title' => $validated['project']['title'],
                    'project_status' => $validated['project']['status'] ?? 'not_started',
                    'current_phase' => $validated['project']['current_phase'],
                    'kickoff_date' => $validated['project']['kickoff_date'],
                    'expected_delivery' => $validated['project']['expected_delivery'],
                    'timestamp' => now()->toISOString()
                ]);

                $project = Project::create([
                    'proposal_id' => $proposal->id,
                    'title' => $validated['project']['title'],
                    'status' => $validated['project']['status'] ?? 'not_started',
                    'current_phase' => $validated['project']['current_phase'],
                    'kickoff_date' => $validated['project']['kickoff_date'],
                    'expected_delivery' => $validated['project']['expected_delivery'],
                    'notes' => $validated['project']['notes'] ?? null,
                ]);

                Log::info("[$requestId] Project created successfully", [
                    'project_id' => $project->id,
                    'project_title' => $project->title,
                    'project_status' => $project->status,
                    'current_phase' => $project->current_phase,
                    'kickoff_date' => $project->kickoff_date,
                    'expected_delivery' => $project->expected_delivery,
                    'timestamp' => now()->toISOString()
                ]);

                // Load relationships for response
                Log::debug("[$requestId] Loading project relationships", [
                    'step' => 'loading_relationships',
                    'timestamp' => now()->toISOString()
                ]);

                $projectWithRelations = $project->load('proposal', 'tasks.subtasks');

                Log::debug("[$requestId] Database transaction completed successfully", [
                    'step' => 'transaction_completed',
                    'entities_created' => [
                        'client_id' => $client->id,
                        'intake_response_id' => $intakeResponse->id,
                        'proposal_id' => $proposal->id,
                        'project_id' => $project->id
                    ],
                    'timestamp' => now()->toISOString()
                ]);

                return [
                    'client' => $client,
                    'intake_response' => $intakeResponse,
                    'proposal' => $proposal,
                    'project' => $projectWithRelations
                ];
            });

            Log::info("[$requestId] New client project created successfully", [
                'user_id' => auth()->id(),
                'client_id' => $result['client']->id,
                'intake_response_id' => $result['intake_response']->id,
                'proposal_id' => $result['proposal']->id,
                'project_id' => $result['project']->id,
                'total_entities_created' => 4,
                'response_status' => 201,
                'timestamp' => now()->toISOString()
            ]);

            return response()->json([
                'message' => 'Client, proposal, and project created successfully',
                'data' => $result,
                'request_id' => $requestId
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error("[$requestId] Validation failed for new client project", [
                'user_id' => auth()->id(),
                'validation_errors' => $e->errors(),
                'failed_fields' => array_keys($e->errors()),
                'total_errors' => count($e->errors()),
                'response_status' => 422,
                'timestamp' => now()->toISOString()
            ]);

            return response()->json([
                'error' => 'Validation failed',
                'errors' => $e->errors(),
                'request_id' => $requestId
            ], 422);

        } catch (\Illuminate\Database\QueryException $e) {
            Log::error("[$requestId] Database query exception in new client project", [
                'user_id' => auth()->id(),
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'sql' => $e->getSql(),
                'bindings' => $e->getBindings(),
                'response_status' => 500,
                'timestamp' => now()->toISOString()
            ]);

            return response()->json([
                'error' => 'Database error occurred',
                'message' => 'Failed to create new client project due to database error',
                'request_id' => $requestId
            ], 500);

        } catch (\Exception $e) {
            Log::error("[$requestId] Failed to create new client project", [
                'user_id' => auth()->id(),
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'error_trace' => $e->getTraceAsString(),
                'request_data' => $request->except(['client.phone', 'client.address', 'client.city', 'client.state', 'client.zip_code', 'project.notes']),
                'response_status' => 500,
                'timestamp' => now()->toISOString()
            ]);

            return response()->json([
                'error' => 'Failed to create new client project',
                'message' => 'An unexpected error occurred',
                'request_id' => $requestId
            ], 500);
        }
    }
}
