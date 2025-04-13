<?php

namespace App\Http\Controllers\Api;

use App\Models\Task;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;

class TaskController extends Controller
{
    // GET /api/tasks?provider_id=1
    public function index(Request $request)
    {
        try {
            $providerId = $request->query('provider_id');

            if (!$providerId) {
                return response()->json(['message' => 'provider_id is required'], 400);
            }

            $tasks = Task::where('provider_id', $providerId)->get();

            return response()->json($tasks);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // GET /api/tasks/{id}
    public function show($id)
    {
        try {
            $task = Task::with('subtasks')->find($id);

            if (!$task) {
                return response()->json(['message' => 'Task not found'], 404);
            }

            return response()->json($task);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // POST /api/tasks
    public function store(Request $request)
    {
        try {
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
            ]);

            $task = Task::create($validated);

            return response()->json($task, 201);
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

    // PUT /api/tasks/{id}
    public function update(Request $request, $id)
    {
        try {
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

    // DELETE /api/tasks/{id}
    public function destroy($id)
    {
        try {
            $task = Task::find($id);

            if (!$task) {
                return response()->json(['message' => 'Task not found'], 404);
            }

            $task->delete();

            return response()->json(['message' => 'Task deleted successfully']);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // PUT /api/tasks/{id}/toggle-completion
    public function toggleCompletion($id)
    {
        try {
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
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // GET /api/projects/{projectId}/tasks
    public function getTasksByProject($projectId)
    {
        try {
            $tasks = Task::where('project_id', $projectId)->get();

            return response()->json($tasks);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
