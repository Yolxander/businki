<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Client;
use App\Models\IntakeResponse;
use App\Models\Proposal;
use App\Models\Intake;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $requestId = uniqid('projects_index_');

        try {
            Log::info("[$requestId] Loading projects index page", [
                'user_id' => auth()->id(),
                'user_email' => auth()->user()?->email,
                'timestamp' => now()->toISOString()
            ]);

            $projects = Project::with(['client', 'proposal', 'tasks.subtasks'])
                ->where('user_id', auth()->id())
                ->orderBy('created_at', 'desc')
                ->get();

            Log::info("[$requestId] Projects loaded successfully", [
                'user_id' => auth()->id(),
                'projects_count' => $projects->count(),
                'timestamp' => now()->toISOString()
            ]);

            return Inertia::render('Projects', [
                'auth' => ['user' => auth()->user()],
                'projects' => $projects,
                'requestId' => $requestId
            ]);

        } catch (\Exception $e) {
            Log::error("[$requestId] Failed to load projects index", [
                'user_id' => auth()->id(),
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'timestamp' => now()->toISOString()
            ]);

            return Inertia::render('Projects', [
                'auth' => ['user' => auth()->user()],
                'projects' => collect([]),
                'error' => 'Failed to load projects',
                'requestId' => $requestId
            ]);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        $requestId = uniqid('projects_create_');

        try {
            Log::info("[$requestId] Loading project creation form", [
                'user_id' => auth()->id(),
                'user_email' => auth()->user()?->email,
                'timestamp' => now()->toISOString()
            ]);

            $clients = Client::whereHas('users', function($query) {
                $query->where('user_id', auth()->id());
            })->get();

            Log::info("[$requestId] Project creation form loaded", [
                'user_id' => auth()->id(),
                'clients_count' => $clients->count(),
                'timestamp' => now()->toISOString()
            ]);

            return Inertia::render('CreateProject', [
                'auth' => ['user' => auth()->user()],
                'clients' => $clients,
                'requestId' => $requestId
            ]);

        } catch (\Exception $e) {
            Log::error("[$requestId] Failed to load project creation form", [
                'user_id' => auth()->id(),
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'timestamp' => now()->toISOString()
            ]);

            return Inertia::render('CreateProject', [
                'auth' => ['user' => auth()->user()],
                'clients' => collect([]),
                'error' => 'Failed to load form',
                'requestId' => $requestId
            ]);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $requestId = uniqid('projects_store_');

        try {
            Log::info("[$requestId] Creating new project", [
                'user_id' => auth()->id(),
                'user_email' => auth()->user()?->email,
                'request_method' => $request->method(),
                'request_url' => $request->fullUrl(),
                'request_data' => $request->except(['notes']),
                'timestamp' => now()->toISOString()
            ]);

            $validated = $request->validate([
                'proposal_id' => 'nullable|exists:proposals,id',
                'client_id' => 'nullable|exists:clients,id',
                'user_id' => 'nullable|exists:users,id',
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'status' => 'required|string|in:not_started,in_progress,paused,completed,planned',
                'current_phase' => 'required|string|max:255',
                'priority' => 'nullable|string|in:low,medium,high',
                'progress' => 'nullable|integer|min:0|max:100',
                'kickoff_date' => 'required|date',
                'start_date' => 'nullable|date',
                'due_date' => 'required|date',
                'notes' => 'nullable|string',
                'color' => 'nullable|string|max:7'
            ]);

            // Ensure user_id is set to current user
            $validated['user_id'] = auth()->id();

            Log::info("[$requestId] Validation passed, creating project", [
                'user_id' => auth()->id(),
                'validated_data' => $validated,
                'timestamp' => now()->toISOString()
            ]);

            $project = Project::create($validated);

            Log::info("[$requestId] Project created successfully", [
                'user_id' => auth()->id(),
                'project_id' => $project->id,
                'project_name' => $project->name,
                'client_id' => $project->client_id,
                'proposal_id' => $project->proposal_id,
                'timestamp' => now()->toISOString()
            ]);

            return redirect()->route('projects.show', $project->id)
                ->with('success', 'Project created successfully!')
                ->with('requestId', $requestId);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error("[$requestId] Validation failed for project creation", [
                'user_id' => auth()->id(),
                'validation_errors' => $e->errors(),
                'failed_fields' => array_keys($e->errors()),
                'timestamp' => now()->toISOString()
            ]);

            return back()->withErrors($e->errors())->withInput();

        } catch (\Exception $e) {
            Log::error("[$requestId] Failed to create project", [
                'user_id' => auth()->id(),
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'error_trace' => $e->getTraceAsString(),
                'request_data' => $request->except(['notes']),
                'timestamp' => now()->toISOString()
            ]);

            return back()->withErrors(['general' => 'Failed to create project. Please try again.'])->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project): Response
    {
        $requestId = uniqid('projects_show_');

        try {
            Log::info("[$requestId] Loading project details", [
                'user_id' => auth()->id(),
                'user_email' => auth()->user()?->email,
                'project_id' => $project->id,
                'project_name' => $project->name,
                'timestamp' => now()->toISOString()
            ]);

            // Load relationships
            $project->load(['client', 'proposal', 'tasks.subtasks']);

            Log::info("[$requestId] Project details loaded successfully", [
                'user_id' => auth()->id(),
                'project_id' => $project->id,
                'has_client' => $project->client ? true : false,
                'has_proposal' => $project->proposal ? true : false,
                'tasks_count' => $project->tasks->count(),
                'timestamp' => now()->toISOString()
            ]);

            return Inertia::render('ProjectDetails', [
                'auth' => ['user' => auth()->user()],
                'project' => $project,
                'requestId' => $requestId
            ]);

        } catch (\Exception $e) {
            Log::error("[$requestId] Failed to load project details", [
                'user_id' => auth()->id(),
                'project_id' => $project->id,
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'timestamp' => now()->toISOString()
            ]);

            return Inertia::render('ProjectDetails', [
                'auth' => ['user' => auth()->user()],
                'project' => null,
                'error' => 'Failed to load project details',
                'requestId' => $requestId
            ]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Project $project): Response
    {
        $requestId = uniqid('projects_edit_');

        try {
            Log::info("[$requestId] Loading project edit form", [
                'user_id' => auth()->id(),
                'user_email' => auth()->user()?->email,
                'project_id' => $project->id,
                'project_name' => $project->name,
                'timestamp' => now()->toISOString()
            ]);

            // Load relationships
            $project->load(['client', 'proposal']);

            $clients = Client::whereHas('users', function($query) {
                $query->where('user_id', auth()->id());
            })->get();

            Log::info("[$requestId] Project edit form loaded successfully", [
                'user_id' => auth()->id(),
                'project_id' => $project->id,
                'clients_count' => $clients->count(),
                'timestamp' => now()->toISOString()
            ]);

            return Inertia::render('EditProject', [
                'auth' => ['user' => auth()->user()],
                'project' => $project,
                'clients' => $clients,
                'projectId' => $project->id,
                'requestId' => $requestId
            ]);

        } catch (\Exception $e) {
            Log::error("[$requestId] Failed to load project edit form", [
                'user_id' => auth()->id(),
                'project_id' => $project->id,
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'timestamp' => now()->toISOString()
            ]);

            return Inertia::render('EditProject', [
                'auth' => ['user' => auth()->user()],
                'project' => null,
                'clients' => collect([]),
                'projectId' => $project->id,
                'error' => 'Failed to load project for editing',
                'requestId' => $requestId
            ]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project)
    {
        $requestId = uniqid('projects_update_');

        try {
            Log::info("[$requestId] Updating project", [
                'user_id' => auth()->id(),
                'user_email' => auth()->user()?->email,
                'project_id' => $project->id,
                'project_name' => $project->name,
                'request_method' => $request->method(),
                'request_url' => $request->fullUrl(),
                'request_data' => $request->except(['notes']),
                'timestamp' => now()->toISOString()
            ]);

            $validated = $request->validate([
                'proposal_id' => 'nullable|exists:proposals,id',
                'client_id' => 'nullable|exists:clients,id',
                'user_id' => 'nullable|exists:users,id',
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'status' => 'required|string|in:not_started,in_progress,paused,completed,planned',
                'current_phase' => 'required|string|max:255',
                'priority' => 'nullable|string|in:low,medium,high',
                'progress' => 'nullable|integer|min:0|max:100',
                'kickoff_date' => 'required|date',
                'start_date' => 'nullable|date',
                'due_date' => 'required|date',
                'notes' => 'nullable|string',
                'color' => 'nullable|string|max:7'
            ]);

            Log::info("[$requestId] Validation passed, updating project", [
                'user_id' => auth()->id(),
                'project_id' => $project->id,
                'updated_fields' => array_keys($validated),
                'timestamp' => now()->toISOString()
            ]);

            $project->update($validated);

            Log::info("[$requestId] Project updated successfully", [
                'user_id' => auth()->id(),
                'project_id' => $project->id,
                'project_name' => $project->name,
                'updated_fields' => array_keys($validated),
                'timestamp' => now()->toISOString()
            ]);

            return back()->with('success', 'Project updated successfully!')->with('requestId', $requestId);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error("[$requestId] Validation failed for project update", [
                'user_id' => auth()->id(),
                'project_id' => $project->id,
                'validation_errors' => $e->errors(),
                'failed_fields' => array_keys($e->errors()),
                'timestamp' => now()->toISOString()
            ]);

            return back()->withErrors($e->errors())->withInput();

        } catch (\Exception $e) {
            Log::error("[$requestId] Failed to update project", [
                'user_id' => auth()->id(),
                'project_id' => $project->id,
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'error_trace' => $e->getTraceAsString(),
                'request_data' => $request->except(['notes']),
                'timestamp' => now()->toISOString()
            ]);

            return back()->withErrors(['general' => 'Failed to update project. Please try again.'])->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project)
    {
        $requestId = uniqid('projects_destroy_');

        try {
            Log::info("[$requestId] Deleting project", [
                'user_id' => auth()->id(),
                'user_email' => auth()->user()?->email,
                'project_id' => $project->id,
                'project_name' => $project->name,
                'timestamp' => now()->toISOString()
            ]);

            $projectName = $project->name;
            $projectId = $project->id;

            $project->delete();

            Log::info("[$requestId] Project deleted successfully", [
                'user_id' => auth()->id(),
                'project_id' => $projectId,
                'project_name' => $projectName,
                'timestamp' => now()->toISOString()
            ]);

            return redirect()->route('projects.index')
                ->with('success', "Project '$projectName' deleted successfully!")
                ->with('requestId', $requestId);

        } catch (\Exception $e) {
            Log::error("[$requestId] Failed to delete project", [
                'user_id' => auth()->id(),
                'project_id' => $project->id,
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'error_trace' => $e->getTraceAsString(),
                'timestamp' => now()->toISOString()
            ]);

            return back()->withErrors(['general' => 'Failed to delete project. Please try again.']);
        }
    }

    /**
     * Create a new client, proposal, and project in one transaction.
     */
    public function newClientProject(Request $request)
    {
        $requestId = uniqid('new_client_project_');

        try {
            Log::info("[$requestId] Starting new client project creation", [
                'user_id' => auth()->id(),
                'user_email' => auth()->user()?->email,
                'request_method' => $request->method(),
                'request_url' => $request->fullUrl(),
                'request_data' => $request->except(['client.phone', 'client.address', 'client.city', 'client.state', 'client.zip_code', 'project.notes']),
                'timestamp' => now()->toISOString()
            ]);

            // Validate the complete request structure
            $validator = validator($request->all(), [
                // Client data
                'client.first_name' => 'required|string|max:255',
                'client.last_name' => 'required|string|max:255',
                'client.email' => 'required|email|unique:clients,email',
                'client.phone' => 'nullable|string|max:20',
                'client.address' => 'nullable|string|max:255',
                'client.city' => 'nullable|string|max:255',
                'client.state' => 'nullable|string|max:255',
                'client.zip_code' => 'nullable|string|max:10',

                // Intake response data
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
                'project.name' => 'required|string|max:255',
                'project.description' => 'nullable|string',
                'project.status' => 'sometimes|string|in:not_started,in_progress,paused,completed,planned',
                'project.current_phase' => 'required|string|max:255',
                'project.priority' => 'nullable|string|in:low,medium,high',
                'project.progress' => 'nullable|integer|min:0|max:100',
                'project.kickoff_date' => 'required|date',
                'project.start_date' => 'nullable|date',
                'project.due_date' => 'required|date|after:project.kickoff_date',
                'project.notes' => 'nullable|string',
                'project.color' => 'nullable|string|max:7',
            ]);

            if ($validator->fails()) {
                throw new \Illuminate\Validation\ValidationException($validator);
            }

            $validated = $validator->validated();

            Log::info("[$requestId] Validation passed, starting database transaction", [
                'user_id' => auth()->id(),
                'validated_fields_count' => count($validated),
                'timestamp' => now()->toISOString()
            ]);

            // Use database transaction to ensure data consistency
            $result = DB::transaction(function () use ($validated, $requestId) {
                Log::debug("[$requestId] Inside database transaction", [
                    'step' => 'transaction_started',
                    'timestamp' => now()->toISOString()
                ]);

                // Create or find intake for the user
                $uniqueLink = 'intake_' . auth()->id() . '_' . time() . '_' . uniqid();

                $intake = Intake::create([
                    'user_id' => auth()->id(),
                    'client_id' => null,
                    'expiration_date' => now()->addDays(30),
                    'status' => 'pending',
                    'link' => $uniqueLink
                ]);

                Log::info("[$requestId] Intake created", [
                    'intake_id' => $intake->id,
                    'timestamp' => now()->toISOString()
                ]);

                // Create client
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

                Log::info("[$requestId] Client created", [
                    'client_id' => $client->id,
                    'client_email' => $client->email,
                    'timestamp' => now()->toISOString()
                ]);

                // Connect the logged-in user to the client
                $userId = auth()->id();
                $existingRelationship = $client->users()->where('user_id', $userId)->exists();

                if (!$existingRelationship) {
                    $client->users()->attach($userId);
                    Log::info("[$requestId] User connected to client", [
                        'user_id' => $userId,
                        'client_id' => $client->id,
                        'timestamp' => now()->toISOString()
                    ]);
                }

                // Update intake with client_id
                $intake->update(['client_id' => $client->id]);

                // Create intake response
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

                Log::info("[$requestId] Intake response created", [
                    'intake_response_id' => $intakeResponse->id,
                    'timestamp' => now()->toISOString()
                ]);

                // Create proposal
                $proposalTitle = $validated['proposal']['title'] ?? $validated['project']['name'] ?? $validated['intake_response']['company_name'] . ' Project';

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

                Log::info("[$requestId] Proposal created", [
                    'proposal_id' => $proposal->id,
                    'timestamp' => now()->toISOString()
                ]);

                // Create project
                $project = Project::create([
                    'proposal_id' => $proposal->id,
                    'client_id' => $client->id,
                    'user_id' => auth()->id(),
                    'name' => $validated['project']['name'],
                    'description' => $validated['project']['description'] ?? null,
                    'status' => $validated['project']['status'] ?? 'not_started',
                    'current_phase' => $validated['project']['current_phase'],
                    'priority' => $validated['project']['priority'] ?? null,
                    'progress' => $validated['project']['progress'] ?? 0,
                    'kickoff_date' => $validated['project']['kickoff_date'],
                    'start_date' => $validated['project']['start_date'] ?? null,
                    'due_date' => $validated['project']['due_date'],
                    'notes' => $validated['project']['notes'] ?? null,
                    'color' => $validated['project']['color'] ?? null,
                ]);

                Log::info("[$requestId] Project created", [
                    'project_id' => $project->id,
                    'timestamp' => now()->toISOString()
                ]);

                return [
                    'client' => $client->load('users'),
                    'intake_response' => $intakeResponse,
                    'proposal' => $proposal,
                    'project' => $project->load('proposal', 'tasks.subtasks')
                ];
            });

            Log::info("[$requestId] New client project created successfully", [
                'user_id' => auth()->id(),
                'client_id' => $result['client']->id,
                'intake_response_id' => $result['intake_response']->id,
                'proposal_id' => $result['proposal']->id,
                'project_id' => $result['project']->id,
                'timestamp' => now()->toISOString()
            ]);

            return redirect()->route('projects.show', $result['project']->id)
                ->with('success', 'Client, proposal, and project created successfully!')
                ->with('requestId', $requestId);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error("[$requestId] Validation failed for new client project", [
                'user_id' => auth()->id(),
                'validation_errors' => $e->errors(),
                'failed_fields' => array_keys($e->errors()),
                'timestamp' => now()->toISOString()
            ]);

            return back()->withErrors($e->errors())->withInput();

        } catch (\Exception $e) {
            Log::error("[$requestId] Failed to create new client project", [
                'user_id' => auth()->id(),
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'error_trace' => $e->getTraceAsString(),
                'request_data' => $request->except(['client.phone', 'client.address', 'client.city', 'client.state', 'client.zip_code', 'project.notes']),
                'timestamp' => now()->toISOString()
            ]);

            return back()->withErrors(['general' => 'Failed to create new client project. Please try again.'])->withInput();
        }
    }

    /**
     * Connect an existing client to the logged-in user for project creation.
     */
    public function connectClientForProject(Request $request)
    {
        $requestId = uniqid('connect_client_project_');

        try {
            Log::info("[$requestId] Connecting client for project creation", [
                'user_id' => auth()->id(),
                'user_email' => auth()->user()?->email,
                'request_data' => $request->all(),
                'timestamp' => now()->toISOString()
            ]);

            $validated = $request->validate([
                'client_id' => 'required|exists:clients,id',
            ]);

            $client = Client::findOrFail($validated['client_id']);
            $userId = auth()->id();

            // Check if the relationship already exists
            $existingRelationship = $client->users()->where('user_id', $userId)->exists();

            if (!$existingRelationship) {
                $client->users()->attach($userId);
                Log::info("[$requestId] User connected to client successfully", [
                    'user_id' => $userId,
                    'client_id' => $client->id,
                    'timestamp' => now()->toISOString()
                ]);

                $message = 'Client connected successfully for project creation';
            } else {
                Log::info("[$requestId] User-client relationship already exists", [
                    'user_id' => $userId,
                    'client_id' => $client->id,
                    'timestamp' => now()->toISOString()
                ]);

                $message = 'Client already connected for project creation';
            }

            return back()->with('success', $message)->with('requestId', $requestId);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error("[$requestId] Validation failed for client connection", [
                'user_id' => auth()->id(),
                'validation_errors' => $e->errors(),
                'timestamp' => now()->toISOString()
            ]);

            return back()->withErrors($e->errors())->withInput();

        } catch (\Exception $e) {
            Log::error("[$requestId] Failed to connect client for project", [
                'user_id' => auth()->id(),
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'timestamp' => now()->toISOString()
            ]);

            return back()->withErrors(['general' => 'Failed to connect client for project. Please try again.'])->withInput();
        }
    }
}
