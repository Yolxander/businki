<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'status',
        'priority',
        'category',
        'due_date',
        'project_id',
        'provider_id',
        'completed',
        'github_repo',
        'tech_stack',
        'code_snippet',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    public function subtasks()
    {
        return $this->hasMany(Subtask::class);
    }
}
