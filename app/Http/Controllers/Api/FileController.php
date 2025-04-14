<?php

namespace App\Http\Controllers\Api;

use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class FileController extends Controller
{
    public function index($folderId)
    {
        $files = File::where('folder_id', $folderId)->get();
        return response()->json($files);
    }

    public function store(Request $request, $folderId)
    {
        $validated = $request->validate([
            'file' => 'required|file|max:10240',
            'provider_id' => 'required|exists:providers,id',
            'project_id' => 'nullable|exists:projects,id',
        ]);

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

        return response()->json($file, 201);
    }

    public function show($id)
    {
        $file = File::findOrFail($id);
        return response()->json($file);
    }

    public function update(Request $request, $id)
    {
        $file = File::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'is_starred' => 'sometimes|boolean',
        ]);

        $file->update($validated);

        return response()->json($file);
    }

    public function destroy($id)
    {
        $file = File::findOrFail($id);
        Storage::delete($file->storage_path);
        $file->delete();

        return response()->json(['message' => 'File deleted']);
    }

    public function download($id)
    {
        $file = File::findOrFail($id);

        return Storage::download($file->storage_path, $file->name);
    }
}
