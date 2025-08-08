<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Exception;

class AIMLAPIService
{
    private string $apiKey;
    private string $baseUrl;
    private string $model;
    private int $maxTokens;
    private float $temperature;
    private float $topP;
    private float $frequencyPenalty;
    private float $presencePenalty;

    public function __construct()
    {
        $this->apiKey = config('services.aimlapi.api_key') ?? '';
        $this->baseUrl = config('services.aimlapi.base_url', 'https://api.aimlapi.com/v1');
        $this->model = config('services.aimlapi.model', 'gpt-4o');
        $this->maxTokens = config('services.aimlapi.max_tokens', 2048);
        $this->temperature = config('services.aimlapi.temperature', 0.7);
        $this->topP = config('services.aimlapi.top_p', 0.9);
        $this->frequencyPenalty = config('services.aimlapi.frequency_penalty', 0.0);
        $this->presencePenalty = config('services.aimlapi.presence_penalty', 0.0);
    }

    /**
     * Test the AIMLAPI connection
     */
    public function testConnection(?array $providerConfig = null): array
    {
        try {
            // Use provider-specific configuration if provided, otherwise fall back to default
            $apiKey = $providerConfig['api_key'] ?? $this->apiKey;
            $baseUrl = $providerConfig['base_url'] ?? $this->baseUrl;
            $model = $providerConfig['model'] ?? $this->model;

            if (empty($apiKey)) {
                return [
                    'status' => 'error',
                    'message' => 'AIMLAPI API key not configured',
                    'data' => null
                ];
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->post($baseUrl . '/chat/completions', [
                'model' => $model,
                'messages' => [
                    ['role' => 'user', 'content' => 'Hello, this is a test message.']
                ],
                'max_tokens' => 10,
                'temperature' => 0.1,
            ]);

            if ($response->successful()) {
                return [
                    'status' => 'success',
                    'message' => 'Connection successful',
                    'data' => $response->json()
                ];
            } else {
                $errorBody = $response->body();
                $statusCode = $response->status();

                Log::error('AIMLAPI connection test failed', [
                    'status_code' => $statusCode,
                    'response_body' => $errorBody,
                    'base_url' => $baseUrl
                ]);

                return [
                    'status' => 'error',
                    'message' => 'Connection failed: ' . $errorBody,
                    'data' => null
                ];
            }
        } catch (Exception $e) {
            Log::error('AIMLAPI connection test failed', [
                'error' => $e->getMessage(),
                'provider_config' => $providerConfig ? 'provided' : 'default'
            ]);

            return [
                'status' => 'error',
                'message' => 'Connection failed: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Generate a simple chat completion
     */
    public function generateChatCompletion(string $message, array $options = []): array
    {
        try {
            $model = $options['model'] ?? $this->model;
            $maxTokens = $options['max_tokens'] ?? $this->maxTokens;
            $temperature = $options['temperature'] ?? $this->temperature;
            $topP = $options['top_p'] ?? $this->topP;
            $frequencyPenalty = $options['frequency_penalty'] ?? $this->frequencyPenalty;
            $presencePenalty = $options['presence_penalty'] ?? $this->presencePenalty;

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/chat/completions', [
                'model' => $model,
                'messages' => [
                    ['role' => 'user', 'content' => $message]
                ],
                'max_tokens' => $maxTokens,
                'temperature' => $temperature,
                'top_p' => $topP,
                'frequency_penalty' => $frequencyPenalty,
                'presence_penalty' => $presencePenalty,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'status' => 'success',
                    'message' => 'Chat completion generated successfully',
                    'data' => $data,
                    'content' => $data['choices'][0]['message']['content'] ?? null
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Failed to generate chat completion: ' . $response->body(),
                    'data' => null
                ];
            }
        } catch (Exception $e) {
            Log::error('AIMLAPI chat completion failed', [
                'error' => $e->getMessage(),
                'message' => $message
            ]);

            return [
                'status' => 'error',
                'message' => 'Failed to generate chat completion: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Generate chat completion with specific parameters for playground
     */
    public function generateChatCompletionWithParams(string $prompt, string $model, float $temperature = 0.7, int $maxTokens = 2000, float $topP = 1.0, ?array $providerConfig = null): array
    {
        try {
            // Use provider-specific configuration if provided, otherwise fall back to default
            $apiKey = $providerConfig['api_key'] ?? $this->apiKey;
            $baseUrl = $providerConfig['base_url'] ?? $this->baseUrl;

            if (empty($apiKey)) {
                throw new Exception('AIMLAPI API key not configured');
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->post($baseUrl . '/chat/completions', [
                'model' => $model,
                'messages' => [
                    ['role' => 'user', 'content' => $prompt]
                ],
                'max_tokens' => $maxTokens,
                'temperature' => $temperature,
                'top_p' => $topP,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $content = $data['choices'][0]['message']['content'] ?? '';
                $usage = $data['usage'] ?? [];

                return [
                    'content' => $content,
                    'tokens' => $usage['total_tokens'] ?? 0,
                    'cost' => $this->calculateCost($usage['total_tokens'] ?? 0, $model)
                ];
            } else {
                $errorBody = $response->body();
                $statusCode = $response->status();

                Log::error('AIMLAPI request failed', [
                    'status_code' => $statusCode,
                    'response_body' => $errorBody,
                    'model' => $model,
                    'base_url' => $baseUrl
                ]);

                throw new Exception("AIMLAPI request failed: {$errorBody}");
            }
        } catch (Exception $e) {
            Log::error('AIMLAPI chat completion failed', [
                'error' => $e->getMessage(),
                'prompt' => $prompt,
                'model' => $model,
                'provider_config' => $providerConfig ? 'provided' : 'default'
            ]);
            throw $e;
        }
    }

    /**
     * Calculate cost based on token usage and model
     */
    private function calculateCost(int $tokens, string $model): string
    {
        // Rough cost estimates per 1K tokens for AIMLAPI
        $costs = [
            'gpt-4' => 0.02,
            'gpt-4o' => 0.015,
            'gpt-3.5-turbo' => 0.001,
        ];

        $costPerToken = $costs[$model] ?? 0.01;
        $cost = ($tokens / 1000) * $costPerToken;

        return '$' . number_format($cost, 4);
    }

    /**
     * Validate provider configuration
     */
    public function validateProviderConfig(?array $providerConfig): array
    {
        $errors = [];

        if (empty($providerConfig)) {
            $errors[] = 'Provider configuration is required';
            return $errors;
        }

        if (empty($providerConfig['api_key'])) {
            $errors[] = 'API key is required';
        }

        if (empty($providerConfig['base_url'])) {
            $errors[] = 'Base URL is required';
        }

        return $errors;
    }

    /**
     * Generate a proposal from intake response data
     */
    public function generateProposal(array $intakeData, array $options = []): array
    {
        $includeDeliverables = $options['include_deliverables'] ?? true;
        $includeTimeline = $options['include_timeline'] ?? true;
        $includePricing = $options['include_pricing'] ?? true;

        $prompt = $this->buildProposalPrompt($intakeData, $includeDeliverables, $includeTimeline, $includePricing);

        $functions = [
            [
                'name' => 'create_proposal',
                'description' => 'Create a comprehensive proposal based on the intake response',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'title' => [
                            'type' => 'string',
                            'description' => 'Professional proposal title'
                        ],
                        'scope' => [
                            'type' => 'string',
                            'description' => 'Detailed project scope and objectives'
                        ],
                        'deliverables' => [
                            'type' => 'array',
                            'items' => ['type' => 'string'],
                            'description' => 'List of specific deliverables'
                        ],
                        'timeline' => [
                            'type' => 'array',
                            'items' => [
                                'type' => 'object',
                                'properties' => [
                                    'id' => ['type' => 'string'],
                                    'description' => ['type' => 'string'],
                                    'duration' => ['type' => 'string'],
                                    'price' => ['type' => 'number']
                                ],
                                'required' => ['id', 'description', 'duration', 'price']
                            ],
                            'description' => 'Project phases with pricing'
                        ],
                        'total_price' => [
                            'type' => 'number',
                            'description' => 'Total project cost'
                        ]
                    ],
                    'required' => ['title', 'scope', 'deliverables', 'timeline', 'total_price']
                ]
            ]
        ];

        return $this->makeFunctionCall($prompt, $functions, 'create_proposal');
    }

    /**
     * Generate a project from proposal data
     */
    public function generateProject(array $proposalData, array $options = []): array
    {
        $includeTimeline = $options['include_timeline'] ?? true;
        $includePhases = $options['include_phases'] ?? true;

        $prompt = $this->buildProjectPrompt($proposalData, $includeTimeline, $includePhases);

        $functions = [
            [
                'name' => 'create_project',
                'description' => 'Create a project structure from proposal data',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'title' => [
                            'type' => 'string',
                            'description' => 'Project title'
                        ],
                        'current_phase' => [
                            'type' => 'string',
                            'description' => 'Current project phase'
                        ],
                        'kickoff_date' => [
                            'type' => 'string',
                            'description' => 'Project kickoff date (YYYY-MM-DD)'
                        ],
                        'expected_delivery' => [
                            'type' => 'string',
                            'description' => 'Expected delivery date (YYYY-MM-DD)'
                        ],
                        'notes' => [
                            'type' => 'string',
                            'description' => 'Project notes and additional information'
                        ]
                    ],
                    'required' => ['title', 'current_phase', 'kickoff_date', 'expected_delivery']
                ]
            ]
        ];

        return $this->makeFunctionCall($prompt, $functions, 'create_project');
    }

    /**
     * Generate tasks for a project
     */
    public function generateTasks(array $projectData, array $options = []): array
    {
        $maxTasks = $options['max_tasks'] ?? 10;
        $includeSubtasks = $options['include_subtasks'] ?? false;

        $prompt = $this->buildTasksPrompt($projectData, $maxTasks, $includeSubtasks);

        $taskProperties = [
            'title' => ['type' => 'string', 'description' => 'Task title'],
            'description' => ['type' => 'string', 'description' => 'Task description'],
            'priority' => ['type' => 'string', 'enum' => ['low', 'medium', 'high'], 'description' => 'Task priority'],
            'estimated_hours' => ['type' => 'number', 'description' => 'Estimated hours to complete'],
            'tags' => ['type' => 'array', 'items' => ['type' => 'string'], 'description' => 'Task tags']
        ];

        if ($includeSubtasks) {
            $taskProperties['subtasks'] = [
                'type' => 'array',
                'items' => ['type' => 'string'],
                'description' => 'List of subtasks'
            ];
        }

        $functions = [
            [
                'name' => 'create_tasks',
                'description' => 'Create tasks for the project',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'tasks' => [
                            'type' => 'array',
                            'items' => [
                                'type' => 'object',
                                'properties' => $taskProperties,
                                'required' => ['title', 'description', 'priority']
                            ],
                            'description' => 'List of tasks for the project'
                        ]
                    ],
                    'required' => ['tasks']
                ]
            ]
        ];

        return $this->makeFunctionCall($prompt, $functions, 'create_tasks');
    }

    /**
     * Make a function call to the AIMLAPI
     */
    private function makeFunctionCall(string $prompt, array $functions, string $functionName): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/chat/completions', [
                'model' => $this->model,
                'messages' => [
                    ['role' => 'user', 'content' => $prompt]
                ],
                'functions' => $functions,
                'function_call' => ['name' => $functionName],
                'max_tokens' => $this->maxTokens,
                'temperature' => $this->temperature,
                'top_p' => $this->topP,
                'frequency_penalty' => $this->frequencyPenalty,
                'presence_penalty' => $this->presencePenalty,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $functionCall = $data['choices'][0]['message']['function_call'] ?? null;

                if ($functionCall && $functionCall['name'] === $functionName) {
                    $arguments = json_decode($functionCall['arguments'], true);
                    return $arguments;
                } else {
                    throw new Exception('Function call not returned as expected');
                }
            } else {
                throw new Exception('API request failed: ' . $response->body());
            }
        } catch (Exception $e) {
            Log::error('AIMLAPI function call failed', [
                'error' => $e->getMessage(),
                'function' => $functionName
            ]);

            throw $e;
        }
    }

    /**
     * Build proposal prompt
     */
    public function buildProposalPrompt(array $intakeData, bool $includeDeliverables, bool $includeTimeline, bool $includePricing): string
    {
        $prompt = "Based on the following intake response, create a comprehensive business proposal:\n\n";
        $prompt .= "Client Information:\n";
        $prompt .= "- Name: " . ($intakeData['client_name'] ?? 'N/A') . "\n";
        $prompt .= "- Company: " . ($intakeData['company_name'] ?? 'N/A') . "\n";
        $prompt .= "- Email: " . ($intakeData['email'] ?? 'N/A') . "\n\n";

        $prompt .= "Project Requirements:\n";
        $prompt .= "- Project Type: " . ($intakeData['project_type'] ?? 'N/A') . "\n";
        $prompt .= "- Description: " . ($intakeData['description'] ?? 'N/A') . "\n";
        $prompt .= "- Budget: " . ($intakeData['budget'] ?? 'N/A') . "\n";
        $prompt .= "- Timeline: " . ($intakeData['timeline'] ?? 'N/A') . "\n\n";

        $prompt .= "Please create a professional proposal that includes:\n";
        $prompt .= "1. A compelling title\n";
        $prompt .= "2. Detailed project scope\n";

        if ($includeDeliverables) {
            $prompt .= "3. Specific deliverables\n";
        }

        if ($includeTimeline) {
            $prompt .= "4. Project timeline with phases\n";
        }

        if ($includePricing) {
            $prompt .= "5. Detailed pricing breakdown\n";
        }

        return $prompt;
    }

    /**
     * Build project prompt
     */
    public function buildProjectPrompt(array $proposalData, bool $includeTimeline, bool $includePhases): string
    {
        $prompt = "Based on the following proposal, create a detailed project structure:\n\n";
        $prompt .= "Proposal Title: " . ($proposalData['title'] ?? 'N/A') . "\n";
        $prompt .= "Scope: " . ($proposalData['scope'] ?? 'N/A') . "\n";
        $prompt .= "Deliverables: " . implode(', ', $proposalData['deliverables'] ?? []) . "\n";
        $prompt .= "Total Price: $" . ($proposalData['price'] ?? 'N/A') . "\n\n";

        $prompt .= "Please create a project structure that includes:\n";
        $prompt .= "1. Project title\n";
        $prompt .= "2. Current phase (should be 'Planning' or 'Kickoff')\n";
        $prompt .= "3. Kickoff date (within the next 2 weeks)\n";
        $prompt .= "4. Expected delivery date (based on timeline)\n";
        $prompt .= "5. Project notes\n";

        return $prompt;
    }

    /**
     * Build tasks prompt
     */
    public function buildTasksPrompt(array $projectData, int $maxTasks, bool $includeSubtasks): string
    {
        $prompt = "Based on the following project, create up to {$maxTasks} detailed tasks:\n\n";
        $prompt .= "Project Title: " . ($projectData['title'] ?? 'N/A') . "\n";
        $prompt .= "Current Phase: " . ($projectData['current_phase'] ?? 'N/A') . "\n";
        $prompt .= "Notes: " . ($projectData['notes'] ?? 'N/A') . "\n\n";

        $prompt .= "Please create tasks that:\n";
        $prompt .= "1. Are specific and actionable\n";
        $prompt .= "2. Have clear priorities (low, medium, high)\n";
        $prompt .= "3. Include estimated hours\n";
        $prompt .= "4. Have relevant tags\n";

        if ($includeSubtasks) {
            $prompt .= "5. Include subtasks where appropriate\n";
        }

        return $prompt;
    }

    /**
     * Update service configuration
     */
    public function updateConfiguration(array $config): void
    {
        $this->model = $config['model'] ?? $this->model;
        $this->maxTokens = $config['max_tokens'] ?? $this->maxTokens;
        $this->temperature = $config['temperature'] ?? $this->temperature;
        $this->topP = $config['top_p'] ?? $this->topP;
        $this->frequencyPenalty = $config['frequency_penalty'] ?? $this->frequencyPenalty;
        $this->presencePenalty = $config['presence_penalty'] ?? $this->presencePenalty;
    }

    /**
     * Get current configuration
     */
    public function getConfiguration(): array
    {
        return [
            'model' => $this->model,
            'max_tokens' => $this->maxTokens,
            'temperature' => $this->temperature,
            'top_p' => $this->topP,
            'frequency_penalty' => $this->frequencyPenalty,
            'presence_penalty' => $this->presencePenalty,
        ];
    }

    /**
     * Generate intent detection using AI models
     */
    public function generateIntentDetection(string $message, array $context = [], array $options = []): array
    {
        try {
            $model = $options['model'] ?? $this->model;
            $temperature = $options['temperature'] ?? 0.1; // Low temperature for consistent intent detection
            $maxTokens = $options['max_tokens'] ?? 1000;
            $topP = $options['top_p'] ?? 0.9;

            $prompt = $this->buildIntentDetectionPrompt($message, $context);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/chat/completions', [
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => 'You are an AI assistant specialized in detecting user intents from business management conversations.'],
                    ['role' => 'user', 'content' => $prompt]
                ],
                'max_tokens' => $maxTokens,
                'temperature' => $temperature,
                'top_p' => $topP,
                'frequency_penalty' => $this->frequencyPenalty,
                'presence_penalty' => $this->presencePenalty,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $content = $data['choices'][0]['message']['content'] ?? '';
                $usage = $data['usage'] ?? [];

                return [
                    'content' => $content,
                    'tokens' => $usage['total_tokens'] ?? 0,
                    'cost' => $this->calculateCost($usage['total_tokens'] ?? 0, $model),
                    'parsed_intent' => $this->parseIntentDetectionResponse($content, $message)
                ];
            } else {
                $errorBody = $response->body();
                $statusCode = $response->status();

                Log::error('AIMLAPI intent detection failed', [
                    'status_code' => $statusCode,
                    'response_body' => $errorBody,
                    'model' => $model
                ]);

                throw new Exception("AIMLAPI intent detection failed: {$errorBody}");
            }
        } catch (Exception $e) {
            Log::error('AIMLAPI intent detection error', [
                'error' => $e->getMessage(),
                'message' => $message
            ]);

            throw $e;
        }
    }

    /**
     * Build prompt for intent detection
     */
    private function buildIntentDetectionPrompt(string $message, array $context = []): string
    {
        return "Analyze the user message and identify the primary intent and any relevant entities. Return your response in the following JSON format:

{
    \"intent\": {
        \"type\": \"[intent_type]\",
        \"action\": \"[action]\",
        \"confidence\": [confidence_score_0_to_1],
        \"entities\": {
            \"[entity_type]\": \"[entity_value]\"
        }
    }
}

