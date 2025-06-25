<?php

namespace App\Http\Controllers;

use App\Models\Subtask;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class SubtaskController extends Controller
{
    /**
     * Display a listing of subtasks for a specific task.
     */
    public function index(Task $task): JsonResponse
    {
        Log::info('Fetching subtasks for task', ['task_id' => $task->id]);

        $subtasks = $task->subtasks()->get();

        Log::info('Successfully retrieved subtasks', [
            'task_id' => $task->id,
            'subtasks_count' => $subtasks->count()
        ]);

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
        Log::info('Creating new subtask for task', [
            'task_id' => $task->id,
            'request_data' => $request->only(['description', 'status'])
        ]);

        $validated = $request->validate([
            'description' => 'required|string|max:1000',
            'status' => 'required|in:todo,in_progress,done'
        ]);

        $subtask = $task->subtasks()->create($validated);

        Log::info('Subtask created successfully', [
            'subtask_id' => $subtask->id,
            'task_id' => $task->id,
            'description' => $subtask->description,
            'status' => $subtask->status
        ]);

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
        Log::info('Fetching subtask details', ['subtask_id' => $subtask->id]);

        $subtask->load('task');

        Log::info('Successfully retrieved subtask details', [
            'subtask_id' => $subtask->id,
            'task_id' => $subtask->task_id
        ]);

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
        Log::info('Updating subtask', [
            'subtask_id' => $subtask->id,
            'request_data' => $request->only(['description', 'status']),
            'current_status' => $subtask->status
        ]);

        $validated = $request->validate([
            'description' => 'sometimes|string|max:1000',
            'status' => 'sometimes|in:todo,in_progress,done'
        ]);

        $oldData = $subtask->only(['description', 'status']);
        $subtask->update($validated);

        Log::info('Subtask updated successfully', [
            'subtask_id' => $subtask->id,
            'old_data' => $oldData,
            'new_data' => $subtask->only(['description', 'status'])
        ]);

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
        Log::info('Deleting subtask', [
            'subtask_id' => $subtask->id,
            'task_id' => $subtask->task_id,
            'description' => $subtask->description
        ]);

        $subtask->delete();

        Log::info('Subtask deleted successfully', ['subtask_id' => $subtask->id]);

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
        Log::info('Updating subtask status', [
            'subtask_id' => $subtask->id,
            'current_status' => $subtask->status,
            'requested_status' => $request->input('status')
        ]);

        $validated = $request->validate([
            'status' => 'required|in:todo,in_progress,done'
        ]);

        $oldStatus = $subtask->status;
        $subtask->update(['status' => $validated['status']]);

        Log::info('Subtask status updated successfully', [
            'subtask_id' => $subtask->id,
            'old_status' => $oldStatus,
            'new_status' => $subtask->status
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Subtask status updated successfully',
            'data' => $subtask
        ]);
    }
}
