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
    private ClientService $clientService;
    private IntentDetectionService $intentDetectionService;

    public function __construct(
        OpenAIService $openAIService,
        ContextAwareService $contextAwareService,
        AIMLAPIService $aimlapiService,
        ClientService $clientService,
        IntentDetectionService $intentDetectionService
    ) {
        $this->openAIService = $openAIService;
        $this->contextAwareService = $contextAwareService;
        $this->aimlapiService = $aimlapiService;
        $this->clientService = $clientService;
        $this->intentDetectionService = $intentDetectionService;
    }

    /**
     * Process a chat message and generate an AI response
     */
    public function processMessage(Chat $chat, string $userMessage, array $options = []): array
    {
        try {
            // Get previous context from chat history
            $previousContext = $this->getPreviousClientContext($chat);

            // Check for client-related intents first
            $clientIntent = $this->intentDetectionService->detectClientIntent($userMessage, $previousContext);

            if ($clientIntent['type'] === 'client' && $clientIntent['confidence'] >= 0.7) {
                return $this->handleClientIntent($chat, $userMessage, $clientIntent, $previousContext);
            }

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
                    'context_used' => $chatContext['context_summary'] ?? [],
                    'client_intent_detected' => false
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

            'clients' => "You are a client management AI assistant. Help users manage client relationships, track client data, create proposals, and analyze client interactions. Focus on client-related queries and business development. You can also help users create, read, update, delete, and list clients through natural language commands.",

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
            Log::error('OpenAI Response Generation Failed, trying AIML API', [
                'error' => $e->getMessage(),
                'chat_type' => $chatType,
                'model' => $model
            ]);

            // Try AIML API as fallback
            try {
                $aimlResponse = $this->aimlapiService->generateChatCompletion($prompt);

                return [
                    'content' => $aimlResponse['content'] ?? 'I apologize, but I encountered an error generating a response. Please try again or rephrase your question.',
                    'tokens' => 0,
                    'cost' => 0,
                    'model' => 'aiml-api',
                    'fallback' => true
                ];

            } catch (Exception $aimlException) {
                Log::error('AIML API also failed', [
                    'error' => $aimlException->getMessage(),
                    'chat_type' => $chatType
                ]);

                // Final fallback response
                return [
                    'content' => 'I apologize, but I encountered an error generating a response. Please try again or rephrase your question.',
                    'tokens' => 0,
                    'cost' => 0,
                    'model' => 'fallback',
                    'error' => $e->getMessage()
                ];
            }
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
     * Get previous client context from cache
     */
    private function getPreviousClientContext(Chat $chat): array
    {
        $contextKey = "client_context_{$chat->id}";
        $context = Cache::get($contextKey, []);

        // Check if context is still valid (within 5 minutes)
        if (!empty($context) && isset($context['timestamp'])) {
            $age = now()->timestamp - $context['timestamp'];
            if ($age > 300) { // 5 minutes
                Cache::forget($contextKey);
                return [];
            }
        }

        return $context;
    }

    /**
     * Handle client-related intents
     */
    private function handleClientIntent(Chat $chat, string $userMessage, array $intent, array $previousContext = []): array
    {
        try {
            $action = $intent['action'];
            $data = $intent['data'];

            $result = null;
            $response = '';

            switch ($action) {
                case 'create':
                    // If this is a follow-up, merge with previous data
                    if (isset($intent['is_followup']) && $intent['is_followup'] && !empty($previousContext)) {
                        $data = array_merge($previousContext['existing_data'] ?? [], $data);
                    }

                    $result = $this->clientService->createClient($data);

                    // If creation was successful, clear any stored context
                    if ($result['success']) {
                        $this->clearClientContext($chat);
                    }
                    break;

                case 'read':
                    $identifier = $data['email'] ?? $data['name'] ?? null;
                    if ($identifier) {
                        $result = $this->clientService->getClient($identifier);
                    } else {
                        $result = [
                            'success' => false,
                            'message' => 'Please specify which client you would like to find (name or email).',
                            'data' => null
                        ];
                    }
                    break;

                case 'update':
                    $identifier = $data['identifier']['email'] ?? $data['identifier']['name'] ?? null;
                    $updates = $data['updates'] ?? [];

                    if ($identifier && !empty($updates)) {
                        $result = $this->clientService->updateClient($identifier, $updates);
                    } else {
                        $result = [
                            'success' => false,
                            'message' => 'Please specify which client to update and what information to change.',
                            'data' => null
                        ];
                    }
                    break;

                case 'delete':
                    $identifier = $data['email'] ?? $data['name'] ?? null;
                    if ($identifier) {
                        $result = $this->clientService->deleteClient($identifier);
                    } else {
                        $result = [
                            'success' => false,
                            'message' => 'Please specify which client you would like to delete (name or email).',
                            'data' => null
                        ];
                    }
                    break;

                case 'list':
                    $result = $this->clientService->listClients($data);
                    break;

                default:
                    $result = [
                        'success' => false,
                        'message' => 'I understand you want to work with clients, but I\'m not sure what specific action you need. Please try being more specific.',
                        'data' => null
                    ];
            }

            // Format response based on result
            if ($result['success']) {
                $response = $this->formatClientResponse($action, $result);
            } else {
                // Check if this is an interactive response that needs AI processing
                                if (isset($result['requires_interaction']) && $result['requires_interaction']) {
                    // Store context for follow-up
                    $this->storeClientContext($chat, $result, $data);

                    // Use AIML API for interactive responses instead of OpenAI
                    $response = $this->generateInteractiveResponse($userMessage, $result, $action);
                } else {
                    $response = $result['message'];
                }
            }

            // Log the client operation
            $this->logClientOperation($chat, $userMessage, $action, $result);

            return [
                'success' => true,
                'response' => $response,
                'metadata' => [
                    'tokens_used' => 0,
                    'cost' => 0,
                    'model_used' => 'client_service',
                    'chat_type' => $chat->type,
                    'client_intent_detected' => true,
                    'client_action' => $action,
                    'client_result' => $result
                ]
            ];

        } catch (Exception $e) {
            Log::error('Client Intent Handling Error', [
                'error' => $e->getMessage(),
                'chat_id' => $chat->id,
                'intent' => $intent
            ]);

            return [
                'success' => false,
                'response' => 'I encountered an error while processing your client request. Please try again.',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Format client response for user
     */
    private function formatClientResponse(string $action, array $result): string
    {
        $response = $result['message'];

        switch ($action) {
            case 'create':
                if ($result['data']) {
                    $client = $result['data'];
                    $response .= "\n\nClient Details:\n";
                    $response .= "- Name: {$client->full_name}\n";
                    $response .= "- Email: {$client->email}\n";
                    if ($client->phone) {
                        $response .= "- Phone: {$client->phone}\n";
                    }
                    if ($client->company_name) {
                        $response .= "- Company: {$client->company_name}\n";
                    }
                    $response .= "- ID: {$client->id}";
                }
                break;

            case 'read':
                if ($result['data']) {
                    $client = $result['data'];
                    $response .= "\n\nClient Information:\n";
                    $response .= "- Name: {$client->full_name}\n";
                    $response .= "- Email: {$client->email}\n";
                    if ($client->phone) {
                        $response .= "- Phone: {$client->phone}\n";
                    }
                    if ($client->company_name) {
                        $response .= "- Company: {$client->company_name}\n";
                    }
                    if ($client->industry) {
                        $response .= "- Industry: {$client->industry}\n";
                    }
                    if ($client->status) {
                        $response .= "- Status: {$client->status}\n";
                    }
                    $response .= "- Created: " . $client->created_at->format('M j, Y');
                }
                break;

            case 'list':
                if ($result['data'] && $result['data']->count() > 0) {
                    $clients = $result['data'];
                    $response .= "\n\nClient List:\n";
                    foreach ($clients as $client) {
                        $response .= "- {$client->full_name} ({$client->email})";
                        if ($client->company_name) {
                            $response .= " - {$client->company_name}";
                        }
                        $response .= "\n";
                    }
                } else {
                    $response .= "\n\nNo clients found matching your criteria.";
                }
                break;
        }

        return $response;
    }

    /**
     * Generate interactive response using AIML API
     */
    private function generateInteractiveResponse(string $userMessage, array $result, string $action): string
    {
        try {
            $missingFields = $result['missing_fields'] ?? [];
            $fieldNames = [
                'first_name' => 'first name',
                'last_name' => 'last name',
                'email' => 'email address',
                'phone' => 'phone number',
                'company_name' => 'company name'
            ];

            $missingFieldNames = array_map(function($field) use ($fieldNames) {
                return $fieldNames[$field] ?? $field;
            }, $missingFields);

            // Build a simple prompt for AIML
            $prompt = "User wants to create a client but is missing: " . implode(', ', $missingFieldNames) . ". ";
            $prompt .= "User said: \"{$userMessage}\". ";
            $prompt .= "Ask them for the missing information in a friendly, conversational way.";

            // Use AIML API instead of OpenAI
            $response = $this->aimlapiService->generateChatCompletion($prompt);

            return $response['content'] ?? $this->getDefaultInteractiveResponse($missingFieldNames);

        } catch (Exception $e) {
            Log::error('AIML Interactive Response Failed', [
                'error' => $e->getMessage(),
                'user_message' => $userMessage,
                'action' => $action
            ]);

            return $this->getDefaultInteractiveResponse($result['missing_fields'] ?? []);
        }
    }

    /**
     * Get default interactive response when AI fails
     */
    private function getDefaultInteractiveResponse(array $missingFields): string
    {
        $fieldNames = [
            'first_name' => 'first name',
            'last_name' => 'last name',
            'email' => 'email address',
            'phone' => 'phone number',
            'company_name' => 'company name'
        ];

        $missingFieldNames = array_map(function($field) use ($fieldNames) {
            return $fieldNames[$field] ?? $field;
        }, $missingFields);

        if (count($missingFieldNames) === 1) {
            return "I'd be happy to help you create a client! I just need the client's " . $missingFieldNames[0] . ".";
        } else {
            return "Great! To create this client, I need their " . implode(' and ', $missingFieldNames) . ".";
        }
    }

    /**
     * Build interactive prompt for missing fields
     */
    private function buildInteractivePrompt(string $userMessage, array $result, string $action): string
    {
        $missingFields = $result['missing_fields'] ?? [];
        $fieldNames = [
            'first_name' => 'first name',
            'last_name' => 'last name',
            'email' => 'email address',
            'phone' => 'phone number',
            'company_name' => 'company name'
        ];

        $missingFieldNames = array_map(function($field) use ($fieldNames) {
            return $fieldNames[$field] ?? $field;
        }, $missingFields);

        $prompt = "You are helping a user create a client. The user said: \"{$userMessage}\"\n\n";
        $prompt .= "The system detected that the following required information is missing: " . implode(', ', $missingFieldNames) . "\n\n";
        $prompt .= "Please respond naturally and ask the user for the missing information. Be conversational and helpful. ";
        $prompt .= "If only one field is missing, ask specifically for that field. If multiple fields are missing, ask for them in a friendly way.\n\n";
        $prompt .= "Example responses:\n";
        $prompt .= "- \"I'd be happy to help you create a client! I just need the client's first name.\"\n";
        $prompt .= "- \"Great! To create this client, I need their first name and email address.\"\n";
        $prompt .= "- \"I'm ready to create the client. Could you please provide their email address?\"\n\n";
        $prompt .= "Respond naturally:";

        return $prompt;
    }

    /**
     * Log client operation for analytics
     */
    private function logClientOperation(Chat $chat, string $userMessage, string $action, array $result): void
    {
        Log::info('Client Operation via Chat', [
            'chat_id' => $chat->id,
            'user_id' => $chat->user_id,
            'action' => $action,
            'success' => $result['success'],
            'user_message' => $userMessage,
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
                'Create a new client named John Smith with email john@example.com',
                'Show client John Smith',
                'List all clients',
                'Update client email to newemail@example.com',
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

    /**
     * Store client context for follow-up messages
     */
    private function storeClientContext(Chat $chat, array $result, array $data): void
    {
        // Store context in cache or session for this chat
        $contextKey = "client_context_{$chat->id}";
        $context = [
            'missing_fields' => $result['missing_fields'] ?? [],
            'existing_data' => $data,
            'timestamp' => now()->timestamp
        ];

        Cache::put($contextKey, $context, 300); // Store for 5 minutes
    }

    /**
     * Clear client context after successful operation
     */
    private function clearClientContext(Chat $chat): void
    {
        $contextKey = "client_context_{$chat->id}";
        Cache::forget($contextKey);
    }
}
