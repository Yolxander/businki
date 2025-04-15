<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProposalContent extends Model
{
    use HasFactory;

    protected $fillable = [
        'proposal_id',
        'scope_of_work',
        'deliverables',
        'timeline_start',
        'timeline_end',
        'pricing',
        'payment_schedule',
    ];

    public function proposal()
    {
        return $this->belongsTo(Proposal::class);
    }
}
