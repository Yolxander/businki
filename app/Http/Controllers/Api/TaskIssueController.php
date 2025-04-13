<?php

namespace App\Http\Controllers\Api;

use App\Models\TaskIssue;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class TaskIssueController extends Controller
{
    // GET /api/tasks/{taskId}/issues
    public function index($taskId)
    {
        try {
            Log::info('Task issue index request received', ['task_id' => $taskId]);

            $issues = TaskIssue::where('task_id', $taskId)->get();

            Log::info('Task issues retrieved successfully', [
                'task_id' => $taskId,
                'count' => $issues->count()
            ]);

            return response()->json($issues);

        } catch (\Exception $e) {
            Log::error('Unexpected error during task issue index', [
                'task_id' => $taskId,
                'error' => $e->getMessage()
            ]);

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
            Log::info('Task issue store request received', [
                'task_id' => $taskId,
                'input' => $request->all()
            ]);

            $validated = $request->validate([
                'title' => 'required|string',
                'description' => 'nullable|string',
                'status' => 'nullable|string',
                'fix' => 'nullable|string',
                'code_snippet' => 'nullable|string',
            ]);

            Log::info('Validation passed', ['validated' => $validated]);

            $issue = TaskIssue::create(array_merge($validated, ['task_id' => $taskId]));

            Log::info('Task issue created successfully', ['issue' => $issue]);

            return response()->json($issue, 201);

        } catch (ValidationException $e) {
            Log::error('Validation failed during task issue store', [
                'task_id' => $taskId,
                'input' => $request->all(),
                'errors' => $e->errors(),
            ]);

            return response()->json([
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Unexpected error during task issue store', [
                'task_id' => $taskId,
                'input' => $request->all(),
                'error' => $e->getMessage()
            ]);

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
            Log::info('Task issue update request received', [
                'issue_id' => $id,
                'input' => $request->all()
            ]);

            $issue = TaskIssue::find($id);

            if (!$issue) {
                Log::warning('Task issue not found during update', ['issue_id' => $id]);
                return response()->json(['message' => 'Issue not found'], 404);
            }

            $validated = $request->validate([
                'title' => 'sometimes|string',
                'description' => 'nullable|string',
                'status' => 'nullable|string',
                'fix' => 'nullable|string',
                'code_snippet' => 'nullable|string',
            ]);

            Log::info('Validation passed', ['validated' => $validated]);

            $issue->update($validated);

            Log::info('Task issue updated successfully', ['issue' => $issue]);

            return response()->json($issue);

        } catch (ValidationException $e) {
            Log::error('Validation failed during task issue update', [
                'issue_id' => $id,
                'input' => $request->all(),
                'errors' => $e->errors(),
            ]);

            return response()->json([
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Unexpected error during task issue update', [
                'issue_id' => $id,
                'input' => $request->all(),
                'error' => $e->getMessage()
            ]);

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
            Log::info('Task issue delete request received', ['issue_id' => $id]);

            $issue = TaskIssue::find($id);

            if (!$issue) {
                Log::warning('Task issue not found during deletion', ['issue_id' => $id]);
                return response()->json(['message' => 'Issue not found'], 404);
            }

            $issue->delete();

            Log::info('Task issue deleted successfully', ['issue_id' => $id]);

            return response()->json(['message' => 'Issue deleted']);

        } catch (\Exception $e) {
            Log::error('Unexpected error during task issue deletion', [
                'issue_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
