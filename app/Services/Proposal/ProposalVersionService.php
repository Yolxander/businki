<?php

namespace App\Services\Proposal;

use App\Models\Proposal;
use App\Models\ProposalVersion;

class ProposalVersionService
{
    public function createVersion(Proposal $proposal): ProposalVersion
    {
        return ProposalVersion::create([
            'proposal_id' => $proposal->id,
            'title' => $proposal->title,
            'content_snapshot' => json_encode($proposal->content),
            'created_by' => auth()->id(),
        ]);
    }

    public function getVersions(Proposal $proposal)
    {
        return $proposal->versions()->latest()->get();
    }

    public function restoreVersion(ProposalVersion $version)
    {
        $proposal = $version->proposal;

        $proposal->update([
            'title' => $version->title,
            'content' => json_decode($version->content_snapshot, true),
        ]);

        return $proposal;
    }
}
