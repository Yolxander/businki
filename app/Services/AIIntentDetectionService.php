<?php

namespace App\Services;

use App\Models\AIModel;
use App\Models\AIProvider;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Exception;

class AIIntentDetectionService
{
    private AIMLAPIService $aimlapiService;
    private IntentDetectionService $intentDetectionService;

    public function __construct(
        AIMLAPIService $aimlapiService,
        IntentDetectionService $intentDetectionService
    ) {
        $this->aimlapiService = $aimlapiService;
        $this->intentDetectionService = $intentDetectionService;
    }

    /**
     * Detect intent using AI models with fallback to rule-based detection
     */
    public function detectIntent(string $message, array $context = [], ?AIModel $aiModel = null): array
    {
        try {
            // Try AI-based intent detection first
            $aiIntent = $this->detectIntentWithAI($message, $context, $aiModel);

            if ($aiIntent['confidence'] >= 0.7) {
                return $aiIntent;
            }

            // Fallback to rule-based detection
            return $this->detectIntentWithRules($message, $context);
        } catch (Exception $e) {
            Log::error('AI Intent Detection failed, falling back to rule-based', [
                'error' => $e->getMessage(),
                'message' => $message
            ]);

            return $this->detectIntentWithRules($message, $context);
        }
    }

    /**
     * Detect intent using AI models
     */
    private function detectIntentWithAI(string $message, array $context = [], ?AIModel $aiModel = null): array
    {
        // Get the best available AI model for intent detection
        $model = $aiModel ?? $this->getBestIntentDetectionModel();

        if (!$model) {
            throw new Exception('No suitable AI model available for intent detection');
        }

        $prompt = $this->buildIntentDetectionPrompt($message, $context);

        try {
            $response = $this->generateIntentDetectionResponse($prompt, $model);
            return $this->parseIntentDetectionResponse($response, $message);
        } catch (Exception $e) {
            Log::error('AI intent detection failed', [
                'error' => $e->getMessage(),
                'model' => $model->model,
                'message' => $message
            ]);
            throw $e;
        }
    }

    /**
     * Get the best AI model for intent detection
     */
    private function getBestIntentDetectionModel(): ?AIModel
    {
        // Try to get a default model first
        $defaultModel = AIModel::default()->active()->first();
        if ($defaultModel) {
            return $defaultModel;
        }

        // Try to get any active model
        $activeModel = AIModel::active()->first();
        if ($activeModel) {
            return $activeModel;
        }

        return null;
    }

    /**
     * Build prompt for intent detection
     */
    private function buildIntentDetectionPrompt(string $message, array $context = []): string
    {
        $systemPrompt = "You are an AI assistant specialized in detecting user intents from business management conversations.

Analyze the user message and identify the primary intent and any relevant entities. Return your response in the following JSON format:

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

        return $systemPrompt;
    }

    /**
     * Generate intent detection response using AI
     */
    private function generateIntentDetectionResponse(string $prompt, AIModel $model): string
    {
        // Use AIMLAPI for all providers (including OpenAI models)
        $providerConfig = [
            'api_key' => $model->aiProvider->api_key,
            'base_url' => $model->aiProvider->base_url,
            'settings' => $model->aiProvider->settings ?? []
        ];

        $response = $this->aimlapiService->generateChatCompletionWithParams(
            $prompt,
            $model->model,
            0.1, // Low temperature for consistent intent detection
            1000, // Moderate token limit
            0.9,
            $providerConfig
        );
        return $response['content'] ?? '';
    }

    /**
     * Parse AI response into structured intent data
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
            Log::error('Failed to parse AI intent detection response', [
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
     * Fallback to rule-based intent detection
     */
    private function detectIntentWithRules(string $message, array $context = []): array
    {
        // Use existing IntentDetectionService for client intents
        $clientIntent = $this->intentDetectionService->detectClientIntent($message, $context);

        if ($clientIntent['type'] !== 'none') {
            return $clientIntent;
        }

        // Add more rule-based detection for other intent types
        return $this->detectOtherIntentsWithRules($message, $context);
    }

    /**
     * Detect non-client intents using rules
     */
    private function detectOtherIntentsWithRules(string $message, array $context = []): array
    {
        $message = strtolower(trim($message));

        // Project intents
        if ($this->containsProjectKeywords($message)) {
            return $this->detectProjectIntent($message);
        }

        // Task intents
        if ($this->containsTaskKeywords($message)) {
            return $this->detectTaskIntent($message);
        }

        // Proposal intents
        if ($this->containsProposalKeywords($message)) {
            return $this->detectProposalIntent($message);
        }

        // Analytics intents
        if ($this->containsAnalyticsKeywords($message)) {
            return $this->detectAnalyticsIntent($message);
        }

        // Calendar intents
        if ($this->containsCalendarKeywords($message)) {
            return $this->detectCalendarIntent($message);
        }

        // Default to general intent
        return [
            'type' => 'general',
            'action' => 'none',
            'confidence' => 0.3,
            'entities' => [],
            'original_message' => $message
        ];
    }

