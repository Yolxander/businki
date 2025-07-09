<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AIGenerationSetting extends Model
{
    protected $table = 'ai_generation_settings';
    protected $fillable = [
        'name',
        'model',
        'temperature',
        'max_tokens',
        'top_p',
        'frequency_penalty',
        'presence_penalty',
        'system_prompt',
        'additional_parameters',
        'is_active',
        'description',
    ];

    protected $casts = [
        'temperature' => 'decimal:2',
        'max_tokens' => 'integer',
        'top_p' => 'decimal:2',
        'frequency_penalty' => 'decimal:2',
        'presence_penalty' => 'decimal:2',
        'additional_parameters' => 'array',
        'is_active' => 'boolean',
    ];
}
