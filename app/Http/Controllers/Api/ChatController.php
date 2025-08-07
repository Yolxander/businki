<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Services\AIChatService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

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
        $validator = Validator::make($request->all(), [
            'type' => 'nullable|string|in:general,projects,clients,bobbi-flow,calendar,system,analytics',
            'limit' => 'nullable|integer|min:1|max:50'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $type = $request->get('type', 'general');
        $limit = $request->get('limit', 5);

        try {
            $chats = Chat::where('user_id', Auth::id())
                ->where('type', $type)
                ->whereHas('messages', function ($query) {
                    $query->havingRaw('COUNT(*) > 3');
                })
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
                'status' => 'success',
                'data' => [
                    'chats' => $chats,
                    'has_more' => Chat::where('user_id', Auth::id())
                        ->where('type', $type)
                        ->count() > $limit
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching recent chats', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch recent chats'
            ], 500);
        }
    }

    /**
     * Get all chats for a specific type with pagination.
     */
    public function getAllChats(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'type' => 'nullable|string|in:general,projects,clients,bobbi-flow,calendar,system,analytics',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $type = $request->get('type', 'general');
        $page = $request->get('page', 1);
        $perPage = $request->get('per_page', 10);

        try {
            $chats = Chat::where('user_id', Auth::id())
                ->where('type', $type)
                ->whereHas('messages', function ($query) {
                    $query->havingRaw('COUNT(*) > 3');
                })
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

            return response()->json([
                'status' => 'success',
                'data' => $chats
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching all chats', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch chats'
            ], 500);
        }
    }

    /**
     * Get a specific chat with its messages.
     */
    public function getChat(int $chatId): JsonResponse
    {
        try {
            $chat = Chat::where('user_id', Auth::id())
                ->with('messages')
                ->findOrFail($chatId);

            $messages = $chat->messages->map(function ($message) {
                return [
                    'id' => $message->id,
                    'role' => $message->role,
                    'content' => $message->content,
                    'created_at' => $message->created_at->toISOString(),
                    'metadata' => $message->metadata
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => [
                    'chat' => [
                        'id' => $chat->id,
                        'title' => $chat->getDisplayTitle(),
                        'type' => $chat->type,
                        'last_activity_at' => $chat->last_activity_at?->toISOString(),
                    ],
                    'messages' => $messages
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching chat', [
                'error' => $e->getMessage(),
                'chat_id' => $chatId,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Chat not found or access denied'
            ], 404);
        }
    }

    /**
     * Create a new chat.
     */
    public function createChat(Request $request): JsonResponse
    {
        Log::info('Creating chat', [
            'user_id' => Auth::id(),
            'request_data' => $request->all()
        ]);

        $validator = Validator::make($request->all(), [
            'type' => 'required|string|in:general,projects,clients,bobbi-flow,calendar,system,analytics',
            'first_message' => 'nullable|string|max:1000',
            'title' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            Log::error('Chat creation validation failed', [
                'errors' => $validator->errors()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $chat = Chat::create([
                'user_id' => Auth::id(),
                'type' => $request->type,
                'title' => $request->title,
                'first_message' => $request->first_message,
                'last_activity_at' => now(),
            ]);

            Log::info('Chat created successfully', [
                'chat_id' => $chat->id,
                'user_id' => $chat->user_id
            ]);

            // If there's a first message, create the message record and generate AI response
            if ($request->first_message) {
                $userMessage = $chat->messages()->create([
                    'role' => 'user',
                    'content' => $request->first_message,
                ]);

                // Generate AI response for the first message
                $aiResult = $this->aiChatService->processMessage($chat, $request->first_message);

                if ($aiResult['success']) {
                    $aiResponse = $chat->messages()->create([
                        'role' => 'assistant',
                        'content' => $aiResult['response'],
                        'metadata' => $aiResult['metadata'] ?? []
                    ]);
                }

                // Note: Title generation will be handled in sendMessage when message count exceeds 3
                Log::info('Chat created with initial messages', [
                    'chat_id' => $chat->id,
                    'message_count' => $chat->messages()->count()
                ]);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Chat created successfully',
                'data' => [
                    'chat' => [
                        'id' => $chat->id,
                        'title' => $chat->getDisplayTitle(),
                        'type' => $chat->type,
                        'last_activity_at' => $chat->last_activity_at->toISOString(),
                    ]
                ]
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error creating chat', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create chat'
            ], 500);
        }
    }

    /**
     * Send a message to a chat and get AI response.
     */
    public function sendMessage(Request $request, int $chatId): JsonResponse
    {
        Log::info('Sending message', [
            'chat_id' => $chatId,
            'user_id' => Auth::id(),
            'request_data' => $request->all()
        ]);

        $validator = Validator::make($request->all(), [
            'content' => 'required|string|max:4000',
            'role' => 'required|string|in:user,assistant',
            'options' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            Log::error('Message validation failed', [
                'errors' => $validator->errors()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $chat = Chat::where('user_id', Auth::id())->findOrFail($chatId);

            Log::info('Chat found', [
                'chat_id' => $chat->id,
                'chat_type' => $chat->type
            ]);

            // Create the user message
            $userMessage = $chat->messages()->create([
                'role' => $request->role,
                'content' => $request->content,
            ]);

            // Update chat's last activity
            $chat->updateLastActivity();

            // Get current message count after adding the new message
            $messageCount = $chat->messages()->count();

            // Only save chats with more than 3 messages
            if ($messageCount <= 3) {
                // For chats with 3 or fewer messages, don't save them permanently
                // They will be handled as temporary chats
                Log::info('Chat has 3 or fewer messages, treating as temporary', [
                    'chat_id' => $chat->id,
                    'message_count' => $messageCount
                ]);
            } else {
                // For chats with more than 3 messages, ensure they are saved
                // If chat has more than 5 messages and no title, generate AI title
                if ($messageCount > 5 && !$chat->title) {
                    Log::info('Generating AI title for chat with more than 5 messages', [
                        'chat_id' => $chat->id,
                        'message_count' => $messageCount
                    ]);

                    try {
                        $aiTitle = $chat->generateAITitle();
                        $chat->update(['title' => $aiTitle]);

                        Log::info('AI title generated successfully', [
                            'chat_id' => $chat->id,
                            'title' => $aiTitle
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Failed to generate AI title', [
                            'chat_id' => $chat->id,
                            'error' => $e->getMessage()
                        ]);

                        // Fallback to simple title generation
                        if (!$chat->title) {
                            $chat->update(['title' => $chat->generateTitle()]);
                        }
                    }
                } elseif (!$chat->title) {
                    // For chats with 4-5 messages, use simple title generation
                    $chat->update(['title' => $chat->generateTitle()]);
                }
            }

            // If this is a user message, generate AI response
            $aiResponse = null;
            if ($request->role === 'user') {
                $aiResult = $this->aiChatService->processMessage($chat, $request->content, $request->options ?? []);

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
            }

            $response = [
                'status' => 'success',
                'message' => 'Message sent successfully',
                'data' => [
                    'message' => [
                        'id' => $userMessage->id,
                        'role' => $userMessage->role,
                        'content' => $userMessage->content,
                        'created_at' => $userMessage->created_at->toISOString(),
                    ]
                ]
            ];

            // Add AI response if generated
            if ($aiResponse) {
                $response['data']['ai_response'] = [
                    'id' => $aiResponse->id,
                    'role' => $aiResponse->role,
                    'content' => $aiResponse->content,
                    'created_at' => $aiResponse->created_at->toISOString(),
                    'metadata' => $aiResponse->metadata
                ];
            }

            return response()->json($response, 201);
        } catch (\Exception $e) {
            Log::error('Error sending message', [
                'error' => $e->getMessage(),
                'chat_id' => $chatId,
                'user_id' => Auth::id(),
                'message_content' => $request->content
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to send message'
            ], 500);
        }
    }

    /**
     * Get chat type suggestions for the frontend.
     */
    public function getChatSuggestions(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'type' => 'nullable|string|in:general,projects,clients,bobbi-flow,calendar,system,analytics'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $type = $request->get('type', 'general');
            $suggestions = $this->aiChatService->getChatTypeSuggestions($type);

            return response()->json([
                'status' => 'success',
                'data' => [
                    'suggestions' => $suggestions,
                    'chat_type' => $type
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching chat suggestions', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch suggestions'
            ], 500);
        }
    }

    /**
     * Delete a chat.
     */
    public function deleteChat(int $chatId): JsonResponse
    {
        try {
            $chat = Chat::where('user_id', Auth::id())->findOrFail($chatId);
            $chat->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Chat deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting chat', [
                'error' => $e->getMessage(),
                'chat_id' => $chatId,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete chat'
            ], 500);
        }
    }

    /**
     * Get chat analytics and statistics.
     */
    public function getChatStats(Request $request): JsonResponse
    {
        try {
            $userId = Auth::id();

            $stats = [
                'total_chats' => Chat::where('user_id', $userId)->count(),
                'total_messages' => ChatMessage::whereHas('chat', function($query) use ($userId) {
                    $query->where('user_id', $userId);
                })->count(),
                'chats_by_type' => Chat::where('user_id', $userId)
                    ->selectRaw('type, COUNT(*) as count')
                    ->groupBy('type')
                    ->pluck('count', 'type')
                    ->toArray(),
                'recent_activity' => Chat::where('user_id', $userId)
                    ->orderBy('last_activity_at', 'desc')
                    ->limit(5)
                    ->get()
                    ->map(function($chat) {
                        return [
                            'id' => $chat->id,
                            'title' => $chat->getShortTitle(),
                            'type' => $chat->type,
                            'last_activity' => $chat->last_activity_at?->diffForHumans()
                        ];
                    })
            ];

            return response()->json([
                'status' => 'success',
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching chat stats', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch chat statistics'
            ], 500);
        }
    }
}
