<?php

namespace App\Http\Controllers\Api;

use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class FileController extends Controller
{
    public function index($folderId)
    {
        try {
            Log::info('File index request received', ['folder_id' => $folderId]);

            $files = File::where('folder_id', $folderId)->get();

            $formatted = $files->map(function ($file) {
                return [
                    'id' => $file->id,
                    'name' => $file->name,
                    'type' => strtolower(\Illuminate\Support\Str::after($file->type, '/')), // e.g., PDF
                    'size' => number_format($file->size / (1024 * 1024), 2) . ' MB',       // e.g., 1.24 MB
                    'storage_path' => $file->storage_path,
                    'is_starred' => (bool) $file->is_starred,
                    'folder_id' => $file->folder_id,
                    'project_id' => $file->project_id,
                    'provider_id' => $file->provider_id,
                    'created_at' => $file->created_at,
                    'updated_at' => $file->updated_at,
                    'deleted_at' => $file->deleted_at,
                ];
            });

            Log::info('Files retrieved successfully', [
                'folder_id' => $folderId,
                'count' => $files->count()
            ]);

            return response()->json($formatted);
        } catch (\Exception $e) {
            Log::error('Unexpected error during file index', [
                'folder_id' => $folderId,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    // GET /api/providers/{providerId}/files
    public function getFilesByProvider($providerId)
    {
        try {
            Log::info('File list by provider request received', ['provider_id' => $providerId]);

            $files = File::where('provider_id', $providerId)->get()->map(function ($file) {
                return [
                    'id' => $file->id,
                    'name' => $file->name,
                    'type' => strtolower(Str::after($file->type, '/')), // e.g. "application/pdf" → "PDF"
                    'size' => number_format($file->size / (1024 * 1024), 2) . ' MB', // bytes → MB
                    'storage_path' => $file->storage_path,
                    'is_starred' => (bool) $file->is_starred,
                    'folder_id' => $file->folder_id,
                    'project_id' => $file->project_id,
                    'provider_id' => $file->provider_id,
                    'created_at' => $file->created_at,
                    'updated_at' => $file->updated_at,
                    'deleted_at' => $file->deleted_at,
                ];
            });

            Log::info('Files retrieved for provider', [
                'provider_id' => $providerId,
                'count' => $files->count()
            ]);

            return response()->json($files);
        } catch (\Exception $e) {
            Log::error('Unexpected error retrieving files by provider', [
                'provider_id' => $providerId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function store(Request $request, $folderId)
    {
        try {
            Log::info('File store request received', [
                'folder_id' => $folderId,
                'input' => $request->all()
            ]);

            $validated = $request->validate([
                'file' => 'required|file|max:10240',
                'provider_id' => 'required|exists:providers,id',
                'project_id' => 'nullable|exists:projects,id',
            ]);

            Log::info('Validation passed', ['validated' => $validated]);

            $uploaded = $request->file('file');
            $filename = $uploaded->getClientOriginalName();
            $extension = $uploaded->getClientOriginalExtension();
            $uuid = Str::uuid();
            $path = "files/{$validated['provider_id']}/{$folderId}/{$uuid}.{$extension}";

            Storage::put($path, file_get_contents($uploaded));

            $file = File::create([
                'folder_id' => $folderId,
                'provider_id' => $validated['provider_id'],
                'project_id' => $validated['project_id'] ?? null,
                'name' => $filename,
                'type' => $uploaded->getMimeType(),
                'size' => $uploaded->getSize(),
                'storage_path' => $path,
                'is_starred' => false,
            ]);

            Log::info('File stored and saved to DB', ['file_id' => $file->id]);

            return response()->json($file, 201);
        } catch (ValidationException $e) {
            Log::error('Validation failed during file upload', ['errors' => $e->errors()]);
            return response()->json(['message' => 'Validation error', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Unexpected error during file store', [
                'folder_id' => $folderId,
                'error' => $e->getMessage()
            ]);
            return response()->json(['message' => 'Something went wrong', 'error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            Log::info('File show request received', ['file_id' => $id]);

            $file = File::find($id);

            if (!$file) {
                Log::warning('File not found', ['file_id' => $id]);
                return response()->json(['message' => 'File not found'], 404);
            }

            $formatted = [
                'id' => $file->id,
                'name' => $file->name,
                'type' => strtoupper(\Illuminate\Support\Str::after($file->type, '/')), // "application/pdf" → "PDF"
                'size' => number_format($file->size / (1024 * 1024), 2) . ' MB', // bytes → MB
                'storage_path' => $file->storage_path,
                'is_starred' => (bool) $file->is_starred,
                'folder_id' => $file->folder_id,
                'project_id' => $file->project_id,
                'provider_id' => $file->provider_id,
                'created_at' => $file->created_at,
                'updated_at' => $file->updated_at,
                'deleted_at' => $file->deleted_at,
            ];

            Log::info('File retrieved successfully', ['file_id' => $id]);

            return response()->json($formatted);
        } catch (\Exception $e) {
            Log::error('Unexpected error during file show', [
                'file_id' => $id,
                'error' => $e->getMessage()
            ]);
            return response()->json(['message' => 'Something went wrong', 'error' => $e->getMessage()], 500);
        }
    }


    public function update(Request $request, $id)
    {
        try {
            Log::info('File update request received', [
                'file_id' => $id,
                'input' => $request->all()
            ]);

            $file = File::find($id);

            if (!$file) {
                Log::warning('File not found during update', ['file_id' => $id]);
                return response()->json(['message' => 'File not found'], 404);
            }

            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'is_starred' => 'sometimes|boolean',
            ]);

            Log::info('Validation passed', ['validated' => $validated]);

            $file->update($validated);

            Log::info('File updated successfully', ['file' => $file]);

            return response()->json($file);
        } catch (ValidationException $e) {
            Log::error('Validation failed during file update', [
                'file_id' => $id,
                'errors' => $e->errors()
            ]);
            return response()->json(['message' => 'Validation error', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Unexpected error during file update', [
                'file_id' => $id,
                'error' => $e->getMessage()
            ]);
            return response()->json(['message' => 'Something went wrong', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            Log::info('File delete request received', ['file_id' => $id]);

            $file = File::find($id);

            if (!$file) {
                Log::warning('File not found during deletion', ['file_id' => $id]);
                return response()->json(['message' => 'File not found'], 404);
            }

            Storage::delete($file->storage_path);
            $file->delete();

            Log::info('File deleted successfully', ['file_id' => $id]);

            return response()->json(['message' => 'File deleted']);
        } catch (\Exception $e) {
            Log::error('Unexpected error during file deletion', [
                'file_id' => $id,
                'error' => $e->getMessage()
            ]);
            return response()->json(['message' => 'Something went wrong', 'error' => $e->getMessage()], 500);
        }
    }

    public function download($id)
    {
        try {
            Log::info('File download request received', ['file_id' => $id]);

            $file = File::find($id);

            if (!$file) {
                Log::warning('File not found during download', ['file_id' => $id]);
                return response()->json(['message' => 'File not found'], 404);
            }

            Log::info('File download initiated', ['file_id' => $id]);

            return Storage::download($file->storage_path, $file->name);
        } catch (\Exception $e) {
            Log::error('Unexpected error during file download', [
                'file_id' => $id,
                'error' => $e->getMessage()
            ]);
            return response()->json(['message' => 'Something went wrong', 'error' => $e->getMessage()], 500);
        }
    }
}
