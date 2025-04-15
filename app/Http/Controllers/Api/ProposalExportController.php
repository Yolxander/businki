<?php

namespace App\Http\Controllers\Api;

use App\Models\Proposal;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\Controller;

class ProposalExportController extends Controller
{
    public function pdf(Proposal $proposal)
    {
        $data = $proposal->load(['content', 'client', 'project']);

        $pdf = Pdf::loadView('exports.proposal', ['proposal' => $data]);
        $filename = 'proposal_' . Str::slug($proposal->title) . '.pdf';

        return $pdf->download($filename);
    }

    public function docx(Proposal $proposal)
    {
        $filename = 'proposal_' . Str::slug($proposal->title) . '.docx';
        $content = view('exports.proposal_docx', ['proposal' => $proposal->load(['content', 'client'])])->render();

        $path = "exports/{$filename}";
        Storage::put($path, $content); // optionally use a Word processing package

        return response()->download(storage_path("app/{$path}"));
    }
}
