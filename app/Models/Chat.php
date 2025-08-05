<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Chat extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'type',
        'first_message',
        'last_activity_at',
    ];

    protected $casts = [
        'last_activity_at' => 'datetime',
    ];

    /**
     * Get the user that owns the chat.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the messages for the chat.
     */
    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class)->orderBy('created_at', 'asc');
    }

    /**
     * Get the most recent messages for the chat.
     */
    public function recentMessages(int $limit = 10): HasMany
    {
        return $this->hasMany(ChatMessage::class)->orderBy('created_at', 'desc')->limit($limit);
    }

    /**
     * Update the last activity timestamp.
     */
    public function updateLastActivity(): void
    {
        $this->update(['last_activity_at' => now()]);
    }

    /**
     * Generate a title based on the first message.
     */
    public function generateTitle(): string
    {
        if (!$this->first_message) {
            return 'New Chat';
        }

        // Simple title generation - take first 50 characters and add ellipsis if longer
        $title = substr($this->first_message, 0, 50);
        if (strlen($this->first_message) > 50) {
            $title .= '...';
        }

        return $title;
    }

    /**
     * Get the display title (generated if not set).
     */
    public function getDisplayTitle(): string
    {
        return $this->title ?: $this->generateTitle();
    }

    /**
     * Get the short title for display (first 10 characters).
     */
    public function getShortTitle(): string
    {
        $title = $this->getDisplayTitle();
        return substr($title, 0, 10) . (strlen($title) > 10 ? '...' : '');
    }
}
