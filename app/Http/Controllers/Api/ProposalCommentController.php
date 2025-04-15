<?php

namespace App\Http\Controllers\Api;

use App\Models\Proposal;
use App\Models\ProposalComment;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProposalCommentController extends Controller
{
    public function index(Proposal $proposal)
    {
        return response()->json($proposal->comments()->latest()->get());
    }

    public function store(Request $request, Proposal $proposal)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'comment' => 'required|string',
        ]);

        $comment = $proposal->comments()->create($data);
        return response()->json($comment, 201);
    }

    public function update(Request $request, Proposal $proposal, ProposalComment $comment)
    {
        $this->authorize('update', $comment); // optional

        $data = $request->validate([
            'comment' => 'required|string',
        ]);

        $comment->update($data);
        return response()->json($comment);
    }

    public function destroy(Proposal $proposal, ProposalComment $comment)
    {
        $this->authorize('delete', $comment); // optional

        $comment->delete();
        return response()->json(['message' => 'Comment deleted']);
    }
}
