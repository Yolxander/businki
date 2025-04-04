<?php

namespace App\Http\Controllers\Api;

use App\Models\Collaboration;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CollaborationController extends Controller
{
    // GET /api/collaborations?provider_id=1
    public function index(Request $request)
    {
        $providerId = $request->query('provider_id');

        if (!$providerId) {
            return response()->json(['message' => 'provider_id is required'], 400);
        }

        $collaborations = Collaboration::with(['project'])
            ->where('inviter_id', $providerId)
            ->orWhere('invitee_id', $providerId)
            ->get();

        return response()->json($collaborations);
    }

    // POST /api/collaborations/invite
    public function invite(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'inviter_id' => 'required|exists:providers,id',
            'invitee_id' => 'required|exists:providers,id',
        ]);

        if ($validated['inviter_id'] == $validated['invitee_id']) {
            return response()->json(['message' => 'Cannot invite yourself'], 400);
        }

        // Prevent duplicate invites
        if (Collaboration::where('project_id', $validated['project_id'])
            ->where('invitee_id', $validated['invitee_id'])
            ->exists()) {
            return response()->json(['message' => 'Provider already invited to this project'], 409);
        }

        $collab = Collaboration::create([
            'project_id' => $validated['project_id'],
            'inviter_id' => $validated['inviter_id'],
            'invitee_id' => $validated['invitee_id'],
            'status' => 'invited',
        ]);

        return response()->json(['message' => 'Collaborator invited', 'collaboration' => $collab], 201);
    }

    // PUT /api/collaborations/{id}
    public function update(Request $request, $id)
    {
        $collab = Collaboration::find($id);

        if (!$collab) {
            return response()->json(['message' => 'Collaboration not found'], 404);
        }

        $validated = $request->validate([
            'status' => 'required|in:invited,accepted,declined,removed',
        ]);

        $collab->update($validated);

        return response()->json(['message' => 'Collaboration updated', 'collaboration' => $collab]);
    }
}
