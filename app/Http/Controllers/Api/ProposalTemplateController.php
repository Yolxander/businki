<?php

namespace App\Http\Controllers\Api;

use App\Models\Proposal;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Proposal\ProposalTemplateService;

class ProposalTemplateController extends Controller
{
    protected ProposalTemplateService $service;

    public function __construct(ProposalTemplateService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        return response()->json($this->service->allTemplates());
    }

    public function show(Proposal $template)
    {
        return response()->json($template->load('content'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'client_id' => 'required|exists:clients,id',
            'project_id' => 'nullable|exists:projects,id',
        ]);

        $template = $this->service->createTemplate($data);
        return response()->json($template, 201);
    }

    public function update(Request $request, Proposal $template)
    {
        $data = $request->validate([
            'title' => 'sometimes|string|max:255',
        ]);

        $updated = $this->service->updateTemplate($template, $data);
        return response()->json($updated);
    }

    public function destroy(Proposal $template)
    {
        $this->service->deleteTemplate($template);
        return response()->json(['message' => 'Template deleted']);
    }

    public function useTemplate(Proposal $template)
    {
        $proposal = $this->service->useTemplate($template);
        return response()->json($proposal);
    }
}
