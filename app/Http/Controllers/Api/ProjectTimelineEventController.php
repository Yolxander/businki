<?php

// app/Http/Controllers/Api/ProjectTimelineEventController.php

namespace App\Http\Controllers\Api;

use App\Models\ProjectTimelineEvent;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProjectTimelineEventController extends Controller
{
    // GET /api/projects/{projectId}/timeline
    public function index($projectId)
    {
        $timeline = ProjectTimelineEvent::where('project_id', $projectId)
            ->orderBy('event_date', 'asc')
            ->get();

        return response()->json($timeline);
    }

    // POST /api/projects/{projectId}/timeline
    public function store(Request $request, $projectId)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'description' => 'nullable|string',
            'event_date' => 'required|date',
            'event_type' => 'nullable|string',
            'created_by' => 'nullable|uuid|exists:users,id',
        ]);

        $timeline = ProjectTimelineEvent::create([
            'project_id' => $projectId,
            ...$validated,
        ]);

        return response()->json($timeline, 201);
    }

    // PUT /api/timeline/{id}
    public function update(Request $request, $id)
    {
        $event = ProjectTimelineEvent::findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|string',
            'description' => 'nullable|string',
            'event_date' => 'sometimes|date',
            'event_type' => 'nullable|string',
        ]);

        $event->update($validated);

        return response()->json($event);
    }

    // DELETE /api/timeline/{id}
    public function destroy($id)
    {
        $event = ProjectTimelineEvent::findOrFail($id);
        $event->delete();

        return response()->json(['message' => 'Timeline event deleted']);
    }
}
