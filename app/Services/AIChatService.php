<?php

namespace App\Services;

use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Exception;

class AIChatService
{
    private OpenAIService $openAIService;
    private ContextAwareService $contextAwareService;
    private AIMLAPIService $aimlapiService;

    public function __construct(
        OpenAIService $openAIService,
        ContextAwareService $contextAwareService,
        AIMLAPIService $aimlapiService
    ) {
        $this->openAIService = $openAIService;
        $this->contextAwareService = $contextAwareService;
        $this->aimlapiService = $aimlapiService;
    }

    /**
     * Process a chat message and generate an AI response
     */
    public function processMessage(Chat $chat, string $userMessage, array $options = []): array
    {
        try {
            // Get chat context and history
            $chatContext = $this->buildChatContext($chat, $userMessage);
            
            // Build AI prompt based on chat type
            $aiPrompt = $this->buildAIPrompt($chat, $userMessage, $chatContext);
            
            // Generate AI response
            $aiResponse = $this->generateAIResponse($aiPrompt, $chat->type, $options);
            
            // Log the interaction
            $this->logChatInteraction($chat, $userMessage, $aiResponse);
            
            return [
                'success' => true,
                'response' => $aiResponse['content'],
                'metadata' => [
                    'tokens_used' => $aiResponse['tokens'] ?? 0,
                    'cost' => $aiResponse['cost'] ?? 0,
                    'model_used' => $aiResponse['model'] ?? 'unknown',
                    'chat_type' => $chat->type,
                    'context_used' => $chatContext['context_summary'] ?? []
                ]
            ];
            
        } catch (Exception $e) {
            Log::error('AI Chat Service Error', [
                'error' => $e->getMessage(),
                'chat_id' => $chat->id,
                'user_id' => $chat->user_id,
                'chat_type' => $chat->type
            ]);
            
            return [
                'success' => false,
                'response' => 'I apologize, but I encountered an error processing your request. Please try again.',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Build comprehensive chat context
     */
    private function buildChatContext(Chat $chat, string $userMessage): array
    {
        $user = $chat->user()->first();
        $recentMessages = $chat->recentMessages(10)->get()->reverse();
        
        // Get platform and user context
        $this->contextAwareService->refreshUserContext();
        $platformContext = $this->contextAwareService->getPlatformContext();
        $userContext = $this->contextAwareService->getUserContext();
        
        // Build conversation history
        $conversationHistory = [];
        foreach ($recentMessages as $message) {
            $conversationHistory[] = [
                'role' => $message->role,
                'content' => $message->content,
                'timestamp' => $message->created_at->toISOString()
            ];
        }
        
        // Analyze user message for context
        $messageAnalysis = $this->analyzeUserMessage($userMessage, $chat->type);
        
        return [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'permissions' => $userContext['user_permissions'] ?? []
            ],
            'chat' => [
                'id' => $chat->id,
                'type' => $chat->type,
                'title' => $chat->getDisplayTitle(),
                'message_count' => $chat->messages()->count()
            ],
            'platform_context' => $platformContext,
            'user_context' => $userContext,
            'conversation_history' => $conversationHistory,
            'message_analysis' => $messageAnalysis,
            'context_summary' => $this->buildContextSummary($chat->type, $messageAnalysis, $userContext)
        ];
    }

    /**
     * Analyze user message for context and intent
     */
    private function analyzeUserMessage(string $message, string $chatType): array
    {
        $message = strtolower(trim($message));
        
        $analysis = [
            'intent' => 'general_query',
            'entities' => [],
            'keywords' => [],
            'action_required' => false,
            'data_request' => false,
            'context_specific' => false
        ];
        
        // Extract keywords
        $keywords = $this->extractKeywords($message);
        $analysis['keywords'] = $keywords;
        
        // Determine intent based on chat type and message content
        $analysis['intent'] = $this->determineIntent($message, $chatType, $keywords);
        
        // Check for data requests
        if ($this->isDataRequest($message, $keywords)) {
            $analysis['data_request'] = true;
            $analysis['action_required'] = true;
        }
        
        // Check for context-specific requests
        if ($this->isContextSpecific($message, $chatType)) {
            $analysis['context_specific'] = true;
        }
        
        // Extract entities (projects, clients, tasks, etc.)
        $analysis['entities'] = $this->extractEntities($message, $chatType);
        
        return $analysis;
    }

    /**
     * Extract keywords from message
     */
    private function extractKeywords(string $message): array
    {
        $keywords = [];
        
        $keywordPatterns = [
            'clients' => ['client', 'customer', 'contact'],
            'projects' => ['project', 'work', 'job', 'assignment'],
            'tasks' => ['task', 'todo', 'work item', 'ticket'],
            'proposals' => ['proposal', 'quote', 'estimate'],
            'revenue' => ['revenue', 'income', 'money', 'earnings', 'profit'],
            'analytics' => ['analytics', 'report', 'metrics', 'statistics', 'data'],
            'calendar' => ['calendar', 'schedule', 'meeting', 'appointment', 'event'],
            'bobbi_flow' => ['workflow', 'process', 'automation', 'flow']
        ];
        
        foreach ($keywordPatterns as $category => $patterns) {
            foreach ($patterns as $pattern) {
                if (str_contains($message, $pattern)) {
                    $keywords[] = $category;
                    break;
                }
            }
        }
        
        return array_unique($keywords);
    }

    /**
     * Determine user intent
     */
    private function determineIntent(string $message, string $chatType, array $keywords): string
    {
        // Intent patterns
        $intentPatterns = [
            'data_query' => ['show', 'display', 'list', 'find', 'get', 'what', 'how many'],
            'analysis_request' => ['analyze', 'report', 'summary', 'overview', 'trend'],
            'action_request' => ['create', 'add', 'update', 'delete', 'modify', 'change'],
            'help_request' => ['help', 'how to', 'guide', 'tutorial', 'support'],
            'status_check' => ['status', 'progress', 'current', 'latest', 'recent']
        ];
        
        foreach ($intentPatterns as $intent => $patterns) {
            foreach ($patterns as $pattern) {
                if (str_contains($message, $pattern)) {
                    return $intent;
                }
            }
        }
        
        return 'general_query';
    }

    /**
     * Check if message is a data request
     */
    private function isDataRequest(string $message, array $keywords): bool
    {
        $dataRequestPatterns = ['show', 'display', 'list', 'find', 'get', 'what', 'how many'];
        
        foreach ($dataRequestPatterns as $pattern) {
            if (str_contains($message, $pattern)) {
                return true;
            }
        }
        
        return !empty($keywords);
    }

    /**
     * Check if message is context-specific
     */
    private function isContextSpecific(string $message, string $chatType): bool
    {
        $contextSpecificKeywords = [
            'projects' => ['project', 'work', 'job', 'assignment', 'timeline', 'deadline'],
            'clients' => ['client', 'customer', 'contact', 'company', 'business'],
            'analytics' => ['analytics', 'report', 'metrics', 'statistics', 'data', 'performance'],
            'calendar' => ['calendar', 'schedule', 'meeting', 'appointment', 'event', 'time'],
            'bobbi_flow' => ['workflow', 'process', 'automation', 'flow', 'pipeline']
        ];
        
        $keywords = $contextSpecificKeywords[$chatType] ?? [];
        
        foreach ($keywords as $keyword) {
            if (str_contains($message, $keyword)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Extract entities from message
     */
    private function extractEntities(string $message, string $chatType): array
    {
        $entities = [];
        
        // Extract potential entity names (simple pattern matching)
        preg_match_all('/\b[A-Z][a-z]+(?:\s+[A-Z][a-z]+)*\b/', $message, $matches);
        
        if (!empty($matches[0])) {
            $entities['names'] = $matches[0];
        }
        
        // Extract numbers (dates, amounts, etc.)
        preg_match_all('/\d+/', $message, $matches);
        if (!empty($matches[0])) {
            $entities['numbers'] = $matches[0];
        }
        
        return $entities;
    }

    /**
     * Build context summary for AI
     */
    private function buildContextSummary(string $chatType, array $messageAnalysis, array $userContext): array
    {
        $summary = [
            'chat_type' => $chatType,
            'user_intent' => $messageAnalysis['intent'],
            'relevant_keywords' => $messageAnalysis['keywords'],
            'data_request' => $messageAnalysis['data_request'],
            'context_specific' => $messageAnalysis['context_specific']
        ];
        
        // Add chat type specific context
        switch ($chatType) {
            case 'projects':
                $summary['available_data'] = ['projects', 'tasks', 'timelines', 'progress'];
                $summary['suggested_actions'] = ['list_projects', 'show_progress', 'create_project', 'update_status'];
                break;
            case 'clients':
                $summary['available_data'] = ['clients', 'contacts', 'companies', 'proposals'];
                $summary['suggested_actions'] = ['list_clients', 'show_contacts', 'create_client', 'view_proposals'];
                break;
            case 'analytics':
                $summary['available_data'] = ['metrics', 'reports', 'statistics', 'performance'];
                $summary['suggested_actions'] = ['generate_report', 'show_metrics', 'analyze_data', 'create_dashboard'];
                break;
            case 'calendar':
                $summary['available_data'] = ['events', 'meetings', 'schedules', 'deadlines'];
                $summary['suggested_actions'] = ['show_schedule', 'create_event', 'find_meetings', 'check_availability'];
                break;
            case 'bobbi-flow':
                $summary['available_data'] = ['workflows', 'processes', 'automations', 'pipelines'];
                $summary['suggested_actions'] = ['show_workflows', 'create_process', 'analyze_efficiency', 'optimize_flow'];
                break;
            default:
                $summary['available_data'] = ['general', 'mixed'];
                $summary['suggested_actions'] = ['general_assistance', 'context_switch'];
        }
        
        return $summary;
    }

    /**
     * Build AI prompt based on chat type and context
     */
    private function buildAIPrompt(Chat $chat, string $userMessage, array $chatContext): string
    {
        $chatType = $chat->type;
        $user = $chat->user;
        
        // Base system prompt
        $systemPrompt = $this->getSystemPrompt($chatType);
        
        // Build conversation context
        $conversationContext = $this->buildConversationContext($chatContext);
        
        // Build user context
        $userContextPrompt = $this->buildUserContextPrompt($chatContext);
        
        // Build platform context
        $platformContextPrompt = $this->buildPlatformContextPrompt($chatContext);
        
        // Build the complete prompt
        $prompt = $systemPrompt . "\n\n" .
                 $userContextPrompt . "\n\n" .
                 $platformContextPrompt . "\n\n" .
                 $conversationContext . "\n\n" .
                 "User Message: " . $userMessage . "\n\n" .
                 "Please provide a helpful, context-aware response based on the available data and user's request.";
        
        return $prompt;
    }

    /**
     * Get system prompt based on chat type
     */
    private function getSystemPrompt(string $chatType): string
    {
        $prompts = [
            'general' => "You are an AI assistant for a business management platform. Help users with general queries about their business data, projects, clients, and tasks. Be helpful, professional, and context-aware.",
            
            'projects' => "You are a project management AI assistant. Help users manage projects, track progress, create tasks, and analyze project data. Focus on project-related queries and provide actionable insights.",
            
            'clients' => "You are a client management AI assistant. Help users manage client relationships, track client data, create proposals, and analyze client interactions. Focus on client-related queries and business development.",
            
            'analytics' => "You are a business analytics AI assistant. Help users analyze data, generate reports, track metrics, and provide insights. Focus on data analysis, trends, and performance metrics.",
            
            'calendar' => "You are a calendar and scheduling AI assistant. Help users manage their schedule, find meetings, create events, and optimize their time. Focus on scheduling and time management.",
            
            'bobbi-flow' => "You are a workflow automation AI assistant. Help users optimize processes, analyze workflows, and improve efficiency. Focus on process optimization and automation opportunities.",
            
            'system' => "You are a system administration AI assistant. Help users with platform configuration, settings, and technical support. Focus on system-related queries and troubleshooting."
        ];
        
        return $prompts[$chatType] ?? $prompts['general'];
    }

    /**
     * Build conversation context
     */
    private function buildConversationContext(array $chatContext): string
    {
        $history = $chatContext['conversation_history'];
        
        if (empty($history)) {
            return "This is the start of a new conversation.";
        }
        
        $context = "Recent conversation history:\n";
        foreach ($history as $message) {
            $role = $message['role'] === 'user' ? 'User' : 'Assistant';
            $context .= "{$role}: {$message['content']}\n";
        }
        
        return $context;
    }

    /**
     * Build user context prompt
     */
    private function buildUserContextPrompt(array $chatContext): string
    {
        $user = $chatContext['user'];
        $userContext = $chatContext['user_context'];
        
        $prompt = "User Information:\n";
        $prompt .= "- Name: {$user['name']}\n";
        $prompt .= "- User ID: {$user['id']}\n";
        
        if (!empty($userContext['user_stats'])) {
            $prompt .= "- User Statistics: " . json_encode($userContext['user_stats']) . "\n";
        }
        
        if (!empty($userContext['user_permissions'])) {
            $prompt .= "- Permissions: " . json_encode($userContext['user_permissions']) . "\n";
        }
        
        return $prompt;
    }

    /**
     * Build platform context prompt
     */
    private function buildPlatformContextPrompt(array $chatContext): string
    {
        $platformContext = $chatContext['platform_context'];
        
        $prompt = "Platform Context:\n";
        $prompt .= "- Platform Type: " . ($platformContext['platform_description'] ?? 'Business Management Platform') . "\n";
        $prompt .= "- Available Tables: " . implode(', ', array_keys($platformContext['available_tables'] ?? [])) . "\n";
        $prompt .= "- Supported Metrics: " . json_encode($platformContext['supported_metrics'] ?? []) . "\n";
        
        return $prompt;
    }

    /**
     * Generate AI response using appropriate service
     */
    private function generateAIResponse(string $prompt, string $chatType, array $options = []): array
    {
        $temperature = $options['temperature'] ?? 0.7;
        $maxTokens = $options['max_tokens'] ?? 2000;
        $model = $options['model'] ?? config('services.openai.model');
        
        try {
            // Use OpenAI service for general responses
            $response = $this->openAIService->generateChatCompletionWithParams(
                $prompt,
                $model,
                $temperature,
                $maxTokens
            );
            
            return [
                'content' => $response['content'],
                'tokens' => $response['usage']['total_tokens'] ?? 0,
                'cost' => $response['cost'] ?? 0,
                'model' => $model
            ];
            
        } catch (Exception $e) {
            Log::error('AI Response Generation Failed', [
                'error' => $e->getMessage(),
                'chat_type' => $chatType,
                'model' => $model
            ]);
            
            // Fallback response
            return [
                'content' => 'I apologize, but I encountered an error generating a response. Please try again or rephrase your question.',
                'tokens' => 0,
                'cost' => 0,
                'model' => $model,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Log chat interaction for analytics
     */
    private function logChatInteraction(Chat $chat, string $userMessage, array $aiResponse): void
    {
        Log::info('AI Chat Interaction', [
            'chat_id' => $chat->id,
            'user_id' => $chat->user_id,
            'chat_type' => $chat->type,
            'user_message_length' => strlen($userMessage),
            'ai_response_length' => strlen($aiResponse['content']),
            'tokens_used' => $aiResponse['tokens'] ?? 0,
            'cost' => $aiResponse['cost'] ?? 0,
            'model_used' => $aiResponse['model'] ?? 'unknown',
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * Get chat type specific suggestions
     */
    public function getChatTypeSuggestions(string $chatType): array
    {
        $suggestions = [
            'general' => [
                'Show me my recent projects',
                'How many clients do I have?',
                'What tasks are due this week?',
                'Generate a business report'
            ],
            'projects' => [
                'List all active projects',
                'Show project progress',
                'Create a new project',
                'Find overdue projects'
            ],
            'clients' => [
                'List all clients',
                'Show client contact info',
                'Find clients with recent activity',
                'Generate client report'
            ],
            'analytics' => [
                'Show revenue metrics',
                'Generate performance report',
                'Analyze project trends',
                'Create dashboard widget'
            ],
            'calendar' => [
                'Show my schedule',
                'Find available time slots',
                'List upcoming meetings',
                'Check calendar conflicts'
            ],
            'bobbi-flow' => [
                'Show workflow status',
                'Analyze process efficiency',
                'Find automation opportunities',
                'Optimize workflow'
            ]
        ];
        
        return $suggestions[$chatType] ?? $suggestions['general'];
    }
} 