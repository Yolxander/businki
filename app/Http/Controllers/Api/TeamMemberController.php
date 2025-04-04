<?php

namespace App\Http\Controllers\Api;

use App\Models\Provider;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class TeamMemberController extends Controller
{
    // GET /api/team-members?provider_id=1
    public function index(Request $request)
    {
        $providerId = $request->query('provider_id');

        if (!$providerId) {
            return response()->json(['message' => 'provider_id is required'], 400);
        }

        $provider = Provider::find($providerId);

        if (!$provider) {
            return response()->json(['message' => 'Provider not found'], 404);
        }

        $members = $provider->teamMembers()->get();

        return response()->json($members);
    }

    // POST /api/team-members/invite
    public function invite(Request $request)
    {
        $validated = $request->validate([
            'owner_provider_id' => 'required|exists:providers,id',
            'member_provider_id' => 'required|exists:providers,id',
            'role' => 'nullable|in:admin,member',
        ]);

        // Prevent self-invite
        if ($validated['owner_provider_id'] == $validated['member_provider_id']) {
            return response()->json(['message' => 'A provider cannot invite itself'], 400);
        }

        $owner = Provider::find($validated['owner_provider_id']);

        // Check if already invited
        $exists = DB::table('provider_team_members')->where([
            ['owner_provider_id', '=', $validated['owner_provider_id']],
            ['member_provider_id', '=', $validated['member_provider_id']],
        ])->first();

        if ($exists) {
            return response()->json(['message' => 'This provider is already in the team'], 409);
        }

        $owner->teamMembers()->attach($validated['member_provider_id'], [
            'role' => $validated['role'] ?? 'member',
            'status' => 'invited',
        ]);

        return response()->json(['message' => 'Team member invited successfully'], 201);
    }

    // PUT /api/team-members/{id}
    public function update(Request $request, $id)
    {
        $request->validate([
            'owner_provider_id' => 'required|exists:providers,id',
            'status' => 'required|in:invited,accepted,removed',
            'role' => 'nullable|in:admin,member',
        ]);

        $owner = Provider::find($request->owner_provider_id);

        if (!$owner->teamMembers()->where('provider_team_members.id', $id)->exists()) {
            return response()->json(['message' => 'Team member not found'], 404);
        }

        DB::table('provider_team_members')
            ->where('id', $id)
            ->update(array_filter([
                'status' => $request->status,
                'role' => $request->role,
                'updated_at' => now(),
            ]));

        return response()->json(['message' => 'Team member updated']);
    }
}
