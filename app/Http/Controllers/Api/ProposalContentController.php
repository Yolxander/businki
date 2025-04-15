<?php

namespace App\Http\Controllers\Api;

use App\Models\Proposal;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProposalContentController extends Controller
{
    public function show(Proposal $proposal)
    {
        return response()->json($proposal->content);
    }

    public function update(Request $request, Proposal $proposal)
    {
        $data = $request->validate([
            'scope_of_work' => 'nullable|string',
            'deliverables' => 'nullable|string',
            'timeline_start' => 'nullable|date',
            'timeline_end' => 'nullable|date',
            'pricing' => 'nullable|string',
            'payment_schedule' => 'nullable|string',
        ]);

        $proposal->content()->updateOrCreate([], $data);
        return response()->json($proposal->fresh('content'));
    }
}