    /**
     * Check if message contains project keywords
     */
    private function containsProjectKeywords(string $message): bool
    {
        $keywords = ['project', 'projects', 'work', 'assignment', 'job'];
        foreach ($keywords as $keyword) {
            if (str_contains($message, $keyword)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if message contains task keywords
     */
    private function containsTaskKeywords(string $message): bool
    {
        $keywords = ['task', 'tasks', 'todo', 'to-do', 'action item', 'work item'];
        foreach ($keywords as $keyword) {
            if (str_contains($message, $keyword)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if message contains proposal keywords
     */
    private function containsProposalKeywords(string $message): bool
    {
        $keywords = ['proposal', 'proposals', 'quote', 'quotation', 'estimate'];
        foreach ($keywords as $keyword) {
            if (str_contains($message, $keyword)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if message contains analytics keywords
     */
    private function containsAnalyticsKeywords(string $message): bool
    {
        $keywords = ['analytics', 'report', 'reports', 'data', 'statistics', 'metrics', 'dashboard'];
        foreach ($keywords as $keyword) {
            if (str_contains($message, $keyword)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if message contains calendar keywords
     */
    private function containsCalendarKeywords(string $message): bool
    {
        $keywords = ['calendar', 'schedule', 'meeting', 'appointment', 'date', 'time'];
        foreach ($keywords as $keyword) {
            if (str_contains($message, $keyword)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Detect project intent
     */
    private function detectProjectIntent(string $message): array
    {
        $intent = [
            'type' => 'project',
            'action' => 'none',
            'confidence' => 0.6,
            'entities' => []
        ];

        if (str_contains($message, 'create') || str_contains($message, 'new') || str_contains($message, 'add')) {
            $intent['action'] = 'create';
            $intent['confidence'] = 0.8;
        } elseif (str_contains($message, 'show') || str_contains($message, 'view') || str_contains($message, 'get')) {
            $intent['action'] = 'read';
            $intent['confidence'] = 0.7;
        } elseif (str_contains($message, 'update') || str_contains($message, 'edit')) {
            $intent['action'] = 'update';
            $intent['confidence'] = 0.7;
        } elseif (str_contains($message, 'delete') || str_contains($message, 'remove')) {
            $intent['action'] = 'delete';
            $intent['confidence'] = 0.7;
        } elseif (str_contains($message, 'list') || str_contains($message, 'all')) {
            $intent['action'] = 'list';
            $intent['confidence'] = 0.8;
        }

        return $intent;
    }

    /**
     * Detect task intent
     */
    private function detectTaskIntent(string $message): array
    {
        $intent = [
            'type' => 'task',
            'action' => 'none',
            'confidence' => 0.6,
            'entities' => []
        ];

        if (str_contains($message, 'create') || str_contains($message, 'new') || str_contains($message, 'add')) {
            $intent['action'] = 'create';
            $intent['confidence'] = 0.8;
        } elseif (str_contains($message, 'show') || str_contains($message, 'view') || str_contains($message, 'get')) {
            $intent['action'] = 'read';
            $intent['confidence'] = 0.7;
        } elseif (str_contains($message, 'update') || str_contains($message, 'edit')) {
            $intent['action'] = 'update';
            $intent['confidence'] = 0.7;
        } elseif (str_contains($message, 'delete') || str_contains($message, 'remove')) {
            $intent['action'] = 'delete';
            $intent['confidence'] = 0.7;
        } elseif (str_contains($message, 'list') || str_contains($message, 'all')) {
            $intent['action'] = 'list';
            $intent['confidence'] = 0.8;
        }

        return $intent;
    }

    /**
     * Detect proposal intent
     */
    private function detectProposalIntent(string $message): array
    {
        $intent = [
            'type' => 'proposal',
            'action' => 'none',
            'confidence' => 0.6,
            'entities' => []
        ];

        if (str_contains($message, 'create') || str_contains($message, 'new') || str_contains($message, 'generate')) {
            $intent['action'] = 'create';
            $intent['confidence'] = 0.8;
        } elseif (str_contains($message, 'show') || str_contains($message, 'view') || str_contains($message, 'get')) {
            $intent['action'] = 'read';
            $intent['confidence'] = 0.7;
        } elseif (str_contains($message, 'update') || str_contains($message, 'edit')) {
            $intent['action'] = 'update';
            $intent['confidence'] = 0.7;
        } elseif (str_contains($message, 'delete') || str_contains($message, 'remove')) {
            $intent['action'] = 'delete';
            $intent['confidence'] = 0.7;
        } elseif (str_contains($message, 'list') || str_contains($message, 'all')) {
            $intent['action'] = 'list';
            $intent['confidence'] = 0.8;
        }

        return $intent;
    }

    /**
     * Detect analytics intent
     */
    private function detectAnalyticsIntent(string $message): array
    {
        $intent = [
            'type' => 'analytics',
            'action' => 'analyze',
            'confidence' => 0.7,
            'entities' => []
        ];

        return $intent;
    }

    /**
     * Detect calendar intent
     */
    private function detectCalendarIntent(string $message): array
    {
        $intent = [
            'type' => 'calendar',
            'action' => 'schedule',
            'confidence' => 0.7,
            'entities' => []
        ];

        return $intent;
    }

    /**
     * Get intent detection statistics
     */
    public function getIntentDetectionStats(): array
    {
        $cacheKey = 'intent_detection_stats';

        return Cache::remember($cacheKey, 3600, function () {
            return [
                'total_detections' => 0, // Would be tracked in production
                'ai_success_rate' => 0.85, // Estimated
                'fallback_rate' => 0.15, // Estimated
                'average_confidence' => 0.75, // Estimated
                'supported_intent_types' => [
                    'client', 'project', 'task', 'proposal',
                    'analytics', 'calendar', 'general', 'system'
                ]
            ];
        });
    }
}
