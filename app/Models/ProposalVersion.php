<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProposalVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'proposal_id',
        'version_number',
        'content_snapshot',
        'created_by',
    ];

    public function proposal()
    {
        return $this->belongsTo(Proposal::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
