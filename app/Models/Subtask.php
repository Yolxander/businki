<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Subtask extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'status',
        'completed',
        'task_id',
        'provider_id',
        'code_snippet',
        'language',
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }
}
