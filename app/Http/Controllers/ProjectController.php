<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        try {
            $projects = Project::with('proposal')->get();
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
}
