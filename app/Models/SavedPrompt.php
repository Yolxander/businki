<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavedPrompt extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'content',
        'category',
        'tags',
        'usage_count',
    ];

    protected $casts = [
        'tags' => 'array',
        'usage_count' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
