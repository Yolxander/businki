<?php

namespace App\Http\Controllers\Api;

use App\Models\Proposal;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Proposal\ProposalService;
use Illuminate\Support\Facades\Log;

class ProposalController extends Controller
{
    protected ProposalService $service;

    public function __construct(ProposalService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        return response()->json(Proposal::latest()->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'client_id' => 'required|exists:clients,id',
            'project_id' => 'nullable|exists:projects,id',
            'status' => 'nullable|string|in:draft,sent,accepted,rejected',
            'is_template' => 'nullable|boolean',
        ]);

        $proposal = $this->service->create($data);
        return response()->json($proposal, 201);
    }

    public function show(Proposal $proposal)
    {
        return response()->json($proposal->load('content', 'versions'));
    }

    public function update(Request $request, Proposal $proposal)
    {
        $data = $request->validate([
            'title' => 'sometimes|string|max:255',
            'status' => 'sometimes|string|in:draft,sent,accepted,rejected',
            'is_template' => 'nullable|boolean',
        ]);

        $updated = $this->service->update($proposal, $data);
        return response()->json($updated);
    }

    public function destroy(Proposal $proposal)
    {
        $this->service->delete($proposal);
        return response()->json(['message' => 'Proposal deleted']);
    }

    public function duplicate(Proposal $proposal)
    {
        $duplicate = $this->service->duplicate($proposal);
        return response()->json($duplicate, 201);
    }
}
