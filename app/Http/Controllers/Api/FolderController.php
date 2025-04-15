<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Folder;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class FolderController extends Controller
{
    public function index(Request $request)
    {
        try {
            $folders = Folder::where('provider_id', $request->provider_id)
                ->when($request->project_id, fn($q) => $q->where('project_id', $request->project_id))
                ->when($request->parent_id, fn($q) => $q->where('parent_id', $request->parent_id))
                ->with('files')
                ->get();

            return response()->json($folders);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Something went wrong', 'error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'provider_id' => 'required|exists:providers,id',
                'project_id' => 'nullable|exists:projects,id',
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'color' => 'nullable|string',
                'parent_id' => 'nullable|exists:folders,id',
            ]);

            $folder = Folder::create($validated);

            return response()->json($folder, 201);
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Validation error', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Something went wrong', 'error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $folder = Folder::with(['files', 'children'])->find($id);

            if (!$folder) {
                return response()->json(['message' => 'Folder not found'], 404);
            }

            return response()->json($folder);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Something went wrong', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $folder = Folder::find($id);

            if (!$folder) {
                return response()->json(['message' => 'Folder not found'], 404);
            }

            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'description' => 'nullable|string',
                'color' => 'nullable|string',
                'parent_id' => 'nullable|exists:folders,id',
            ]);

            $folder->update($validated);

            return response()->json($folder);
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Validation error', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Something went wrong', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $folder = Folder::find($id);

            if (!$folder) {
                return response()->json(['message' => 'Folder not found'], 404);
            }

            $folder->delete();

            return response()->json(['message' => 'Folder deleted']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Something went wrong', 'error' => $e->getMessage()], 500);
        }
    }
}
