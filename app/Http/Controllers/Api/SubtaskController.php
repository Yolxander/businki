<?php

namespace App\Http\Controllers\Api;

use App\Models\Subtask;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SubtaskController extends Controller
{
    // GET /api/tasks/{taskId}/subtasks
    public function index($taskId)
    {
        return Subtask::where('task_id', $taskId)->get();
    }

    // POST /api/tasks/{taskId}/subtasks
    public function store(Request $request, $taskId)
    {
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
    }

    // PUT /api/subtasks/{id}
    public function update(Request $request, $id)
    {
        $subtask = Subtask::findOrFail($id);

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
    }

    // DELETE /api/subtasks/{id}
    public function destroy($id)
    {
        $subtask = Subtask::findOrFail($id);
        $subtask->delete();

        return response()->json(['message' => 'Subtask deleted']);
    }
}
