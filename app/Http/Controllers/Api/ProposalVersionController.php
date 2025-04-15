<?php

namespace App\Http\Controllers\Api;

use App\Models\Proposal;
use App\Models\ProposalVersion;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProposalVersionController extends Controller
{
    public function index(Proposal $proposal)
    {
        return response()->json($proposal->versions);
    }

    public function show(Proposal $proposal, ProposalVersion $version)
    {
        return response()->json($version);
    }

    public function restore(Proposal $proposal, ProposalVersion $version)
    {
        $proposal->content()->update([
            'scope_of_work' => $version->scope_of_work,
            'deliverables' => $version->deliverables,
            'timeline_start' => $version->timeline_start,
            'timeline_end' => $version->timeline_end,
            'pricing' => $version->pricing,
            'payment_schedule' => $version->payment_schedule,
        ]);

        return response()->json(['message' => 'Proposal restored to version', 'version' => $version]);
    }
}
