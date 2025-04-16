<?php

namespace App\Http\Controllers\Api;

use App\Models\Proposal;
use App\Models\ProposalAttachment;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ProposalAttachmentController extends Controller
{
    public function index(Proposal $proposal)
    {
        try {
            Log::info('Fetching attachments for proposal', ['proposal_id' => $proposal->id]);
            return response()->json($proposal->attachments);
        } catch (\Exception $e) {
            Log::error('Error fetching attachments', ['proposal_id' => $proposal->id, 'error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to fetch attachments'], 500);
        }
    }

    public function store(Request $request, Proposal $proposal)
    {
        try {
            $validated = $request->validate([
                'file' => 'required|file|max:20480', // 20MB max
                'uploaded_by' => 'required|exists:users,id',
            ]);

            $uploaded = $request->file('file');
            $path = $uploaded->store("proposals/{$proposal->id}/attachments");

            $attachment = $proposal->attachments()->create([
                'file_name' => $uploaded->getClientOriginalName(),
                'file_path' => $path,
                'file_type' => $uploaded->getClientMimeType(),
                'file_size' => $uploaded->getSize(),
                'uploaded_by' => $validated['uploaded_by'],
            ]);

            Log::info('Attachment stored', ['attachment_id' => $attachment->id]);

            return response()->json($attachment, 201);
        } catch (\Exception $e) {
            Log::error('Error storing attachment', ['proposal_id' => $proposal->id, 'error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to store attachment'], 500);
        }
    }

    public function destroy(Proposal $proposal, ProposalAttachment $attachment)
    {
        try {
            Storage::delete($attachment->file_path);
            $attachment->delete();
            Log::info('Attachment deleted', ['attachment_id' => $attachment->id]);
            return response()->json(['message' => 'Attachment deleted']);
        } catch (\Exception $e) {
            Log::error('Error deleting attachment', ['attachment_id' => $attachment->id, 'error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to delete attachment'], 500);
        }
    }

    public function download(Proposal $proposal, ProposalAttachment $attachment)
    {
        try {
            return Storage::download($attachment->file_path, $attachment->file_name);
        } catch (\Exception $e) {
            Log::error('Error downloading attachment', ['attachment_id' => $attachment->id, 'error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to download attachment'], 500);
        }
    }
}
