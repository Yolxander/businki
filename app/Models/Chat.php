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
     * Generate an AI-powered title based on chat content.
     */
    public function generateAITitle(): string
    {
        try {
            $openAIService = new \App\Services\OpenAIService();

            // Get recent messages to understand the conversation context
            $recentMessages = $this->messages()->orderBy('created_at', 'desc')->limit(10)->get();

            if ($recentMessages->isEmpty()) {
                return $this->generateTitle();
            }

            // Build context from recent messages
            $context = '';
            foreach ($recentMessages->reverse() as $message) {
                $context .= ucfirst($message->role) . ': ' . $message->content . "\n";
            }

            $prompt = "Based on the following conversation, generate a concise, descriptive title (max 60 characters) that captures the main topic or purpose of this chat:\n\n" . $context;

            $response = $openAIService->generateChatCompletionWithParams(
                $prompt,
                config('services.openai.model'),
                0.7,
                100
            );

            $title = trim($response['content'] ?? $this->generateTitle());

            // Remove quotes if present
            $title = trim($title, '"\'');

            // Limit to 60 characters
            if (strlen($title) > 60) {
                $title = substr($title, 0, 57) . '...';
            }

            return $title;
        } catch (\Exception $e) {
            \Log::error('Failed to generate AI title for chat', [
                'chat_id' => $this->id,
                'error' => $e->getMessage()
            ]);

            return $this->generateTitle();
        }
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

    /**
     * Check if this chat should be considered temporary (3 or fewer messages).
     */
    public function isTemporary(): bool
    {
        return $this->messages()->count() <= 3;
    }

    /**
     * Check if this chat should have an AI-generated title (more than 5 messages).
     */
    public function shouldHaveAITitle(): bool
    {
        return $this->messages()->count() > 5;
    }

    /**
     * Clean up temporary chats (those with 3 or fewer messages older than 24 hours).
     */
    public static function cleanupTemporaryChats(): int
    {
        $deletedCount = 0;

        try {
            $temporaryChats = self::whereHas('messages', function ($query) {
                $query->havingRaw('COUNT(*) <= 3');
            })
            ->where('created_at', '<', now()->subHours(24))
            ->get();

            foreach ($temporaryChats as $chat) {
                $chat->delete();
                $deletedCount++;
            }

            \Log::info('Cleaned up temporary chats', [
                'deleted_count' => $deletedCount
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to cleanup temporary chats', [
                'error' => $e->getMessage()
            ]);
        }

        return $deletedCount;
    }
}
