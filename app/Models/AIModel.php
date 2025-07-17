<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AIModel extends Model
{
    use HasFactory;

    protected $table = 'ai_models';

    protected $fillable = [
        'name',
        'provider',
        'model',
        'api_key',
        'base_url',
        'status',
        'user_id',
        'is_default',
        'usage_count',
        'last_used_at',
        'settings'
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'usage_count' => 'integer',
        'last_used_at' => 'datetime',
        'settings' => 'array'
    ];

    protected $hidden = [
        'api_key'
    ];

    /**
     * Get the user that owns the model
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to get only active models
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to get default model
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
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
        if ($length <= 8) {
            return str_repeat('*', $length);
        }

        return substr($this->api_key, 0, 4) . str_repeat('*', $length - 8) . substr($this->api_key, -4);
    }

    /**
     * Increment usage count
     */
    public function incrementUsage()
    {
        $this->increment('usage_count');
        $this->update(['last_used_at' => now()]);
    }
}
