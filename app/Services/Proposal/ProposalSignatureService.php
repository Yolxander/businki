<?php

namespace App\Services\Proposal;

use App\Models\Proposal;
use App\Models\ProposalSignature;

class ProposalSignatureService
{
    public function signProposal(Proposal $proposal, array $data): ProposalSignature
    {
        return ProposalSignature::create([
            'proposal_id' => $proposal->id,
            'signed_by_user_id' => auth()->id(),
            'signature_data' => $data['signature_data'],
            'signed_at' => now(),
        ]);
    }

    public function getSignatures(Proposal $proposal)
    {
        return $proposal->signatures()->latest()->get();
    }
}
