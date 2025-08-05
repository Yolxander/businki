<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\ChatMessage;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
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
        $request->validate([
            'type' => 'required|in:general,projects,clients,bobbi-flow,calendar,system,analytics',
            'first_message' => 'nullable|string',
        ]);

        $chat = Chat::create([
            'user_id' => Auth::id(),
            'type' => $request->type,
            'first_message' => $request->first_message,
            'last_activity_at' => now(),
        ]);

        // If there's a first message, create the message record
        if ($request->first_message) {
            $chat->messages()->create([
                'role' => 'user',
                'content' => $request->first_message,
            ]);
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
     * Send a message to a chat.
     */
    public function sendMessage(Request $request, int $chatId): JsonResponse
    {
        $request->validate([
            'content' => 'required|string',
            'role' => 'required|in:user,assistant',
        ]);

        $chat = Chat::where('user_id', Auth::id())->findOrFail($chatId);

        // Create the message
        $message = $chat->messages()->create([
            'role' => $request->role,
            'content' => $request->content,
        ]);

        // Update chat's last activity
        $chat->updateLastActivity();

        // If this is the first message and no title is set, generate one
        if ($chat->messages()->count() === 1 && !$chat->title) {
            $chat->update(['title' => $chat->generateTitle()]);
        }

        return response()->json([
            'message' => [
                'id' => $message->id,
                'role' => $message->role,
                'content' => $message->content,
                'created_at' => $message->created_at->toISOString(),
            ]
        ], 201);
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
