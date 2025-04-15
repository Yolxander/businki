<?php

namespace App\Http\Controllers\Api;

use App\Models\Proposal;
use App\Models\ProposalAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;

class ProposalAttachmentController extends Controller
{
    public function index(Proposal $proposal)
    {
        return response()->json($proposal->attachments);
    }

    public function store(Request $request, Proposal $proposal)
    {
        $data = $request->validate([
            'file' => 'required|file|max:10240',
            'uploaded_by' => 'required|exists:users,id',
        ]);

        $file = $request->file('file');
        $filename = $file->getClientOriginalName();
        $uuid = (string) Str::uuid();
        $path = "attachments/{$proposal->id}/{$uuid}_{$filename}";

        Storage::put($path, file_get_contents($file));

        $attachment = $proposal->attachments()->create([
            'filename' => $filename,
            'storage_path' => $path,
            'uploaded_by' => $data['uploaded_by'],
        ]);

        return response()->json($attachment, 201);
    }

    public function destroy(Proposal $proposal, ProposalAttachment $attachment)
    {
        Storage::delete($attachment->storage_path);
        $attachment->delete();

        return response()->json(['message' => 'Attachment deleted']);
    }

    public function download(Proposal $proposal, ProposalAttachment $attachment)
    {
        return Storage::download($attachment->storage_path, $attachment->filename);
    }
}
