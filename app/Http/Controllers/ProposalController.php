<?php

namespace App\Http\Controllers;

use App\Models\Proposal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProposalController extends Controller
{
    public function index()
    {
        try {
            $proposals = auth()->user()->proposals()->with('intakeResponse')->latest()->paginate(10);
            Log::info('Proposals retrieved successfully', ['user_id' => auth()->id(), 'count' => $proposals->count()]);
            return response()->json([
                'status' => 'success',
                'data' => $proposals
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve proposals', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve proposals'
            ], 500);
        }
    }

    public function create()
    {
        try {
            $intakeResponses = auth()->user()->intakeResponses()->whereDoesntHave('proposal')->get();
            Log::info('Proposal creation form accessed', [
                'user_id' => auth()->id(),
                'available_intakes' => $intakeResponses->count()
            ]);
            return response()->json([
                'status' => 'success',
                'data' => [
                    'intake_responses' => $intakeResponses
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to load proposal creation form', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to load proposal creation form'
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            Log::info('Raw Request', [
                'request' => $request->all(),
            ]);

            $validated = $request->validate([
                'intake_response_id' => 'required|exists:intake_responses,id',
                'scope' => 'required|string',
                'deliverables' => 'required|array',
                'deliverables.*' => 'required|string',
                'timeline' => 'required|array',
                'timeline.*.description' => 'required|string',
                'timeline.*.duration' => 'required|string',
                'timeline.*.price' => 'required|numeric|min:0',
                'status' => 'required|in:draft,sent,accepted,rejected'
            ]);

            // Calculate total price from timeline items
            $totalPrice = collect($validated['timeline'])->sum('price');

            $proposal = auth()->user()->proposals()->create([
                'intake_response_id' => $validated['intake_response_id'],
                'scope' => $validated['scope'],
                'deliverables' => $validated['deliverables'],
                'timeline' => $validated['timeline'],
                'price' => $totalPrice,
                'status' => $validated['status']
            ]);

            Log::info('Proposal created successfully', [
                'user_id' => auth()->id(),
                'proposal_id' => $proposal->id,
                'intake_response_id' => $validated['intake_response_id'],
                'total_price' => $totalPrice
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Proposal created successfully',
                'data' => $proposal->load('intakeResponse')
            ], 201);
        } catch (\Exception $e) {
            Log::error('Failed to create proposal', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'request_data' => $request->except(['scope', 'deliverables', 'timeline'])
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create proposal',
                'errors' => $e->getMessage()
            ], 422);
        }
    }

    public function show(Proposal $proposal)
    {
        try {
            $this->authorize('view', $proposal);
            $proposal->load('intakeResponse');
            Log::info('Proposal viewed', [
                'user_id' => auth()->id(),
                'proposal_id' => $proposal->id
            ]);
            return response()->json([
                'status' => 'success',
                'data' => $proposal
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to view proposal', [
                'user_id' => auth()->id(),
                'proposal_id' => $proposal->id,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to view proposal'
            ], 500);
        }
    }

    public function edit(Proposal $proposal)
    {
        try {
            $this->authorize('update', $proposal);
            Log::info('Proposal edit form accessed', [
                'user_id' => auth()->id(),
                'proposal_id' => $proposal->id
            ]);
            return response()->json([
                'status' => 'success',
                'data' => $proposal->load('intakeResponse')
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to access proposal edit form', [
                'user_id' => auth()->id(),
                'proposal_id' => $proposal->id,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to access proposal edit form'
            ], 500);
        }
    }

    public function update(Request $request, Proposal $proposal)
    {
        try {
            $this->authorize('update', $proposal);

            $validated = $request->validate([
                'scope' => 'required|string',
                'deliverables' => 'required|array',
                'deliverables.*' => 'required|string',
                'timeline' => 'required|array',
                'timeline.*.description' => 'required|string',
                'timeline.*.duration' => 'required|string',
                'timeline.*.price' => 'required|numeric|min:0',
                'status' => 'required|in:draft,sent,accepted,rejected'
            ]);

            // Calculate total price from timeline items
            $totalPrice = collect($validated['timeline'])->sum('price');

            $proposal->update([
                'scope' => $validated['scope'],
                'deliverables' => $validated['deliverables'],
                'timeline' => $validated['timeline'],
                'price' => $totalPrice,
                'status' => $validated['status']
            ]);

            Log::info('Proposal updated successfully', [
                'user_id' => auth()->id(),
                'proposal_id' => $proposal->id,
                'total_price' => $totalPrice
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Proposal updated successfully',
                'data' => $proposal->fresh()->load('intakeResponse')
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update proposal', [
                'user_id' => auth()->id(),
                'proposal_id' => $proposal->id,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update proposal',
                'errors' => $e->getMessage()
            ], 422);
        }
    }

    public function destroy(Proposal $proposal)
    {
        try {
            $this->authorize('delete', $proposal);
            $proposalId = $proposal->id;
            $proposal->delete();

            Log::info('Proposal deleted successfully', [
                'user_id' => auth()->id(),
                'proposal_id' => $proposalId
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Proposal deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to delete proposal', [
                'user_id' => auth()->id(),
                'proposal_id' => $proposal->id,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete proposal'
            ], 500);
        }
    }

    public function send(Proposal $proposal)
    {
        try {
            $this->authorize('update', $proposal);

            // Mock sending functionality
            $proposal->update(['status' => 'sent']);

            Log::info('Proposal sent successfully', [
                'user_id' => auth()->id(),
                'proposal_id' => $proposal->id
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Proposal sent successfully',
                'data' => $proposal->fresh()
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send proposal', [
                'user_id' => auth()->id(),
                'proposal_id' => $proposal->id,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to send proposal'
            ], 500);
        }
    }

    public function saveDraft(Proposal $proposal)
    {
        try {
            $this->authorize('update', $proposal);

            $proposal->update(['status' => 'draft']);

            Log::info('Proposal saved as draft', [
                'user_id' => auth()->id(),
                'proposal_id' => $proposal->id
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Proposal saved as draft',
                'data' => $proposal->fresh()
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to save proposal as draft', [
                'user_id' => auth()->id(),
                'proposal_id' => $proposal->id,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to save proposal as draft'
            ], 500);
        }
    }

    public function preview(Proposal $proposal)
    {
        try {
            $this->authorize('view', $proposal);
            $proposal->load('intakeResponse');

            Log::info('Proposal preview accessed', [
                'user_id' => auth()->id(),
                'proposal_id' => $proposal->id
            ]);

            return response()->json([
                'status' => 'success',
                'data' => $proposal
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to preview proposal', [
                'user_id' => auth()->id(),
                'proposal_id' => $proposal->id,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to preview proposal'
            ], 500);
        }
    }
}
