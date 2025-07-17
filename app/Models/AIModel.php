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
        'model',
        'status',
        'user_id',
        'ai_provider_id',
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



    /**
     * Get the user that owns the model
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the AI provider that this model uses
     */
    public function aiProvider()
    {
        return $this->belongsTo(AIProvider::class);
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
     * Increment usage count
     */
    public function incrementUsage()
    {
        $this->increment('usage_count');
        $this->update(['last_used_at' => now()]);
    }
}
