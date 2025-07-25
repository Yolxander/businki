<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subtask extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'description',
        'status'
    ];

    protected $casts = [
        'task_id' => 'integer'
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }
}
