<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TaskIssue extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'title',
        'description',
        'status',
        'fix',
        'code_snippet',
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function codeSnippets()
    {
        return $this->morphMany(CodeSnippet::class, 'snippable');
    }

}