Available intent types:
- client: Client management (create, read, update, delete, list)
- project: Project management (create, read, update, delete, list)
- task: Task management (create, read, update, delete, list)
- proposal: Proposal management (create, read, update, delete, list)
- analytics: Data analysis and reporting
- calendar: Scheduling and time management
- general: General business queries
- system: System configuration and settings

Available actions:
- create: Create new item
- read: View or get information
- update: Modify existing item
- delete: Remove item
- list: Show multiple items
- analyze: Analyze data or generate reports
- schedule: Schedule or plan
- configure: Configure settings

Entity types to extract:
- name: Person or company name
- email: Email address
- phone: Phone number
- company: Company name
- project_id: Project identifier
- task_id: Task identifier
- client_id: Client identifier
- date: Date or time
- status: Status information
- priority: Priority level
- amount: Monetary amount
- description: Description or details

Context information: " . json_encode($context) . "

User message: " . $message;
    }

    /**
     * Parse intent detection response
     */
    private function parseIntentDetectionResponse(string $response, string $originalMessage): array
    {
        try {
            // Try to extract JSON from the response
            $jsonMatch = preg_match('/\{.*\}/s', $response, $matches);
            if ($jsonMatch) {
                $jsonData = json_decode($matches[0], true);
                if (json_last_error() === JSON_ERROR_NONE && isset($jsonData['intent'])) {
                    return $this->validateAndEnhanceIntent($jsonData['intent'], $originalMessage);
                }
            }

            // If JSON parsing fails, try to extract intent from text
            return $this->extractIntentFromText($response, $originalMessage);
        } catch (Exception $e) {
            Log::error('Failed to parse intent detection response', [
                'error' => $e->getMessage(),
                'response' => $response
            ]);

            return [
                'type' => 'general',
                'action' => 'none',
                'confidence' => 0.3,
                'entities' => [],
                'raw_response' => $response
            ];
        }
    }

    /**
     * Validate and enhance intent data
     */
    private function validateAndEnhanceIntent(array $intent, string $originalMessage): array
    {
        $validTypes = ['client', 'project', 'task', 'proposal', 'analytics', 'calendar', 'general', 'system'];
        $validActions = ['create', 'read', 'update', 'delete', 'list', 'analyze', 'schedule', 'configure'];

        // Validate intent type
        if (!isset($intent['type']) || !in_array($intent['type'], $validTypes)) {
            $intent['type'] = 'general';
        }

        // Validate action
        if (!isset($intent['action']) || !in_array($intent['action'], $validActions)) {
            $intent['action'] = 'none';
        }

        // Validate confidence
        if (!isset($intent['confidence']) || !is_numeric($intent['confidence'])) {
            $intent['confidence'] = 0.5;
        }
        $intent['confidence'] = max(0, min(1, $intent['confidence']));

        // Ensure entities is an array
        if (!isset($intent['entities']) || !is_array($intent['entities'])) {
            $intent['entities'] = [];
        }

        // Add original message for reference
        $intent['original_message'] = $originalMessage;

        return $intent;
    }

    /**
     * Extract intent from text response when JSON parsing fails
     */
    private function extractIntentFromText(string $response, string $originalMessage): array
    {
        $intent = [
            'type' => 'general',
            'action' => 'none',
            'confidence' => 0.4,
            'entities' => [],
            'original_message' => $originalMessage
        ];

        // Try to extract intent type from text
        $responseLower = strtolower($response);

        if (str_contains($responseLower, 'client')) {
            $intent['type'] = 'client';
        } elseif (str_contains($responseLower, 'project')) {
            $intent['type'] = 'project';
        } elseif (str_contains($responseLower, 'task')) {
            $intent['type'] = 'task';
        } elseif (str_contains($responseLower, 'proposal')) {
            $intent['type'] = 'proposal';
        } elseif (str_contains($responseLower, 'analytics') || str_contains($responseLower, 'report')) {
            $intent['type'] = 'analytics';
        } elseif (str_contains($responseLower, 'calendar') || str_contains($responseLower, 'schedule')) {
            $intent['type'] = 'calendar';
        }

        // Try to extract action
        if (str_contains($responseLower, 'create') || str_contains($responseLower, 'add')) {
            $intent['action'] = 'create';
        } elseif (str_contains($responseLower, 'read') || str_contains($responseLower, 'show') || str_contains($responseLower, 'view')) {
            $intent['action'] = 'read';
        } elseif (str_contains($responseLower, 'update') || str_contains($responseLower, 'edit')) {
            $intent['action'] = 'update';
        } elseif (str_contains($responseLower, 'delete') || str_contains($responseLower, 'remove')) {
            $intent['action'] = 'delete';
        } elseif (str_contains($responseLower, 'list') || str_contains($responseLower, 'all')) {
            $intent['action'] = 'list';
        }

        return $intent;
    }

    /**
     * Get available models for intent detection
     */
    public function getIntentDetectionModels(): array
    {
        return [
            'gpt-4o' => 'GPT-4 Omni (Best for intent detection)',
            'gpt-4o-mini' => 'GPT-4 Omni Mini (Fast intent detection)',
            'claude-3-opus' => 'Claude 3 Opus (High accuracy)',
            'claude-3-sonnet' => 'Claude 3 Sonnet (Balanced)',
            'claude-3-haiku' => 'Claude 3 Haiku (Fast)',
            'gemini-pro' => 'Gemini Pro (Good for intent detection)'
        ];
    }

    /**
     * Process client data and generate a formatted response
     */
    public function processClientData(array $clients, string $viewType, array $options = []): array
    {
        try {
            $prompt = $this->buildClientDataPrompt($clients, $viewType);

            $response = $this->generateChatCompletionWithParams(
                $prompt,
                $options['model'] ?? $this->model,
                $options['temperature'] ?? 0.7,
                $options['max_tokens'] ?? 2000,
                $options['top_p'] ?? 0.9
            );

            // generateChatCompletionWithParams returns content, tokens, and cost directly
            return [
                'status' => 'success',
                'data' => [
                    'response' => $response['content'],
                    'view_type' => $viewType,
                    'client_count' => count($clients),
                    'tokens_used' => $response['tokens'],
                    'cost' => $response['cost']
                ]
            ];
        } catch (Exception $e) {
            Log::error('Error processing client data', [
                'error' => $e->getMessage(),
                'view_type' => $viewType,
                'client_count' => count($clients)
            ]);

            return [
                'status' => 'error',
                'message' => 'Failed to process client data: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Build a prompt for processing client data
     */
    private function buildClientDataPrompt(array $clients, string $viewType): string
    {
        $clientData = json_encode($clients, JSON_PRETTY_PRINT);

        return "You are a helpful AI assistant. Here is the client data for '{$viewType}':\n\n" .
               "Client Data:\n{$clientData}\n\n" .
               "Please provide a clean list of clients with their key information. Format each client as:\n" .
               "Client Name - Email (Status) - Last Contact: date, Rating: number, Projects: number, Total Revenue: amount\n" .
               "Each client should be on a separate line with no bullet points or markdown formatting.\n" .
               "Keep it concise and include all the key details for each client.";
    }
}
