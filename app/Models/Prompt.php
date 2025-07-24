<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prompt extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'project_id',
        'task_id',
        'title',
        'description',
        'content',
        'tags',
        'context',
        'favorite',
    ];

    protected $casts = [
        'tags' => 'array',
        'favorite' => 'boolean',
    ];
}
