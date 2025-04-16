<?php

namespace App\Http\Controllers\Api;

use App\Models\Proposal;
use App\Models\ProposalComment;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

class ProposalCommentController extends Controller
{
    public function index(Proposal $proposal)
    {
        try {
            Log::info('Fetching comments for proposal', ['proposal_id' => $proposal->id]);
            return response()->json($proposal->comments()->latest()->get());
        } catch (\Exception $e) {
            Log::error('Error fetching proposal comments', ['proposal_id' => $proposal->id, 'error' => $e->getMessage()]);
            return response()->json(['message' => 'Something went wrong'], 500);
        }
    }

    public function store(Request $request, Proposal $proposal)
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
                'comment' => 'required|string',
            ]);

            $comment = $proposal->comments()->create($validated);
            Log::info('Comment added to proposal', ['proposal_id' => $proposal->id, 'comment_id' => $comment->id]);

            return response()->json($comment, 201);
        } catch (\Exception $e) {
            Log::error('Error storing proposal comment', ['proposal_id' => $proposal->id, 'error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to add comment'], 500);
        }
    }

    public function update(Request $request, Proposal $proposal, ProposalComment $comment)
    {
        try {
            $validated = $request->validate([
                'comment' => 'required|string',
            ]);

            $comment->update($validated);
            Log::info('Comment updated', ['comment_id' => $comment->id]);

            return response()->json($comment);
        } catch (\Exception $e) {
            Log::error('Error updating comment', ['comment_id' => $comment->id, 'error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to update comment'], 500);
        }
    }

    public function destroy(Proposal $proposal, ProposalComment $comment)
    {
        try {
            $comment->delete();
            Log::info('Comment deleted', ['comment_id' => $comment->id]);
            return response()->json(['message' => 'Comment deleted']);
        } catch (\Exception $e) {
            Log::error('Error deleting comment', ['comment_id' => $comment->id, 'error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to delete comment'], 500);
        }
    }
}
