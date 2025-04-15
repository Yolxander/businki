<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProposalAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'proposal_id',
        'filename',
        'file_path',
        'size',
        'uploaded_by',
    ];

    public function proposal()
    {
        return $this->belongsTo(Proposal::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
