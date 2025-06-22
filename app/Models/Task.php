<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'phase_id',
        'title',
        'status',
        'due_date',
        'assigned_to',
        'description',
        'priority',
        'tags',
        'estimated_hours'
    ];

    protected $casts = [
        'tags' => 'array',
        'due_date' => 'date',
        'estimated_hours' => 'decimal:2'
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function getPhaseAttribute()
    {
        if (!$this->phase_id || !$this->project || !$this->project->proposal) {
            return null;
        }

        $timeline = $this->project->proposal->timeline;
        return collect($timeline)->firstWhere('id', $this->phase_id);
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function subtasks()
    {
        return $this->hasMany(Subtask::class);
    }
}
