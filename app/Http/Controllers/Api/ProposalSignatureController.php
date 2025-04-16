<?php

namespace App\Http\Controllers\Api;

use App\Models\Proposal;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Proposal\ProposalSignatureService;

class ProposalSignatureController extends Controller
{
    protected ProposalSignatureService $service;

    public function __construct(ProposalSignatureService $service)
    {
        $this->service = $service;
    }

    public function index(Proposal $proposal)
    {
        return response()->json($proposal->signatures);
    }

    public function show(Proposal $proposal, $signatureId)
    {
        $signature = $proposal->signatures()->findOrFail($signatureId);
        return response()->json($signature);
    }

    public function store(Request $request, Proposal $proposal)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'signed_at' => 'nullable|date',
            'signature_data' => 'required|string',
            'ip_address' => 'nullable|ip',
        ]);

        $signature = $this->service->store($proposal, $data);
        return response()->json($signature, 201);
    }
}
