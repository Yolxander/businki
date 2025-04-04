<?php

namespace App\Http\Controllers\Api;

use App\Models\Task;
use App\Models\Issue;
use App\Models\CodeSnippet;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CodeSnippetController extends Controller
{
    // GET /api/tasks/{taskId}/code-snippets
    public function indexForTask($taskId)
    {
        $task = Task::findOrFail($taskId);
        return response()->json($task->codeSnippets);
    }

    // GET /api/issues/{issueId}/code-snippets
    public function indexForIssue($issueId)
    {
        $issue = Issue::findOrFail($issueId);
        return response()->json($issue->codeSnippets);
    }

    // POST /api/tasks/{taskId}/code-snippets
    public function storeForTask(Request $request, $taskId)
    {
        $task = Task::findOrFail($taskId);

        $validated = $request->validate([
            'title' => 'nullable|string',
            'code' => 'required|string',
            'language' => 'nullable|string',
        ]);

        $snippet = $task->codeSnippets()->create($validated);

        return response()->json($snippet, 201);
    }

    // POST /api/issues/{issueId}/code-snippets
    public function storeForIssue(Request $request, $issueId)
    {
        $issue = Issue::findOrFail($issueId);

        $validated = $request->validate([
            'title' => 'nullable|string',
            'code' => 'required|string',
            'language' => 'nullable|string',
        ]);

        $snippet = $issue->codeSnippets()->create($validated);

        return response()->json($snippet, 201);
    }

    // PUT /api/code-snippets/{id}
    public function update(Request $request, $id)
    {
        $snippet = CodeSnippet::findOrFail($id);

        $validated = $request->validate([
            'title' => 'nullable|string',
            'code' => 'required|string',
            'language' => 'nullable|string',
        ]);

        $snippet->update($validated);

        return response()->json($snippet);
    }

    // DELETE /api/code-snippets/{id}
    public function destroy($id)
    {
        $snippet = CodeSnippet::findOrFail($id);
        $snippet->delete();

        return response()->json(['message' => 'Code snippet deleted']);
    }
}
