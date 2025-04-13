<?php

namespace App\Http\Controllers\Api;

use App\Models\CalendarEvent;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class CalendarEventController extends Controller
{
    public function index()
    {
        try {
            Log::info('CalendarEvent index request received');

            $events = CalendarEvent::all();

            Log::info('Calendar events retrieved successfully', ['count' => $events->count()]);

            return response()->json($events);
        } catch (\Exception $e) {
            Log::error('Unexpected error during calendar event index', [
                'error' => $e->getMessage()
            ]);
            return response()->json(['message' => 'Something went wrong', 'error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            Log::info('CalendarEvent store request received', ['input' => $request->all()]);

            $validated = $request->validate([
                'client_id' => 'required|exists:clients,id',
                'project_id' => 'required|exists:projects,id',
                'title' => 'required|string|max:255',
                'date' => 'required|date',
                'start_time' => 'required|string',
                'end_time' => 'required|string',
                'location' => 'nullable|string',
                'attendees' => 'nullable|integer',
                'color' => 'nullable|string',
            ]);

            Log::info('Validation passed', ['validated' => $validated]);

            if (!array_key_exists('date', $validated)) {
                Log::warning('Date field missing from validated data');
            }

            $event = CalendarEvent::create($validated);

            Log::info('Calendar event created successfully', ['event' => $event]);

            return response()->json($event, 201);
        } catch (ValidationException $e) {
            Log::error('Validation failed during calendar event store', ['errors' => $e->errors()]);
            return response()->json(['message' => 'Validation error', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Unexpected error during calendar event store', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Something went wrong', 'error' => $e->getMessage()], 500);
        }
    }


    public function show($id)
    {
        try {
            Log::info('CalendarEvent show request received', ['event_id' => $id]);

            $event = CalendarEvent::find($id);

            if (!$event) {
                Log::warning('Calendar event not found', ['event_id' => $id]);
                return response()->json(['message' => 'Event not found'], 404);
            }

            Log::info('Calendar event retrieved successfully', ['event' => $event]);

            return response()->json($event);
        } catch (\Exception $e) {
            Log::error('Unexpected error during calendar event show', [
                'event_id' => $id,
                'error' => $e->getMessage()
            ]);
            return response()->json(['message' => 'Something went wrong', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            Log::info('CalendarEvent update request received', [
                'event_id' => $id,
                'input' => $request->all()
            ]);

            $event = CalendarEvent::find($id);

            if (!$event) {
                Log::warning('Calendar event not found during update', ['event_id' => $id]);
                return response()->json(['message' => 'Event not found'], 404);
            }

            $validated = $request->validate([
                'title' => 'sometimes|string|max:255',
                'date' => 'sometimes|date',
                'start_time' => 'sometimes|string',
                'end_time' => 'sometimes|string',
                'location' => 'nullable|string',
                'attendees' => 'nullable|integer',
                'color' => 'nullable|string',
            ]);

            Log::info('Validation passed', ['validated' => $validated]);

            $event->update($validated);

            Log::info('Calendar event updated successfully', ['event' => $event]);

            return response()->json($event);
        } catch (ValidationException $e) {
            Log::error('Validation failed during calendar event update', [
                'event_id' => $id,
                'errors' => $e->errors(),
                'input' => $request->all()
            ]);
            return response()->json(['message' => 'Validation error', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Unexpected error during calendar event update', [
                'event_id' => $id,
                'error' => $e->getMessage(),
                'input' => $request->all()
            ]);
            return response()->json(['message' => 'Something went wrong', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            Log::info('CalendarEvent destroy request received', ['event_id' => $id]);

            $event = CalendarEvent::find($id);

            if (!$event) {
                Log::warning('Calendar event not found during deletion', ['event_id' => $id]);
                return response()->json(['message' => 'Event not found'], 404);
            }

            $event->delete();

            Log::info('Calendar event deleted successfully', ['event_id' => $id]);

            return response()->json(['message' => 'Event deleted']);
        } catch (\Exception $e) {
            Log::error('Unexpected error during calendar event deletion', [
                'event_id' => $id,
                'error' => $e->getMessage()
            ]);
            return response()->json(['message' => 'Something went wrong', 'error' => $e->getMessage()], 500);
        }
    }
}
