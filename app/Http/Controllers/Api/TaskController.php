<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class TaskController extends Controller
{
    public function store(Request $request)
    {
        Log::info('Task Store Request:', [
            'request_data' => $request->all(),
            'has_phase_id' => $request->has('phase_id'),
            'phase_id_value' => $request->input('phase_id')
        ]);

        $validator = Validator::make($request->all(), [
            'project_id' => 'required|exists:projects,id',
            'phase_id' => 'nullable|integer',
            'title' => 'required|string|max:255',
            'status' => 'required|in:todo,in_progress,done',
            'due_date' => 'nullable|date',
            'assigned_to' => 'nullable|exists:users,id',
            'description' => 'nullable|string',
            'priority' => 'nullable|in:low,medium,high',
            'tags' => 'nullable|array',
            'estimated_hours' => 'nullable|numeric|min:0|max:999.99'
        ]);

        if ($validator->fails()) {
            Log::warning('Task Store Validation Failed:', [
                'errors' => $validator->errors()->toArray()
            ]);
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Validate phase_id exists in project's proposal timeline if provided
        if ($request->has('phase_id')) {
            $project = Project::findOrFail($request->project_id);
            Log::info('Project Found:', [
                'project_id' => $project->id,
                'has_proposal' => !is_null($project->proposal),
                'proposal_timeline' => $project->proposal ? $project->proposal->timeline : null
            ]);

            if (!$project->proposal || !collect($project->proposal->timeline)->contains('id', $request->phase_id)) {
                Log::warning('Invalid Phase ID:', [
                    'phase_id' => $request->phase_id,
                    'project_id' => $project->id,
                    'timeline' => $project->proposal ? $project->proposal->timeline : null
                ]);
                return response()->json(['errors' => ['phase_id' => ['The selected phase does not exist in the project\'s proposal timeline.']]], 422);
            }
        }

        try {
            $task = Task::create($request->all());
            Log::info('Task Created Successfully:', [
                'task_id' => $task->id,
                'task_data' => $task->toArray()
            ]);
            return response()->json($task, 201);
        } catch (\Exception $e) {
            Log::error('Task Creation Failed:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Failed to create task'], 500);
        }
    }

    public function update(Request $request, Task $task)
    {
        Log::info('Task Update Request:', [
            'task_id' => $task->id,
            'request_data' => $request->all(),
            'current_task_data' => $task->toArray()
        ]);

        $validator = Validator::make($request->all(), [
            'project_id' => 'sometimes|required|exists:projects,id',
            'phase_id' => 'nullable|integer',
            'title' => 'sometimes|required|string|max:255',
            'status' => 'sometimes|required|in:todo,in_progress,done',
            'due_date' => 'nullable|date',
            'assigned_to' => 'nullable|exists:users,id',
            'description' => 'nullable|string',
            'priority' => 'nullable|in:low,medium,high',
            'tags' => 'nullable|array',
            'estimated_hours' => 'nullable|numeric|min:0|max:999.99'
        ]);

        if ($validator->fails()) {
            Log::warning('Task Update Validation Failed:', [
                'errors' => $validator->errors()->toArray()
            ]);
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Validate phase_id exists in project's proposal timeline if provided
        if ($request->has('phase_id')) {
            $project = Project::findOrFail($request->project_id ?? $task->project_id);
            Log::info('Project Found for Update:', [
                'project_id' => $project->id,
                'has_proposal' => !is_null($project->proposal),
                'proposal_timeline' => $project->proposal ? $project->proposal->timeline : null,
                'requested_phase_id' => $request->phase_id
            ]);

            if (!$project->proposal || !collect($project->proposal->timeline)->contains('id', $request->phase_id)) {
                Log::warning('Invalid Phase ID in Update:', [
                    'phase_id' => $request->phase_id,
                    'project_id' => $project->id,
                    'timeline' => $project->proposal ? $project->proposal->timeline : null
                ]);
                return response()->json(['errors' => ['phase_id' => ['The selected phase does not exist in the project\'s proposal timeline.']]], 422);
            }
        }

        try {
            $task->update($request->all());
            Log::info('Task Updated Successfully:', [
                'task_id' => $task->id,
                'updated_data' => $task->fresh()->toArray()
            ]);
            return response()->json($task->load(['project', 'assignedUser']));
        } catch (\Exception $e) {
            Log::error('Task Update Failed:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Failed to update task'], 500);
        }
    }

    public function connectToTimeline(Request $request, Task $task)
    {
        Log::info('Connect to Timeline Request:', [
            'task_id' => $task->id,
            'request_data' => $request->all(),
            'current_task_data' => $task->toArray()
        ]);

        $validator = Validator::make($request->all(), [
            'phase_id' => 'required|integer'
        ]);

        if ($validator->fails()) {
            Log::warning('Connect to Timeline Validation Failed:', [
                'errors' => $validator->errors()->toArray()
            ]);
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $project = Project::findOrFail($task->project_id);
        Log::info('Project Found for Timeline Connection:', [
            'project_id' => $project->id,
            'has_proposal' => !is_null($project->proposal),
            'proposal_timeline' => $project->proposal ? $project->proposal->timeline : null,
            'requested_phase_id' => $request->phase_id
        ]);

        if (!$project->proposal) {
            Log::warning('No Proposal Found:', [
                'project_id' => $project->id
            ]);
            return response()->json(['message' => 'No proposal found for this project'], 404);
        }

        if (!collect($project->proposal->timeline)->contains('id', $request->phase_id)) {
            Log::warning('Invalid Phase ID for Timeline Connection:', [
                'phase_id' => $request->phase_id,
                'project_id' => $project->id,
                'timeline' => $project->proposal->timeline
            ]);
            return response()->json(['errors' => ['phase_id' => ['The selected phase does not exist in the project\'s proposal timeline.']]], 422);
        }

        try {
            $task->update(['phase_id' => $request->phase_id]);
            Log::info('Task Connected to Timeline Successfully:', [
                'task_id' => $task->id,
                'phase_id' => $request->phase_id,
                'updated_task' => $task->fresh()->toArray()
            ]);

            return response()->json([
                'message' => 'Task successfully connected to timeline phase',
                'task' => $task->load(['project', 'assignedUser'])
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to Connect Task to Timeline:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Failed to connect task to timeline'], 500);
        }
    }

    // ... rest of the methods remain the same ...
}
