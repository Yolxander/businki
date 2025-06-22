<?php

namespace App\Http\Controllers;

use App\Models\Tool;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class ToolController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Log::info('Fetching all tools with relationships');
        $tools = Tool::with(['user'])->get();
        Log::info('Successfully retrieved tools', ['count' => $tools->count()]);
        return response()->json($tools);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Log::info('Creating new tool', ['request_data' => $request->all()]);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:100',
            'status' => 'required|in:active,inactive,trial,cancelled',
            'cost' => 'required|numeric|min:0|max:999999.99',
            'billing_cycle' => 'required|in:monthly,yearly,weekly,one-time',
            'next_billing' => 'required|date',
            'color' => 'nullable|string|max:50',
            'icon' => 'nullable|string|max:10',
            'description' => 'nullable|string',
            'usage' => 'nullable|string',
            'user_id' => 'nullable|exists:users,id'
        ]);

        if ($validator->fails()) {
            Log::warning('Tool creation validation failed', ['errors' => $validator->errors()->toArray()]);
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $tool = Tool::create($request->all());
            Log::info('Tool created successfully', ['tool_id' => $tool->id]);
            return response()->json($tool, 201);
        } catch (\Exception $e) {
            Log::error('Failed to create tool', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Failed to create tool'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Tool $tool)
    {
        Log::info('Fetching tool details', ['tool_id' => $tool->id]);
        return response()->json($tool->load(['user']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Tool $tool)
    {
        Log::info('Updating tool', [
            'tool_id' => $tool->id,
            'request_data' => $request->all()
        ]);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'type' => 'sometimes|required|string|max:100',
            'status' => 'sometimes|required|in:active,inactive,trial,cancelled',
            'cost' => 'sometimes|required|numeric|min:0|max:999999.99',
            'billing_cycle' => 'sometimes|required|in:monthly,yearly,weekly,one-time',
            'next_billing' => 'sometimes|required|date',
            'color' => 'nullable|string|max:50',
            'icon' => 'nullable|string|max:10',
            'description' => 'nullable|string',
            'usage' => 'nullable|string',
            'user_id' => 'nullable|exists:users,id'
        ]);

        if ($validator->fails()) {
            Log::warning('Tool update validation failed', [
                'tool_id' => $tool->id,
                'errors' => $validator->errors()->toArray()
            ]);
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $tool->update($request->all());
            Log::info('Tool updated successfully', ['tool_id' => $tool->id]);
            return response()->json($tool->load(['user']));
        } catch (\Exception $e) {
            Log::error('Failed to update tool', [
                'tool_id' => $tool->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Failed to update tool'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tool $tool)
    {
        Log::info('Deleting tool', ['tool_id' => $tool->id]);
        try {
            $tool->delete();
            Log::info('Tool deleted successfully', ['tool_id' => $tool->id]);
            return response()->json(null, 204);
        } catch (\Exception $e) {
            Log::error('Failed to delete tool', [
                'tool_id' => $tool->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Failed to delete tool'], 500);
        }
    }

    public function getByUser($userId)
    {
        Log::info('Fetching tools by user', ['user_id' => $userId]);
        try {
            $tools = Tool::where('user_id', $userId)->get();
            Log::info('Successfully retrieved tools for user', [
                'user_id' => $userId,
                'tool_count' => $tools->count()
            ]);
            return response()->json($tools);
        } catch (\Exception $e) {
            Log::error('Failed to fetch tools by user', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Failed to fetch tools'], 500);
        }
    }

    public function getByStatus($status)
    {
        Log::info('Fetching tools by status', ['status' => $status]);
        try {
            $tools = Tool::with(['user'])
                ->where('status', $status)
                ->get();
            Log::info('Successfully retrieved tools for status', [
                'status' => $status,
                'tool_count' => $tools->count()
            ]);
            return response()->json($tools);
        } catch (\Exception $e) {
            Log::error('Failed to fetch tools by status', [
                'status' => $status,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Failed to fetch tools'], 500);
        }
    }
}
