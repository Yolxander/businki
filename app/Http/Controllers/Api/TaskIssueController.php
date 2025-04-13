<?php

namespace App\Http\Controllers\Api;

use App\Models\TaskIssue;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;

class TaskIssueController extends Controller
{
    // GET /api/tasks/{taskId}/issues
    public function index($taskId)
    {
        try {
            $issues = TaskIssue::where('task_id', $taskId)->get();

            return response()->json($issues);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // POST /api/tasks/{taskId}/issues
    public function store(Request $request, $taskId)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string',
                'description' => 'nullable|string',
                'status' => 'nullable|string',
                'fix' => 'nullable|string',
                'code_snippet' => 'nullable|string',
            ]);

            $issue = TaskIssue::create(array_merge($validated, ['task_id' => $taskId]));

            return response()->json($issue, 201);
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

    // PUT /api/issues/{id}
    public function update(Request $request, $id)
    {
        try {
            $issue = TaskIssue::find($id);

            if (!$issue) {
                return response()->json(['message' => 'Issue not found'], 404);
            }

            $validated = $request->validate([
                'title' => 'sometimes|string',
                'description' => 'nullable|string',
                'status' => 'nullable|string',
                'fix' => 'nullable|string',
                'code_snippet' => 'nullable|string',
            ]);

            $issue->update($validated);

            return response()->json($issue);
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

    // DELETE /api/issues/{id}
    public function destroy($id)
    {
        try {
            $issue = TaskIssue::find($id);

            if (!$issue) {
                return response()->json(['message' => 'Issue not found'], 404);
            }

            $issue->delete();

            return response()->json(['message' => 'Issue deleted']);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
