<?php

namespace App\Services\Proposal;

use App\Models\Proposal;

class ProposalTemplateService
{
    public function convertToTemplate(Proposal $proposal)
    {
        $proposal->update([
            'is_template' => true,
        ]);

        return $proposal;
    }

    public function useTemplate(Proposal $template)
    {
        return Proposal::create([
            'title' => $template->title,
            'client_id' => null,
            'project_id' => null,
            'is_template' => false,
            'status' => 'draft',
        ]);
    }

    public function getAllTemplates()
    {
        return Proposal::where('is_template', true)->get();
    }
}
