<?php

namespace App\Http\Controllers\Api;

use App\Models\Proposal;
use App\Models\ProposalSignature;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProposalSignatureController extends Controller
{
    public function index(Proposal $proposal)
    {
        return response()->json($proposal->signatures);
    }

    public function store(Request $request, Proposal $proposal)
    {
        $data = $request->validate([
            'signed_by' => 'required|exists:users,id',
            'signature_data' => 'required|string', // could be a base64 image string
            'signed_at' => 'nullable|date',
        ]);

        $signature = $proposal->signatures()->create($data);
        return response()->json($signature, 201);
    }

    public function show(Proposal $proposal, ProposalSignature $signature)
    {
        return response()->json($signature);
    }
}
