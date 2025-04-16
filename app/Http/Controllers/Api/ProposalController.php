<?php

namespace App\Http\Controllers\Api;

use App\Models\Proposal;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Proposal\ProposalService;
use App\Services\Proposal\ProposalContentService;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ProposalController extends Controller
{
    protected ProposalService $service;
    protected ProposalContentService $contentService;

    public function __construct(ProposalService $service, ProposalContentService $contentService)
    {
        $this->service = $service;
        $this->contentService = $contentService;
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
     * Store a new proposal and its initial content.
     */
    public function store(Request $request)
    {
        try {
            Log::info('Request', ['raw' => $request->all()]);

            // Validate the main proposal fields
            $proposalData = $request->validate([
                'title' => 'required|string|max:255',
                'client_id' => 'required|exists:clients,id',
                'project_id' => 'nullable|exists:projects,id',
                'status' => 'nullable|in:draft,sent,accepted,rejected',
                'is_template' => 'nullable|boolean',
            ]);

            $proposalData['status'] = $proposalData['status'] ?? 'draft';

            // Extract and validate nested content fields
            $contentInput = $request->input('content', []);
            $contentData = validator($contentInput, [
                'scope_of_work' => 'nullable|string',
                'deliverables' => 'nullable',
                'timeline_start' => 'nullable|date',
                'timeline_end' => 'nullable|date|after_or_equal:timeline_start',
                'pricing' => 'nullable',
                'payment_schedule' => 'nullable',
                'signature' => 'nullable',
            ])->validate();

            Log::info('Storing new proposal with content', [
                'proposal' => $proposalData,
                'content' => $contentData,
            ]);

            // Create proposal
            $proposal = $this->service->create($proposalData);

            // Create content linked to proposal
            $this->contentService->create($proposal, $contentData);

            return response()->json($proposal->load('content'), 201);
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
