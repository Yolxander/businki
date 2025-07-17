<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AIProvider extends Model
{
    use HasFactory;

    protected $table = 'ai_providers';

    protected $fillable = [
        'name',
        'provider_type',
        'api_key',
        'base_url',
        'status',
        'user_id',
        'settings',
        'usage_count',
        'last_used_at'
    ];

    protected $casts = [
        'settings' => 'array',
        'usage_count' => 'integer',
        'last_used_at' => 'datetime'
    ];

    protected $hidden = [
        'api_key'
    ];

    /**
     * Get the user that owns the provider
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the AI models that use this provider
     */
    public function aiModels()
    {
        return $this->hasMany(AIModel::class);
    }

    /**
     * Scope to get only active providers
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Get the masked API key for display
     */
    public function getMaskedApiKeyAttribute()
    {
        if (!$this->api_key) {
            return null;
        }

        $length = strlen($this->api_key);
        if ($length <= 10) {
            return str_repeat('*', $length);
        }

        // Show first 3 and last 3 characters, with 4 asterisks in between (total 10 chars)
        return substr($this->api_key, 0, 3) . '****' . substr($this->api_key, -3);
    }

    /**
     * Increment usage count
     */
    public function incrementUsage()
    {
        $this->increment('usage_count');
        $this->update(['last_used_at' => now()]);
    }

    /**
     * Get available models for this provider type
     */
    public function getAvailableModels()
    {
        $models = [
            'AIMLAPI' => [
                'gpt-4o', 'gpt-4o-mini', 'claude-3-opus', 'claude-3-sonnet',
                'claude-3-haiku', 'gemini-pro', 'gemini-pro-vision'
            ],
            'OpenAI' => [
                'gpt-4o', 'gpt-4o-mini', 'gpt-4-turbo', 'gpt-3.5-turbo'
            ],
            'Anthropic' => [
                'claude-3-opus', 'claude-3-sonnet', 'claude-3-haiku'
            ],
            'Google' => [
                'gemini-pro', 'gemini-pro-vision'
            ]
        ];

        return $models[$this->provider_type] ?? [];
    }
}
