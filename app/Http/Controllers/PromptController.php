<?php

namespace App\Http\Controllers;

use App\Models\Prompt;
use Illuminate\Http\Request;

class PromptController extends Controller
{
    public function index()
    {
        return response()->json(Prompt::all());
    }

    public function show($id)
    {
        $prompt = Prompt::findOrFail($id);
        return response()->json($prompt);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'project_id' => 'nullable|exists:projects,id',
            'task_id' => 'nullable|exists:tasks,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'content' => 'required|string',
            'tags' => 'nullable|array',
            'tags.*' => 'string',
            'context' => 'nullable|string|max:255',
            'favorite' => 'boolean',
        ]);
        $prompt = Prompt::create($data);
        return response()->json($prompt, 201);
    }

    public function update(Request $request, $id)
    {
        $prompt = Prompt::findOrFail($id);
        $data = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string|max:255',
            'content' => 'sometimes|required|string',
            'tags' => 'nullable|array',
            'tags.*' => 'string',
            'context' => 'nullable|string|max:255',
            'favorite' => 'boolean',
        ]);
        $prompt->update($data);
        return response()->json($prompt);
    }

    public function destroy($id)
    {
        $prompt = Prompt::findOrFail($id);
        $prompt->delete();
        return response()->json(['message' => 'Prompt deleted']);
    }
}
