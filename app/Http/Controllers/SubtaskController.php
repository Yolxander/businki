<?php

namespace App\Http\Controllers;

use App\Models\Subtask;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SubtaskController extends Controller
{
    /**
     * Display a listing of subtasks for a specific task.
     */
    public function index(Task $task): JsonResponse
    {
        $subtasks = $task->subtasks()->get();

        return response()->json([
            'success' => true,
            'data' => $subtasks
        ]);
    }

    /**
     * Store a newly created subtask.
     */
    public function store(Request $request, Task $task): JsonResponse
    {
        $validated = $request->validate([
            'description' => 'required|string|max:1000',
            'status' => 'required|in:todo,in_progress,done'
        ]);

        $subtask = $task->subtasks()->create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Subtask created successfully',
            'data' => $subtask
        ], 201);
    }

    /**
     * Display the specified subtask.
     */
    public function show(Subtask $subtask): JsonResponse
    {
        $subtask->load('task');

        return response()->json([
            'success' => true,
            'data' => $subtask
        ]);
    }

    /**
     * Update the specified subtask.
     */
    public function update(Request $request, Subtask $subtask): JsonResponse
    {
        $validated = $request->validate([
            'description' => 'sometimes|string|max:1000',
            'status' => 'sometimes|in:todo,in_progress,done'
        ]);

        $subtask->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Subtask updated successfully',
            'data' => $subtask
        ]);
    }

    /**
     * Remove the specified subtask.
     */
    public function destroy(Subtask $subtask): JsonResponse
    {
        $subtask->delete();

        return response()->json([
            'success' => true,
            'message' => 'Subtask deleted successfully'
        ]);
    }

    /**
     * Update subtask status.
     */
    public function updateStatus(Request $request, Subtask $subtask): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:todo,in_progress,done'
        ]);

        $subtask->update(['status' => $validated['status']]);

        return response()->json([
            'success' => true,
            'message' => 'Subtask status updated successfully',
            'data' => $subtask
        ]);
    }
}
