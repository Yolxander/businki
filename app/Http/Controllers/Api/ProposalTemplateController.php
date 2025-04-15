<?php

namespace App\Http\Controllers\Api;

use App\Models\Proposal;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProposalTemplateController extends Controller
{
    public function index()
    {
        $templates = Proposal::where('is_template', true)->latest()->get();
        return response()->json($templates);
    }

    public function create()
    {
        return response()->json(['message' => 'Ready to create a new template.']);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'created_by' => 'required|exists:users,id',
        ]);

        $template = Proposal::create(array_merge($data, [
            'is_template' => true,
            'status' => 'draft',
        ]));

        return response()->json($template, 201);
    }

    public function show(Proposal $template)
    {
        if (!$template->is_template) {
            return response()->json(['message' => 'Not a template'], 400);
        }

        return response()->json($template->load(['content']));
    }

    public function edit(Proposal $template)
    {
        if (!$template->is_template) {
            return response()->json(['message' => 'Not a template'], 400);
        }

        return response()->json($template);
    }

    public function update(Request $request, Proposal $template)
    {
        if (!$template->is_template) {
            return response()->json(['message' => 'Not a template'], 400);
        }

        $data = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
        ]);

        $template->update($data);
        return response()->json($template);
    }

    public function destroy(Proposal $template)
    {
        if (!$template->is_template) {
            return response()->json(['message' => 'Not a template'], 400);
        }

        $template->delete();
        return response()->json(['message' => 'Template deleted']);
    }

    public function useTemplate(Proposal $template)
    {
        if (!$template->is_template) {
            return response()->json(['message' => 'Not a template'], 400);
        }

        $copy = $template->replicate();
        $copy->is_template = false;
        $copy->status = 'draft';
        $copy->save();

        // Optionally clone content too
        if ($template->content) {
            $copy->content()->create($template->content->toArray());
        }

        return response()->json($copy->load('content'));
    }
}
