<?php

namespace App\Services\Proposal;

use App\Models\Proposal;
use App\Models\ProposalContent;

class ProposalContentService
{
    public function create(Proposal $proposal, array $data = []): ProposalContent
    {
        return $proposal->content()->create([
            'scope_of_work' => $data['scope_of_work'] ?? $data['scope'] ?? null,
            'deliverables' => $this->sanitizeJson($data['deliverables'] ?? null),
            'timeline_start' => $data['timeline_start'] ?? ($data['timeline']['start'] ?? null),
            'timeline_end' => $data['timeline_end'] ?? ($data['timeline']['end'] ?? null),
            'pricing' => $this->sanitizeJson($data['pricing'] ?? $data['budget'] ?? null),
            'payment_schedule' => $this->sanitizeJson($data['payment_schedule'] ?? $data['terms'] ?? null),
            'signature' => $this->sanitizeJson($data['signature'] ?? null),
        ]);
    }

    private function sanitizeJson($value): ?string
    {
        if (is_array($value)) {
            return json_encode($value);
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return json_encode($decoded);
            }
        }

        return null;
    }

    /**
     * Update existing proposal content.
     */
    public function update(Proposal $proposal, array $data): ProposalContent
    {
        $proposal->content()->update($data);
        return $proposal->fresh()->content;
    }

    /**
     * Retrieve proposal content.
     */
    public function get(Proposal $proposal): ?ProposalContent
    {
        return $proposal->content;
    }
}
