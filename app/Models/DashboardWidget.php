<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DashboardWidget extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'widget_type',
        'widget_key',
        'title',
        'description',
        'configuration',
        'ai_prompt',
        'ai_response',
        'generation_metadata',
        'is_ai_generated',
        'is_active',
        'position',
    ];

    protected $casts = [
        'configuration' => 'array',
        'ai_prompt' => 'array',
        'ai_response' => 'array',
        'generation_metadata' => 'array',
        'is_ai_generated' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get the user that owns the widget.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get widget types
     */
    public static function getWidgetTypes(): array
    {
        return [
            'quick_stats' => 'Quick Stats',
            'recent_tasks' => 'Recent Tasks',
            'recent_projects' => 'Recent Projects',
            'quick_actions' => 'Quick Actions',
            'recent_proposals' => 'Recent Proposals',
        ];
    }

    /**
     * Scope to get active widgets
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get widgets by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('widget_type', $type);
    }
}
