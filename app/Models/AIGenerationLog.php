<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AIGenerationLog extends Model
{
    protected $table = 'ai_generation_logs';
    protected $fillable = [
        'user_id',
        'model_id',
        'prompt',
        'response',
        'tokens_used',
        'cost',
        'template_id',
        'status',
        'execution_time',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'tokens_used' => 'integer',
        'execution_time' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function model(): BelongsTo
    {
        return $this->belongsTo(AIModel::class, 'model_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(PromptTemplate::class, 'template_id');
    }
}
