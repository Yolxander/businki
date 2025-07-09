<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AIGenerationLog extends Model
{
    protected $table = 'ai_generation_logs';
    protected $fillable = [
        'generation_type',
        'prompt',
        'response',
        'model',
        'prompt_tokens',
        'completion_tokens',
        'total_tokens',
        'temperature',
        'max_tokens',
        'execution_time_ms',
        'status',
        'error_message',
        'metadata',
        'user_id',
    ];

    protected $casts = [
        'metadata' => 'array',
        'temperature' => 'decimal:2',
        'prompt_tokens' => 'integer',
        'completion_tokens' => 'integer',
        'total_tokens' => 'integer',
        'max_tokens' => 'integer',
        'execution_time_ms' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
