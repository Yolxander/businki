<?php

namespace App\Http\Controllers\Api;

use App\Models\Proposal;
use App\Models\ProposalVersion;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\ProposalVersionService;
use Illuminate\Support\Facades\Log;

class ProposalVersionController extends Controller
{
    protected ProposalVersionService $versionService;

    public function __construct(ProposalVersionService $versionService)
    {
        $this->versionService = $versionService;
    }

    /**
     * List all versions for a given proposal.
     */
    public function index(Proposal $proposal)
    {
        Log::info('Fetching proposal versions', ['proposal_id' => $proposal->id]);

        $versions = $proposal->versions;

        return response()->json($versions);
    }

    /**
     * Show a specific version of a proposal.
     */
    public function show(Proposal $proposal, ProposalVersion $version)
    {
        Log::info('Showing specific proposal version', [
            'proposal_id' => $proposal->id,
            'version_id' => $version->id
        ]);

        return response()->json($version);
    }

    /**
     * Restore the content of a proposal to a specific version.
     */
    public function restore(Proposal $proposal, ProposalVersion $version)
    {
        try {
            Log::info('Restoring proposal to version', [
                'proposal_id' => $proposal->id,
                'version_id' => $version->id
            ]);

            $this->versionService->restoreVersion($proposal, $version);

            return response()->json([
                'message' => 'Proposal restored to version successfully.',
                'version' => $version
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to restore version', [
                'proposal_id' => $proposal->id,
                'version_id' => $version->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Failed to restore proposal version.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
