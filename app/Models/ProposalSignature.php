<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProposalSignature extends Model
{
    use HasFactory;

    protected $fillable = [
        'proposal_id',
        'signed_by',
        'signature_data',
        'signed_at',
        'ip_address',
    ];

    public function proposal()
    {
        return $this->belongsTo(Proposal::class);
    }

    public function signer()
    {
        return $this->belongsTo(User::class, 'signed_by');
    }
}
