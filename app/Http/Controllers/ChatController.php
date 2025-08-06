<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\ChatMessage;
use App\Services\AIChatService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    private AIChatService $aiChatService;

    public function __construct(AIChatService $aiChatService)
    {
        $this->aiChatService = $aiChatService;
    }

    /**
     * Get recent chats for the current user.
     */
    public function getRecentChats(Request $request): JsonResponse
    {
        $type = $request->get('type', 'general');
        $limit = $request->get('limit', 5);

        $chats = Chat::where('user_id', Auth::id())
            ->where('type', $type)
            ->orderBy('last_activity_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($chat) {
                return [
                    'id' => $chat->id,
                    'title' => $chat->getShortTitle(),
                    'full_title' => $chat->getDisplayTitle(),
                    'type' => $chat->type,
                    'last_activity_at' => $chat->last_activity_at?->diffForHumans(),
                    'message_count' => $chat->messages()->count(),
                ];
            });

        return response()->json([
            'chats' => $chats,
            'has_more' => Chat::where('user_id', Auth::id())
                ->where('type', $type)
                ->count() > $limit
        ]);
    }

    /**
     * Get all chats for a specific type with pagination.
     */
    public function getAllChats(Request $request): JsonResponse
    {
        $type = $request->get('type', 'general');
        $page = $request->get('page', 1);
        $perPage = $request->get('per_page', 10);

        $chats = Chat::where('user_id', Auth::id())
            ->where('type', $type)
            ->orderBy('last_activity_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        $chats->getCollection()->transform(function ($chat) {
            return [
                'id' => $chat->id,
                'title' => $chat->getShortTitle(),
                'full_title' => $chat->getDisplayTitle(),
                'type' => $chat->type,
                'last_activity_at' => $chat->last_activity_at?->diffForHumans(),
                'message_count' => $chat->messages()->count(),
            ];
        });

        return response()->json($chats);
    }

    /**
     * Get a specific chat with its messages.
     */
    public function getChat(int $chatId): JsonResponse
    {
        $chat = Chat::where('user_id', Auth::id())
            ->with('messages')
            ->findOrFail($chatId);

        $messages = $chat->messages->map(function ($message) {
            return [
                'id' => $message->id,
                'role' => $message->role,
                'content' => $message->content,
                'created_at' => $message->created_at->toISOString(),
            ];
        });

        return response()->json([
            'chat' => [
                'id' => $chat->id,
                'title' => $chat->getDisplayTitle(),
                'type' => $chat->type,
                'last_activity_at' => $chat->last_activity_at?->toISOString(),
            ],
            'messages' => $messages
        ]);
    }

    /**
     * Create a new chat.
     */
    public function createChat(Request $request): JsonResponse
    {
        // Debug logging
        Log::info('Chat creation attempt', [
            'user_id' => Auth::id(),
            'type' => $request->type,
            'first_message' => $request->first_message,
            'authenticated' => Auth::check()
        ]);

        $request->validate([
            'type' => 'required|in:general,projects,clients,bobbi-flow,calendar,system,analytics',
            'first_message' => 'nullable|string',
        ]);

        try {
            $chat = Chat::create([
                'user_id' => Auth::id(),
                'type' => $request->type,
                'first_message' => $request->first_message,
                'last_activity_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Chat creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }

        // If there's a first message, create the message record
        if ($request->first_message) {
            try {
                $chat->messages()->create([
                    'role' => 'user',
                    'content' => $request->first_message,
                ]);
            } catch (\Exception $e) {
                Log::error('Message creation failed', [
                    'error' => $e->getMessage(),
                    'chat_id' => $chat->id
                ]);
                throw $e;
            }
        }

        return response()->json([
            'chat' => [
                'id' => $chat->id,
                'title' => $chat->getDisplayTitle(),
                'type' => $chat->type,
                'last_activity_at' => $chat->last_activity_at->toISOString(),
            ]
        ], 201);
    }

    /**
     * Send a message to a chat and get AI response.
     */
    public function sendMessage(Request $request, int $chatId): JsonResponse
    {
        $request->validate([
            'content' => 'required|string',
            'role' => 'required|in:user,assistant',
        ]);

        $chat = Chat::where('user_id', Auth::id())->findOrFail($chatId);

        // Create the user message
        $userMessage = $chat->messages()->create([
            'role' => $request->role,
            'content' => $request->content,
        ]);

        // Update chat's last activity
        $chat->updateLastActivity();

        // If this is the first message and no title is set, generate one
        if ($chat->messages()->count() === 1 && !$chat->title) {
            $chat->update(['title' => $chat->generateTitle()]);
        }

        // If this is a user message, generate AI response
        $aiResponse = null;
        if ($request->role === 'user') {
            try {
                $aiResult = $this->aiChatService->processMessage($chat, $request->content);
                
                if ($aiResult['success']) {
                    // Create AI response message
                    $aiResponse = $chat->messages()->create([
                        'role' => 'assistant',
                        'content' => $aiResult['response'],
                        'metadata' => $aiResult['metadata'] ?? []
                    ]);
                } else {
                    // Create error response message
                    $aiResponse = $chat->messages()->create([
                        'role' => 'assistant',
                        'content' => $aiResult['response'],
                        'metadata' => ['error' => $aiResult['error'] ?? 'Unknown error']
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('AI response generation failed', [
                    'error' => $e->getMessage(),
                    'chat_id' => $chatId,
                    'user_message' => $request->content
                ]);
                
                // Create fallback response
                $aiResponse = $chat->messages()->create([
                    'role' => 'assistant',
                    'content' => 'I apologize, but I encountered an error processing your request. Please try again.',
                    'metadata' => ['error' => $e->getMessage()]
                ]);
            }
        }

        $response = [
            'message' => [
                'id' => $userMessage->id,
                'role' => $userMessage->role,
                'content' => $userMessage->content,
                'created_at' => $userMessage->created_at->toISOString(),
            ]
        ];

        // Add AI response if generated
        if ($aiResponse) {
            $response['ai_response'] = [
                'id' => $aiResponse->id,
                'role' => $aiResponse->role,
                'content' => $aiResponse->content,
                'created_at' => $aiResponse->created_at->toISOString(),
                'metadata' => $aiResponse->metadata
            ];
        }

        return response()->json($response, 201);
    }

    /**
     * Get chat type suggestions for the frontend.
     */
    public function getChatSuggestions(Request $request): JsonResponse
    {
        $type = $request->get('type', 'general');
        
        $suggestions = $this->aiChatService->getChatTypeSuggestions($type);
        
        return response()->json([
            'suggestions' => $suggestions,
            'chat_type' => $type
        ]);
    }

    /**
     * Delete a chat.
     */
    public function deleteChat(int $chatId): JsonResponse
    {
        $chat = Chat::where('user_id', Auth::id())->findOrFail($chatId);
        $chat->delete();

        return response()->json(['message' => 'Chat deleted successfully']);
    }
}
