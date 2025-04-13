<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class ProjectController extends Controller
{
    // GET /api/projects?provider_id=1
    public function index(Request $request)
    {
        $providerId = $request->query('provider_id');

        if (!$providerId) {
            return response()->json(['message' => 'provider_id is required'], 400);
        }

        $projects = Project::where('provider_id', $providerId)->with('client')->get();

        return response()->json($projects);
    }

    public function allProjects()
    {
        $projects = Project::with([

            'tasks.subtasks'
        ])->get();

        return response()->json([
            'projects' => $projects
        ]);
    }

    // GET /api/projects/{id}
    public function show($id)
    {
        try {
            $project = Project::with('tasks.subtasks','client','timeline')->find($id);

            if (!$project) {
                return response()->json(['message' => 'Project not found'], 404);
            }

            return response()->json($project);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // POST /api/projects
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string',
                'description' => 'nullable|string',
                'status' => 'nullable|string',
                'start_date' => 'nullable|date',
                'due_date' => 'nullable|date',
                'provider_id' => 'required|exists:providers,id',
                'color' => 'nullable|string',
            ]);

            // Add client_id from the authenticated user
            $validated['client_id'] = auth()->id();

            $project = Project::create($validated);

            return response()->json($project, 201);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // PUT /api/projects/{id}
    public function update(Request $request, $id)
    {

        try {
            $project = Project::find($id);

            if (!$project) {
                return response()->json(['message' => 'Project not found'], 404);
            }

            $validated = $request->validate([
                'name' => 'sometimes|string',
                'description' => 'nullable|string',
                'status' => 'nullable|string',
                'start_date' => 'nullable|date',
                'due_date' => 'nullable|date',
                'client_id' => 'sometimes|exists:clients,id',
                'color' => 'nullable|string',
            ]);

            // Convert ISO 8601 to Y-m-d if necessary
            if (!empty($validated['due_date'])) {
                $validated['due_date'] = Carbon::parse($validated['due_date'])->format('Y-m-d');
            }

            if (!empty($validated['start_date'])) {
                $validated['start_date'] = Carbon::parse($validated['start_date'])->format('Y-m-d');
            }

            if (isset($validated['client_id'])) {
                $validated['client_id'] = (int) $validated['client_id'];
            }

            $project->update($validated);

            return response()->json($project);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // DELETE /api/projects/{id}
    public function destroy($id)
    {
        $project = Project::find($id);

        if (!$project) {
            return response()->json(['message' => 'Project not found'], 404);
        }

        $project->delete();

        return response()->json(['message' => 'Project deleted successfully']);
    }

    // GET /api/clients/{clientId}/projects
    public function getProjectsByClient($clientId)
    {
        $projects = Project::where('client_id', $clientId)->get();

        return response()->json($projects);
    }
}
