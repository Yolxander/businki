<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Proposal extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'project_id',
        'title',
        'status',
        'is_template',
        'version_number',
    ];

    public function content()
    {
        return $this->hasOne(ProposalContent::class);
    }

    public function versions()
    {
        return $this->hasMany(ProposalVersion::class);
    }

    public function comments()
    {
        return $this->hasMany(ProposalComment::class);
    }

    public function attachments()
    {
        return $this->hasMany(ProposalAttachment::class);
    }

    public function signatures()
    {
        return $this->hasMany(ProposalSignature::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
