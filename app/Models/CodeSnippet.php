<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CodeSnippet extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'code',
        'language',
        'snippable_id',
        'snippable_type',
    ];

    public function snippable()
    {
        return $this->morphTo();
    }

    protected static function booted()
    {
        static::saving(function ($snippet) {
            if (!$snippet->snippable_type || !$snippet->snippable_id) {
                throw new \Exception('CodeSnippet must belong to either a Task or an Issue.');
            }

            if (!in_array($snippet->snippable_type, [\App\Models\Task::class, \App\Models\Issue::class])) {
                throw new \Exception('Invalid snippable type for CodeSnippet.');
            }
        });
    }
}
