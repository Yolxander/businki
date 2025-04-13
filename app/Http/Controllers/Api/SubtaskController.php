<?php

namespace App\Http\Controllers\Api;

use App\Models\Subtask;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;

class SubtaskController extends Controller
{
    // GET /api/tasks/{taskId}/subtasks
    public function index($taskId)
    {
        try {
            $subtasks = Subtask::where('task_id', $taskId)->get();

            return response()->json($subtasks);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // POST /api/tasks/{taskId}/subtasks
    public function store(Request $request, $taskId)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string',
                'description' => 'nullable|string',
                'status' => 'nullable|string',
                'completed' => 'nullable|boolean',
                'provider_id' => 'required|exists:providers,id',
                'code_snippet' => 'nullable|string',
                'language' => 'nullable|string',
            ]);

            $subtask = Subtask::create(array_merge($validated, ['task_id' => $taskId]));

            return response()->json($subtask, 201);
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

    // PUT /api/subtasks/{id}
    public function update(Request $request, $id)
    {
        try {
            $subtask = Subtask::find($id);

            if (!$subtask) {
                return response()->json(['message' => 'Subtask not found'], 404);
            }

            $validated = $request->validate([
                'title' => 'sometimes|string',
                'description' => 'nullable|string',
                'status' => 'nullable|string',
                'completed' => 'nullable|boolean',
                'code_snippet' => 'nullable|string',
                'language' => 'nullable|string',
            ]);

            $subtask->update($validated);

            return response()->json($subtask);
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

    // DELETE /api/subtasks/{id}
    public function destroy($id)
    {
        try {
            $subtask = Subtask::find($id);

            if (!$subtask) {
                return response()->json(['message' => 'Subtask not found'], 404);
            }

            $subtask->delete();

            return response()->json(['message' => 'Subtask deleted']);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
