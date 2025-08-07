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
    private AIIntentDetectionService $aiIntentDetectionService;

    public function __construct(
        OpenAIService $openAIService,
        ContextAwareService $contextAwareService,
        AIMLAPIService $aimlapiService,
        ClientService $clientService,
        AIIntentDetectionService $aiIntentDetectionService
    ) {
        $this->openAIService = $openAIService;
        $this->contextAwareService = $contextAwareService;
        $this->aimlapiService = $aimlapiService;
        $this->clientService = $clientService;
        $this->aiIntentDetectionService = $aiIntentDetectionService;
    }

    /**
     * Process a chat message and generate an AI response
     */
    public function processMessage(Chat $chat, string $userMessage, array $options = []): array
    {
        try {
            // Get previous context from chat history
            $previousContext = $this->getPreviousClientContext($chat);

            // Use AI-powered intent detection first
            $aiIntent = $this->aiIntentDetectionService->detectIntent($userMessage, $previousContext);

            // If AI intent detection has high confidence, use it
            if ($aiIntent['confidence'] >= 0.7) {
                // Handle specific intent types
                if ($aiIntent['type'] === 'client') {
                    return $this->handleClientIntent($chat, $userMessage, $aiIntent, $previousContext);
                } elseif ($aiIntent['type'] === 'project') {
                    return $this->handleProjectIntent($chat, $userMessage, $aiIntent, $previousContext);
                } elseif ($aiIntent['type'] === 'task') {
                    return $this->handleTaskIntent($chat, $userMessage, $aiIntent, $previousContext);
                } elseif ($aiIntent['type'] === 'proposal') {
                    return $this->handleProposalIntent($chat, $userMessage, $aiIntent, $previousContext);
                }
            }

            // Check if this is a field response in an existing context
            if (!empty($previousContext) && $this->isFieldResponse($userMessage, $previousContext)) {
                return $this->handleFieldResponse($chat, $userMessage, $previousContext);
            }

            // If AI intent detection has low confidence, continue with general AI response
            // The AIIntentDetectionService already handles rule-based fallback internally

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
                    'ai_intent_detected' => $aiIntent['confidence'] >= 0.7,
                    'intent_type' => $aiIntent['type'] ?? 'none',
                    'intent_confidence' => $aiIntent['confidence'] ?? 0,
                    'client_intent_detected' => ($aiIntent['type'] === 'client')
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

        Log::info('Retrieved client context', [
            'chat_id' => $chat->id,
            'context' => $context
        ]);

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
                    } else if (isset($result['current_field'])) {
                        // If we need more fields, ask for the next one
                        $result = $this->handleSequentialFieldCollection($chat, $result, $data);
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

                case 'field_response':
                    // Handle field response in context
                    return $this->handleFieldResponse($chat, $userMessage, $previousContext);

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
                    $response = $this->generateInteractiveResponse($chat, $userMessage, $result, $action);
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
     * Enhanced AI-driven field collection with context awareness
     */
    private function generateInteractiveResponse(Chat $chat, string $userMessage, array $result, string $action): string
    {
        try {
            $missingFields = $result['missing_fields'] ?? [];
            $existingData = $result['existing_data'] ?? [];
            $currentField = $result['current_field'] ?? null;

            // If no missing fields, we're done
            if (empty($missingFields)) {
                return "All required information has been collected. You're ready to create the client.";
            }

            // Use AI to determine the next field to ask for
            $nextField = $this->determineNextFieldWithAI($userMessage, $missingFields, $existingData, $action);

            if ($nextField) {
                $fieldNames = [
                    'first_name' => 'first name',
                    'last_name' => 'last name',
                    'email' => 'email address',
                    'phone' => 'phone number',
                    'company_name' => 'company name'
                ];

                $fieldName = $fieldNames[$nextField] ?? $nextField;

                // Update the result to track the current field
                $result['current_field'] = $nextField;
                $result['missing_fields'] = array_filter($missingFields, function($field) use ($nextField) {
                    return $field !== $nextField;
                });

                // Store context for next interaction
                $this->storeClientContext($chat, $result, $existingData);

                return "Great! Now what is the client's " . $fieldName . "?";
            }

            // Fallback to asking for the first missing field
            return $this->getDefaultInteractiveResponse($missingFields);

        } catch (Exception $e) {
            Log::error('Enhanced AI Interactive Response Failed', [
                'error' => $e->getMessage(),
                'user_message' => $userMessage,
                'action' => $action
            ]);

            return $this->getDefaultInteractiveResponse($result['missing_fields'] ?? []);
        }
    }

    /**
     * Use AI to determine the next field to ask for based on context
     */
    private function determineNextFieldWithAI(string $userMessage, array $missingFields, array $existingData, string $action): ?string
    {
        $fieldNames = [
            'first_name' => 'first name',
            'last_name' => 'last name',
            'email' => 'email address',
            'phone' => 'phone number',
            'company_name' => 'company name'
        ];

        // Build context-aware prompt
        $prompt = $this->buildContextAwareFieldPrompt($userMessage, $missingFields, $existingData, $action);

        try {
            // Use AIML API to determine next field
            $response = $this->aimlapiService->generateChatCompletion($prompt);
            $content = $response['content'] ?? '';

            // Extract field name from AI response
            $nextField = $this->extractFieldFromAIResponse($content, $missingFields, $fieldNames);

            if ($nextField) {
                Log::info('AI determined next field', [
                    'next_field' => $nextField,
                    'missing_fields' => $missingFields,
                    'existing_data' => $existingData
                ]);
                return $nextField;
            }
        } catch (Exception $e) {
            Log::error('AI field determination failed', [
                'error' => $e->getMessage(),
                'missing_fields' => $missingFields
            ]);
        }

        // Fallback to first missing field
        return $missingFields[0] ?? null;
    }

    /**
     * Build context-aware prompt for field determination
     */
    private function buildContextAwareFieldPrompt(string $userMessage, array $missingFields, array $existingData, string $action): string
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

        $prompt = "You are an AI assistant helping to collect client information. ";
        $prompt .= "The user said: \"{$userMessage}\"\n\n";

        if (!empty($existingData)) {
            $prompt .= "Information already collected:\n";
            foreach ($existingData as $field => $value) {
                if (!empty($value)) {
                    $fieldName = $fieldNames[$field] ?? $field;
                    $prompt .= "- {$fieldName}: {$value}\n";
                }
            }
            $prompt .= "\n";
        }

        $prompt .= "Missing required fields: " . implode(', ', $missingFieldNames) . "\n\n";
        $prompt .= "Based on the user's message and the context, determine which field should be asked for next. ";
        $prompt .= "Consider:\n";
        $prompt .= "1. If the user provided information, extract it and identify the corresponding field\n";
        $prompt .= "2. If no information was provided, suggest the most logical next field\n";
        $prompt .= "3. Prioritize fields that are most commonly needed first\n\n";
        $prompt .= "Respond with ONLY the field name (e.g., 'first_name', 'email', etc.) or 'none' if no field should be asked for.";

        return $prompt;
    }

    /**
     * Extract field name from AI response
     */
    private function extractFieldFromAIResponse(string $response, array $missingFields, array $fieldNames): ?string
    {
        $response = strtolower(trim($response));

        // Direct field name match
        foreach ($missingFields as $field) {
            if (str_contains($response, $field) || str_contains($response, str_replace('_', ' ', $field))) {
                return $field;
            }
        }

        // Field name mapping
        $fieldMapping = array_flip($fieldNames);
        foreach ($fieldMapping as $displayName => $fieldName) {
            if (str_contains($response, $displayName)) {
                return $fieldName;
            }
        }

        // Check for common variations
        $variations = [
            'name' => 'first_name',
            'email' => 'email',
            'phone' => 'phone',
            'company' => 'company_name'
        ];

        foreach ($variations as $keyword => $fieldName) {
            if (str_contains($response, $keyword) && in_array($fieldName, $missingFields)) {
                return $fieldName;
            }
        }

        return null;
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

        // Ask for one field at a time, starting with the first missing field
        if (!empty($missingFields)) {
            $firstMissingField = $missingFields[0];
            $fieldName = $fieldNames[$firstMissingField] ?? $firstMissingField;
            return "I'd be happy to help you create a client! What is the client's " . $fieldName . "?";
        }

        return "I'd be happy to help you create a client! What is the client's first name?";
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
            'current_field' => $result['current_field'] ?? null,
            'timestamp' => now()->timestamp
        ];

        Log::info('Storing client context', [
            'chat_id' => $chat->id,
            'context' => $context
        ]);

        Cache::put($contextKey, $context, 300); // Store for 5 minutes
    }

    /**
     * Handle sequential field collection for client creation
     */
    private function handleSequentialFieldCollection(Chat $chat, array $result, array $existingData): array
    {
        $missingFields = $result['missing_fields'] ?? [];
        $currentField = $result['current_field'] ?? null;

        if (empty($missingFields)) {
            return $result;
        }

        // If no current field is set, set it to the first missing field
        if (!$currentField) {
            $currentField = $missingFields[0];
        }

        // Remove the current field from missing fields
        $remainingFields = array_filter($missingFields, function($field) use ($currentField) {
            return $field !== $currentField;
        });

        $fieldNames = [
            'first_name' => 'first name',
            'last_name' => 'last name',
            'email' => 'email address',
            'phone' => 'phone number',
            'company_name' => 'company name'
        ];

        $nextField = $remainingFields[0] ?? null;
        $nextFieldName = $fieldNames[$nextField] ?? $nextField;

        if ($nextField) {
            // Ask for the next field
            $result['message'] = "Great! Now what is the client's " . $nextFieldName . "?";
            $result['missing_fields'] = $remainingFields;
            $result['current_field'] = $nextField;
            $result['existing_data'] = $existingData;
        } else {
            // All fields collected, create the client immediately
            $clientName = ($existingData['first_name'] ?? '') . ' ' . ($existingData['last_name'] ?? '');
            $clientEmail = $existingData['email'] ?? '';

            Log::info('All fields collected, creating client', [
                'existing_data' => $existingData,
                'client_name' => trim($clientName),
                'client_email' => $clientEmail
            ]);

            // Create the client immediately
            $finalResult = $this->clientService->createClient($existingData);

            Log::info('Client creation result', [
                'success' => $finalResult['success'],
                'message' => $finalResult['message'],
                'data' => $finalResult['data'] ?? null
            ]);

            if ($finalResult['success']) {
                $result = $finalResult;
                // Clear the context after successful creation
                $this->clearClientContext($chat);
            } else {
                // If creation failed, return the error
                $result = $finalResult;
            }
        }

        return $result;
    }

    /**
     * Check if message is a response to field collection
     */
    private function isFieldResponse(string $message, array $context): bool
    {
        // Check if we're in a field collection context
        if (isset($context['missing_fields']) || isset($context['current_field'])) {
            // Simple heuristics for field responses
            $message = strtolower(trim($message));

            // Check if it looks like a name, email, phone, or company
            $namePatterns = ['/^[a-z]+$/i', '/^[a-z]+\s+[a-z]+$/i'];
            $emailPatterns = ['/@/', '/\.com/', '/\.org/', '/\.net/'];
            $phonePatterns = ['/\d{3}/', '/\d{10}/', '/\+\d+/'];

            // Check for name patterns
            foreach ($namePatterns as $pattern) {
                if (preg_match($pattern, $message) && strlen($message) > 1) {
                    return true;
                }
            }

            // Check for email patterns
            foreach ($emailPatterns as $pattern) {
                if (preg_match($pattern, $message)) {
                    return true;
                }
            }

            // Check for phone patterns
            foreach ($phonePatterns as $pattern) {
                if (preg_match($pattern, $message)) {
                    return true;
                }
            }

            // Check for company names (multiple words)
            if (str_word_count($message) > 1 && strlen($message) > 3) {
                return true;
            }
        }

        return false;
    }

    /**
     * Handle field response in context
     */
    private function handleFieldResponse(Chat $chat, string $userMessage, array $context): array
    {
        try {
            // Extract data from user response
            $extractedData = $this->extractFieldData($userMessage, $context);

            // Merge with existing data
            $existingData = $context['existing_data'] ?? [];
            $updatedData = array_merge($existingData, $extractedData);

            // Update missing fields
            $missingFields = $context['missing_fields'] ?? [];
            $currentField = $context['current_field'] ?? null;

            // Ensure missingFields is an array with numeric keys
            if (!is_array($missingFields)) {
                $missingFields = [];
            }

            // Remove the field that was just provided
            if ($currentField && isset($extractedData[$currentField])) {
                $missingFields = array_values(array_filter($missingFields, function($field) use ($currentField) {
                    return $field !== $currentField;
                }));
            }

            // If no more missing fields, create the client
            if (empty($missingFields)) {
                $result = $this->clientService->createClient($updatedData);
                $this->clearClientContext($chat);

                return [
                    'success' => true,
                    'response' => $this->formatClientResponse('create', $result),
                    'metadata' => [
                        'client_intent_detected' => true,
                        'client_action' => 'create',
                        'client_result' => $result
                    ]
                ];
            }

            // Use AI to determine next field
            $nextField = $this->determineNextFieldWithAI($userMessage, $missingFields, $updatedData, 'create');

            if ($nextField) {
                $fieldNames = [
                    'first_name' => 'first name',
                    'last_name' => 'last name',
                    'email' => 'email address',
                    'phone' => 'phone number',
                    'company_name' => 'company name'
                ];

                $fieldName = $fieldNames[$nextField] ?? $nextField;

                // Store updated context
                $newContext = [
                    'missing_fields' => $missingFields,
                    'current_field' => $nextField,
                    'existing_data' => $updatedData
                ];
                $this->storeClientContext($chat, $newContext, $updatedData);

                return [
                    'success' => true,
                    'response' => "Great! Now what is the client's " . $fieldName . "?",
                    'metadata' => [
                        'client_intent_detected' => true,
                        'client_action' => 'field_collection',
                        'missing_fields' => $missingFields,
                        'current_field' => $nextField
                    ]
                ];
            }

            // Fallback
            return [
                'success' => true,
                'response' => $this->getDefaultInteractiveResponse($missingFields),
                'metadata' => [
                    'client_intent_detected' => true,
                    'client_action' => 'field_collection'
                ]
            ];

        } catch (Exception $e) {
            Log::error('Field response handling failed', [
                'error' => $e->getMessage(),
                'user_message' => $userMessage,
                'context' => $context
            ]);

            return [
                'success' => false,
                'response' => 'I encountered an error processing your response. Please try again.',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Extract field data from user response
     */
    private function extractFieldData(string $message, array $context): array
    {
        $currentField = $context['current_field'] ?? null;
        $data = [];

        if ($currentField) {
            // Extract data based on current field
            switch ($currentField) {
                case 'first_name':
                    $data['first_name'] = ucfirst(strtolower(trim($message)));
                    break;
                case 'last_name':
                    $data['last_name'] = ucfirst(strtolower(trim($message)));
                    break;
                case 'email':
                    $data['email'] = trim($message);
                    break;
                case 'phone':
                    $data['phone'] = trim($message);
                    break;
                case 'company_name':
                    $data['company_name'] = trim($message);
                    break;
            }
        }

        return $data;
    }

    /**
     * Clear client context after successful operation
     */
    private function clearClientContext(Chat $chat): void
    {
        $contextKey = "client_context_{$chat->id}";
        Cache::forget($contextKey);
    }

    /**
     * Handle project-related intents
     */
    private function handleProjectIntent(Chat $chat, string $userMessage, array $intent, array $previousContext = []): array
    {
        try {
            $action = $intent['action'] ?? 'none';
            $entities = $intent['entities'] ?? [];

            switch ($action) {
                case 'create':
                    return [
                        'success' => true,
                        'response' => "I'll help you create a new project. Please provide the project details like name, description, and timeline.",
                        'metadata' => [
                            'intent_type' => 'project',
                            'action' => 'create',
                            'confidence' => $intent['confidence'],
                            'entities' => $entities,
                            'requires_followup' => true
                        ]
                    ];

                case 'read':
                case 'list':
                    return [
                        'success' => true,
                        'response' => "I'll help you find project information. Let me search for projects that match your criteria.",
                        'metadata' => [
                            'intent_type' => 'project',
                            'action' => $action,
                            'confidence' => $intent['confidence'],
                            'entities' => $entities
                        ]
                    ];

                default:
                    return [
                        'success' => true,
                        'response' => "I understand you're asking about projects. How can I help you with project management?",
                        'metadata' => [
                            'intent_type' => 'project',
                            'action' => 'none',
                            'confidence' => $intent['confidence'],
                            'entities' => $entities
                        ]
                    ];
            }
        } catch (Exception $e) {
            Log::error('Project intent handling failed', [
                'error' => $e->getMessage(),
                'intent' => $intent
            ]);

            return [
                'success' => false,
                'response' => 'I encountered an error while processing your project request. Please try again.',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Handle task-related intents
     */
    private function handleTaskIntent(Chat $chat, string $userMessage, array $intent, array $previousContext = []): array
    {
        try {
            $action = $intent['action'] ?? 'none';
            $entities = $intent['entities'] ?? [];

            switch ($action) {
                case 'create':
                    return [
                        'success' => true,
                        'response' => "I'll help you create a new task. Please provide the task details like title, description, and priority.",
                        'metadata' => [
                            'intent_type' => 'task',
                            'action' => 'create',
                            'confidence' => $intent['confidence'],
                            'entities' => $entities,
                            'requires_followup' => true
                        ]
                    ];

                case 'read':
                case 'list':
                    return [
                        'success' => true,
                        'response' => "I'll help you find task information. Let me search for tasks that match your criteria.",
                        'metadata' => [
                            'intent_type' => 'task',
                            'action' => $action,
                            'confidence' => $intent['confidence'],
                            'entities' => $entities
                        ]
                    ];

                default:
                    return [
                        'success' => true,
                        'response' => "I understand you're asking about tasks. How can I help you with task management?",
                        'metadata' => [
                            'intent_type' => 'task',
                            'action' => 'none',
                            'confidence' => $intent['confidence'],
                            'entities' => $entities
                        ]
                    ];
            }
        } catch (Exception $e) {
            Log::error('Task intent handling failed', [
                'error' => $e->getMessage(),
                'intent' => $intent
            ]);

            return [
                'success' => false,
                'response' => 'I encountered an error while processing your task request. Please try again.',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Handle proposal-related intents
     */
    private function handleProposalIntent(Chat $chat, string $userMessage, array $intent, array $previousContext = []): array
    {
        try {
            $action = $intent['action'] ?? 'none';
            $entities = $intent['entities'] ?? [];

            switch ($action) {
                case 'create':
                    return [
                        'success' => true,
                        'response' => "I'll help you create a new proposal. Please provide the client information and project requirements.",
                        'metadata' => [
                            'intent_type' => 'proposal',
                            'action' => 'create',
                            'confidence' => $intent['confidence'],
                            'entities' => $entities,
                            'requires_followup' => true
                        ]
                    ];

                case 'read':
                case 'list':
                    return [
                        'success' => true,
                        'response' => "I'll help you find proposal information. Let me search for proposals that match your criteria.",
                        'metadata' => [
                            'intent_type' => 'proposal',
                            'action' => $action,
                            'confidence' => $intent['confidence'],
                            'entities' => $entities
                        ]
                    ];

                default:
                    return [
                        'success' => true,
                        'response' => "I understand you're asking about proposals. How can I help you with proposal management?",
                        'metadata' => [
                            'intent_type' => 'proposal',
                            'action' => 'none',
                            'confidence' => $intent['confidence'],
                            'entities' => $entities
                        ]
                    ];
            }
        } catch (Exception $e) {
            Log::error('Proposal intent handling failed', [
                'error' => $e->getMessage(),
                'intent' => $intent
            ]);

            return [
                'success' => false,
                'response' => 'I encountered an error while processing your proposal request. Please try again.',
                'error' => $e->getMessage()
            ];
        }
    }
}
