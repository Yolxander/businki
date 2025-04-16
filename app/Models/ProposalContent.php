<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProposalContent extends Model
{
    protected $fillable = [
        'proposal_id',
        'scope_of_work',
        'deliverables',
        'timeline_start',
        'timeline_end',
        'pricing',
        'payment_schedule',
        'signature',
    ];

    protected $casts = [
        'deliverables' => 'array',
        'pricing' => 'array',
        'payment_schedule' => 'array',
        'signature' => 'array',
        'timeline_start' => 'date',
        'timeline_end' => 'date',
    ];

    public function proposal()
    {
        return $this->belongsTo(Proposal::class);
    }
}
