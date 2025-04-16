<?php

namespace App\Http\Controllers\Api;

use App\Models\Proposal;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ProposalContentController extends Controller
{
    /**
     * Show content for a given proposal.
     */
    public function show(Proposal $proposal)
    {
        try {
            Log::info('Fetching proposal content', ['proposal_id' => $proposal->id]);

            $content = $proposal->content;

            if (!$content) {
                return response()->json(['message' => 'Proposal content not found'], 404);
            }

            return response()->json($content);
        } catch (\Exception $e) {
            Log::error('Error fetching proposal content', [
                'proposal_id' => $proposal->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json(['message' => 'Something went wrong', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update content for a given proposal.
     */
    public function update(Request $request, Proposal $proposal)
    {
        try {
            Log::info('Updating proposal content', [
                'proposal_id' => $proposal->id,
                'input' => $request->all(),
            ]);

            $validated = $request->validate([
                'scope_of_work' => 'nullable|string',
                'deliverables' => 'nullable|string',
                'timeline_start' => 'nullable|date',
                'timeline_end' => 'nullable|date|after_or_equal:timeline_start',
                'pricing' => 'nullable|string',
                'payment_schedule' => 'nullable|string',
            ]);

            if (!$proposal->content) {
                Log::info('Creating new content for proposal', ['proposal_id' => $proposal->id]);
                $content = $proposal->content()->create($validated);
            } else {
                $proposal->content()->update($validated);
                $content = $proposal->fresh()->content;
            }

            Log::info('Proposal content updated successfully', ['proposal_id' => $proposal->id]);

            return response()->json($content);
        } catch (ValidationException $e) {
            Log::error('Validation failed during proposal content update', [
                'proposal_id' => $proposal->id,
                'errors' => $e->errors(),
            ]);

            return response()->json([
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Unexpected error during proposal content update', [
                'proposal_id' => $proposal->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json(['message' => 'Something went wrong', 'error' => $e->getMessage()], 500);
        }
    }
}
