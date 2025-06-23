<?php

namespace App\Http\Controllers;

use App\Models\Proposal;
use Illuminate\Http\Request;

class ProposalController extends Controller
{
    public function index()
    {
        try {
            $proposals = auth()->user()->proposals()->with('intakeResponse')->latest()->paginate(10);
            return response()->json([
                'status' => 'success',
                'data' => $proposals
            ]);
        } catch (\Exception $e) {
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
            return response()->json([
                'status' => 'success',
                'data' => [
                    'intake_responses' => $intakeResponses
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to load proposal creation form'
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'intake_response_id' => 'required|exists:intake_responses,id',
                'title' => 'required|string|max:255',
                'scope' => 'required|string',
                'deliverables' => 'required|array',
                'deliverables.*' => 'required|string',
                'timeline' => 'required|array',
                'timeline.*.description' => 'required|string',
                'timeline.*.duration' => 'required|string',
                'timeline.*.price' => 'required|numeric|min:0',
                'status' => 'required|in:draft,sent,accepted,rejected'
            ]);

            $totalPrice = collect($validated['timeline'])->sum('price');

            $proposal = auth()->user()->proposals()->create([
                'intake_response_id' => $validated['intake_response_id'],
                'title' => $validated['title'],
                'scope' => $validated['scope'],
                'deliverables' => $validated['deliverables'],
                'timeline' => $validated['timeline'],
                'price' => $totalPrice,
                'status' => $validated['status']
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Proposal created successfully',
                'data' => $proposal->load('intakeResponse')
            ], 201);
        } catch (\Exception $e) {
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
            $proposal->load('intakeResponse');
            return response()->json([
                'status' => 'success',
                'data' => $proposal
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to view proposal'
            ], 500);
        }
    }

    public function edit(Proposal $proposal)
    {
        try {

            return response()->json([
                'status' => 'success',
                'data' => $proposal->load('intakeResponse')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to access proposal edit form'
            ], 500);
        }
    }

    public function update(Request $request, Proposal $proposal)
    {
        try {


            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'scope' => 'required|string',
                'deliverables' => 'required|array',
                'deliverables.*' => 'required|string',
                'timeline' => 'required|array',
                'timeline.*.description' => 'required|string',
                'timeline.*.duration' => 'required|string',
                'timeline.*.price' => 'required|numeric|min:0',
                'status' => 'required|in:draft,sent,accepted,rejected'
            ]);

            $totalPrice = collect($validated['timeline'])->sum('price');

            $proposal->update([
                'title' => $validated['title'],
                'scope' => $validated['scope'],
                'deliverables' => $validated['deliverables'],
                'timeline' => $validated['timeline'],
                'price' => $totalPrice,
                'status' => $validated['status']
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Proposal updated successfully',
                'data' => $proposal->fresh()->load('intakeResponse')
            ]);
        } catch (\Exception $e) {
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
            $proposal->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Proposal deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete proposal'
            ], 500);
        }
    }

    public function send(Proposal $proposal)
    {
        try {


            // Mock sending functionality
            $proposal->update(['status' => 'sent']);

            return response()->json([
                'status' => 'success',
                'message' => 'Proposal sent successfully',
                'data' => $proposal->fresh()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to send proposal'
            ], 500);
        }
    }

    public function saveDraft(Proposal $proposal)
    {
        try {


            $proposal->update(['status' => 'draft']);

            return response()->json([
                'status' => 'success',
                'message' => 'Proposal saved as draft',
                'data' => $proposal->fresh()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to save proposal as draft'
            ], 500);
        }
    }

    public function preview(Proposal $proposal)
    {
        try {
            $proposal->load('intakeResponse');

            return response()->json([
                'status' => 'success',
                'data' => $proposal
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to preview proposal'
            ], 500);
        }
    }
}
