<?php

namespace App\Services\Proposal;

use App\Models\Proposal;

class ProposalService
{
    public function create(array $data): Proposal
    {
        // Ensure default status
        $data['status'] = $data['status'] ?? 'draft';

        return Proposal::create($data);
    }

    public function update(Proposal $proposal, array $data): Proposal
    {
        $proposal->update($data);
        return $proposal;
    }

    public function delete(Proposal $proposal): bool
    {
        return $proposal->delete();
    }

    public function duplicate(Proposal $proposal): Proposal
    {
        $new = $proposal->replicate();
        $new->title = $new->title . ' (Copy)';
        $new->status = 'draft';
        $new->save();

        return $new;
    }
}
