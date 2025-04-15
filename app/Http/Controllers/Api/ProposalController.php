<?php

namespace App\Http\Controllers\Api;

use App\Models\Proposal;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Proposal\ProposalService;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ProposalController extends Controller
{
    protected ProposalService $service;

    public function __construct(ProposalService $service)
    {
        $this->service = $service;
    }

    /**
     * List all proposals.
     */
    public function index()
    {
        try {
            Log::info('Fetching all proposals');

            $proposals = Proposal::with('client', 'project')->latest()->get();

            return response()->json($proposals);
        } catch (\Exception $e) {
            Log::error('Failed to fetch proposals', ['error' => $e->getMessage()]);

            return response()->json(['message' => 'Something went wrong', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Store a new proposal.
     */
    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'title' => 'required|string|max:255',
                'client_id' => 'required|exists:clients,id',
                'project_id' => 'nullable|exists:projects,id',
                'status' => 'nullable|string|in:draft,sent,accepted,rejected',
                'is_template' => 'nullable|boolean',
            ]);

            Log::info('Storing new proposal', ['data' => $data]);

            $proposal = $this->service->create($data);

            return response()->json($proposal, 201);
        } catch (ValidationException $e) {
            Log::error('Validation failed during proposal store', ['errors' => $e->errors()]);
            return response()->json(['message' => 'Validation error', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Unexpected error storing proposal', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Something went wrong', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Show a specific proposal.
     */
    public function show(Proposal $proposal)
    {
        try {
            Log::info('Fetching proposal', ['id' => $proposal->id]);

            return response()->json($proposal->load('client', 'project', 'content', 'versions'));
        } catch (\Exception $e) {
            Log::error('Failed to load proposal', ['id' => $proposal->id, 'error' => $e->getMessage()]);
            return response()->json(['message' => 'Something went wrong', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update an existing proposal.
     */
    public function update(Request $request, Proposal $proposal)
    {
        try {
            $data = $request->validate([
                'title' => 'sometimes|string|max:255',
                'status' => 'sometimes|string|in:draft,sent,accepted,rejected',
                'is_template' => 'nullable|boolean',
            ]);

            Log::info('Updating proposal', ['id' => $proposal->id, 'data' => $data]);

            $updated = $this->service->update($proposal, $data);

            return response()->json($updated);
        } catch (ValidationException $e) {
            Log::error('Validation failed during proposal update', [
                'id' => $proposal->id,
                'errors' => $e->errors()
            ]);
            return response()->json(['message' => 'Validation error', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Unexpected error updating proposal', [
                'id' => $proposal->id,
                'error' => $e->getMessage()
            ]);
            return response()->json(['message' => 'Something went wrong', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete a proposal.
     */
    public function destroy(Proposal $proposal)
    {
        try {
            Log::info('Deleting proposal', ['id' => $proposal->id]);

            $this->service->delete($proposal);

            return response()->json(['message' => 'Proposal deleted']);
        } catch (\Exception $e) {
            Log::error('Unexpected error deleting proposal', [
                'id' => $proposal->id,
                'error' => $e->getMessage()
            ]);
            return response()->json(['message' => 'Something went wrong', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Duplicate a proposal.
     */
    public function duplicate(Proposal $proposal)
    {
        try {
            Log::info('Duplicating proposal', ['id' => $proposal->id]);

            $duplicate = $this->service->duplicate($proposal);

            return response()->json($duplicate, 201);
        } catch (\Exception $e) {
            Log::error('Unexpected error duplicating proposal', [
                'id' => $proposal->id,
                'error' => $e->getMessage()
            ]);
            return response()->json(['message' => 'Something went wrong', 'error' => $e->getMessage()], 500);
        }
    }
}
