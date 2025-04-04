<?php

namespace App\Http\Controllers\Api;

use App\Models\TaskIssue;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TaskIssueController extends Controller
{
    // GET /api/tasks/{taskId}/issues
    public function index($taskId)
    {
        return TaskIssue::where('task_id', $taskId)->get();
    }

    // POST /api/tasks/{taskId}/issues
    public function store(Request $request, $taskId)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'description' => 'nullable|string',
            'status' => 'nullable|string',
            'fix' => 'nullable|string',
            'code_snippet' => 'nullable|string',
        ]);

        $issue = TaskIssue::create(array_merge($validated, ['task_id' => $taskId]));

        return response()->json($issue, 201);
    }

    // PUT /api/issues/{id}
    public function update(Request $request, $id)
    {
        $issue = TaskIssue::findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|string',
            'description' => 'nullable|string',
            'status' => 'nullable|string',
            'fix' => 'nullable|string',
            'code_snippet' => 'nullable|string',
        ]);

        $issue->update($validated);

        return response()->json($issue);
    }

    // DELETE /api/issues/{id}
    public function destroy($id)
    {
        $issue = TaskIssue::findOrFail($id);
        $issue->delete();

        return response()->json(['message' => 'Issue deleted']);
    }
}
