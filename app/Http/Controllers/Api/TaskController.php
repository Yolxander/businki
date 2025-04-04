<?php

namespace App\Http\Controllers\Api;

use App\Models\Task;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TaskController extends Controller
{
    // GET /api/tasks?provider_id=1
    public function index(Request $request)
    {
        $providerId = $request->query('provider_id');

        if (!$providerId) {
            return response()->json(['message' => 'provider_id is required'], 400);
        }

        $tasks = Task::where('provider_id', $providerId)->get();

        return response()->json($tasks);
    }

    // GET /api/tasks/{id}
    public function show($id)
    {
        $task = Task::find($id);

        if (!$task) {
            return response()->json(['message' => 'Task not found'], 404);
        }

        return response()->json($task);
    }

    // POST /api/tasks
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'description' => 'nullable|string',
            'status' => 'nullable|string',
            'priority' => 'nullable|string',
            'category' => 'nullable|string',
            'due_date' => 'nullable|date',
            'project_id' => 'required|exists:projects,id',
            'provider_id' => 'required|exists:providers,id',
            'completed' => 'nullable|boolean',
            'github_repo' => 'nullable|string',
            'tech_stack' => 'nullable|string',
            'code_snippet' => 'nullable|string',
        ]);

        $task = Task::create($validated);

        return response()->json($task, 201);
    }

    // PUT /api/tasks/{id}
    public function update(Request $request, $id)
    {
        $task = Task::find($id);

        if (!$task) {
            return response()->json(['message' => 'Task not found'], 404);
        }

        $validated = $request->validate([
            'title' => 'sometimes|string',
            'description' => 'nullable|string',
            'status' => 'nullable|string',
            'priority' => 'nullable|string',
            'category' => 'nullable|string',
            'due_date' => 'nullable|date',
            'project_id' => 'sometimes|exists:projects,id',
            'provider_id' => 'sometimes|exists:providers,id',
            'completed' => 'nullable|boolean',
            'github_repo' => 'nullable|string',
            'tech_stack' => 'nullable|string',
            'code_snippet' => 'nullable|string',
        ]);

        $task->update($validated);

        return response()->json($task);
    }

    // DELETE /api/tasks/{id}
    public function destroy($id)
    {
        $task = Task::find($id);

        if (!$task) {
            return response()->json(['message' => 'Task not found'], 404);
        }

        $task->delete();

        return response()->json(['message' => 'Task deleted successfully']);
    }

    // PUT /api/tasks/{id}/toggle-completion
    public function toggleCompletion($id)
    {
        $task = Task::find($id);

        if (!$task) {
            return response()->json(['message' => 'Task not found'], 404);
        }

        $task->completed = !$task->completed;
        $task->save();

        return response()->json([
            'message' => 'Task completion status toggled',
            'completed' => $task->completed,
        ]);
    }

    // GET /api/projects/{projectId}/tasks
    public function getTasksByProject($projectId)
    {
        $tasks = Task::where('project_id', $projectId)->get();

        return response()->json($tasks);
    }
}
