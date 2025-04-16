<?php

namespace App\Http\Controllers\Api;

use App\Models\Proposal;
use App\Http\Controllers\Controller;
use App\Services\Proposal\ProposalExportService;

class ProposalExportController extends Controller
{
    protected ProposalExportService $service;

    public function __construct(ProposalExportService $service)
    {
        $this->service = $service;
    }

    public function pdf(Proposal $proposal)
    {
        $pdf = $this->service->exportAsPDF($proposal);
        return response()->json(['url' => $pdf]); // or stream/download depending on setup
    }

    public function docx(Proposal $proposal)
    {
        $docx = $this->service->exportAsDocx($proposal);
        return response()->json(['url' => $docx]); // or stream/download depending on setup
    }
}
