<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Folder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class FolderController extends Controller
{
    public function index(Request $request)
    {
        try {
            Log::info('Folder index request received', ['query' => $request->all()]);

            $folders = Folder::where('provider_id', $request->provider_id)
                ->when($request->project_id, fn($q) => $q->where('project_id', $request->project_id))
                ->when($request->parent_id, fn($q) => $q->where('parent_id', $request->parent_id))
                ->get();

            Log::info('Folders retrieved successfully', ['count' => $folders->count()]);

            return response()->json($folders);
        } catch (\Exception $e) {
            Log::error('Unexpected error during folder index', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Something went wrong', 'error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            Log::info('Folder store request received', ['input' => $request->all()]);

            $validated = $request->validate([
                'provider_id' => 'required|exists:providers,id',
                'project_id' => 'nullable|exists:projects,id',
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'color' => 'nullable|string',
                'parent_id' => 'nullable|exists:folders,id',
            ]);

            Log::info('Validation passed', ['validated' => $validated]);

            $folder = Folder::create($validated);

            Log::info('Folder created successfully', ['folder' => $folder]);

            return response()->json($folder, 201);
        } catch (ValidationException $e) {
            Log::error('Validation failed during folder store', ['errors' => $e->errors()]);
            return response()->json(['message' => 'Validation error', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Unexpected error during folder store', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Something went wrong', 'error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            Log::info('Folder show request received', ['folder_id' => $id]);

            $folder = Folder::with(['files', 'children'])->find($id);

            if (!$folder) {
                Log::warning('Folder not found', ['folder_id' => $id]);
                return response()->json(['message' => 'Folder not found'], 404);
            }

            Log::info('Folder retrieved successfully', ['folder' => $folder]);

            return response()->json($folder);
        } catch (\Exception $e) {
            Log::error('Unexpected error during folder show', [
                'folder_id' => $id,
                'error' => $e->getMessage()
            ]);
            return response()->json(['message' => 'Something went wrong', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            Log::info('Folder update request received', [
                'folder_id' => $id,
                'input' => $request->all()
            ]);

            $folder = Folder::find($id);

            if (!$folder) {
                Log::warning('Folder not found during update', ['folder_id' => $id]);
                return response()->json(['message' => 'Folder not found'], 404);
            }

            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'description' => 'nullable|string',
                'color' => 'nullable|string',
                'parent_id' => 'nullable|exists:folders,id',
            ]);

            Log::info('Validation passed', ['validated' => $validated]);

            $folder->update($validated);

            Log::info('Folder updated successfully', ['folder' => $folder]);

            return response()->json($folder);
        } catch (ValidationException $e) {
            Log::error('Validation failed during folder update', [
                'folder_id' => $id,
                'errors' => $e->errors(),
                'input' => $request->all()
            ]);
            return response()->json(['message' => 'Validation error', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Unexpected error during folder update', [
                'folder_id' => $id,
                'error' => $e->getMessage(),
                'input' => $request->all()
            ]);
            return response()->json(['message' => 'Something went wrong', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            Log::info('Folder delete request received', ['folder_id' => $id]);

            $folder = Folder::find($id);

            if (!$folder) {
                Log::warning('Folder not found during deletion', ['folder_id' => $id]);
                return response()->json(['message' => 'Folder not found'], 404);
            }

            $folder->delete();

            Log::info('Folder deleted successfully', ['folder_id' => $id]);

            return response()->json(['message' => 'Folder deleted']);
        } catch (\Exception $e) {
            Log::error('Unexpected error during folder deletion', [
                'folder_id' => $id,
                'error' => $e->getMessage()
            ]);
            return response()->json(['message' => 'Something went wrong', 'error' => $e->getMessage()], 500);
        }
    }
}
