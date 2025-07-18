<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'proposal_id',
        'title',
        'status',
        'current_phase',
        'kickoff_date',
        'expected_delivery',
        'notes'
    ];

    protected $casts = [
        'kickoff_date' => 'datetime',
        'expected_delivery' => 'datetime',
    ];

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(Proposal::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function contextEngineeringDocuments()
    {
        return $this->hasMany(ContextEngineeringDocument::class);
    }
}
