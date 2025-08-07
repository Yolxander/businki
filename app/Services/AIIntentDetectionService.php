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

    public function __construct(
        AIMLAPIService $aimlapiService
    ) {
        $this->aimlapiService = $aimlapiService;
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
        try {
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

            // If no AI models are available, return null to trigger rule-based fallback
            Log::info('No AI models available, using rule-based fallback');
            return null;
        } catch (Exception $e) {
            Log::warning('Error getting AI model, using rule-based fallback', [
                'error' => $e->getMessage()
            ]);
            return null;
        }
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
        // Detect client intents
        $clientIntent = $this->detectClientIntent($message, $context);

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
     * Detect client-related intents
     */
    private function detectClientIntent(string $message, array $context = []): array
    {
        $message = strtolower(trim($message));
        $intent = [
            'type' => 'none',
            'action' => 'none',
            'confidence' => 0.0,
            'entities' => [],
            'data' => []
        ];

        // Check if message contains client keywords or is about a specific person
        $clientKeywords = ['client', 'customer', 'contact'];
        $hasClientKeywords = false;
        foreach ($clientKeywords as $keyword) {
            if (str_contains($message, $keyword)) {
                $hasClientKeywords = true;
                break;
            }
        }

        // Check if this is about updating a specific person (likely a client)
        $personName = $this->extractPersonName($message);
        if (!$hasClientKeywords && $personName && (str_contains($message, 'update') || str_contains($message, 'edit'))) {
            $hasClientKeywords = true;
        }

        // Check if this is a response to field collection (context-aware)
        if (!$hasClientKeywords && !empty($context)) {
            // If we have context and the message looks like a field response
            if ($this->isFieldResponse($message, $context)) {
                $intent['type'] = 'client';
                $intent['action'] = 'field_response';
                $intent['confidence'] = 0.8;
                $intent['data'] = $this->extractFieldResponseData($message, $context);
                return $intent;
            }

            // Check if this is an update field response
            if ($this->isUpdateFieldResponse($message, $context)) {
                $intent['type'] = 'client';
                $intent['action'] = 'update_field_response';
                $intent['confidence'] = 0.8;
                $intent['data'] = $this->extractUpdateFieldData($message, $context);
                return $intent;
            }
        }

        if (!$hasClientKeywords) {
            return $intent;
        }

                // Extract client data
        $data = $this->extractClientData($message);

        // Extract person name if not already in data
        if (empty($data['name']) && empty($data['first_name'])) {
            $personName = $this->extractPersonName($message);
            if ($personName) {
                $nameParts = explode(' ', $personName);
                if (count($nameParts) >= 2) {
                    $data['first_name'] = $nameParts[0];
                    $data['last_name'] = $nameParts[1];
                } else {
                    $data['name'] = strtolower($personName);
                }
            }
        }

        // Determine action
        if (str_contains($message, 'update') || str_contains($message, 'edit')) {
            $intent['type'] = 'client';
            $intent['action'] = 'update';
            $intent['confidence'] = 0.7;

            // Format data for update action
            $updateData = [
                'identifier' => [
                    'name' => $data['name'] ?? null,
                    'email' => null
                ],
                'updates' => [] // Will be filled in subsequent messages
            ];

            // If we have a name, use it as identifier
            if (!empty($data['name']) || !empty($data['first_name'])) {
                $updateData['identifier']['name'] = $data['name'] ?? ($data['first_name'] . ' ' . ($data['last_name'] ?? ''));
            }

            // For update commands, extract the actual update data
            if (preg_match('/update\s+([^,\s]+(?:\s+[^,\s]+)*)\s+(\w+)\s+to\s+([^\s]+)/i', $message, $matches)) {
                $fieldName = strtolower($matches[2]);
                $newValue = $matches[3];
                
                if ($fieldName === 'email') {
                    $updateData['updates']['email'] = $newValue;
                } elseif ($fieldName === 'phone') {
                    $updateData['updates']['phone'] = $newValue;
                } elseif ($fieldName === 'name') {
                    $updateData['updates']['first_name'] = ucfirst(strtolower($newValue));
                }
            }

            $intent['data'] = $updateData;
        } elseif (str_contains($message, 'create') || str_contains($message, 'new') || str_contains($message, 'add')) {
            $intent['type'] = 'client';
            $intent['action'] = 'create';
            $intent['confidence'] = 0.8;
            $intent['data'] = $data;
        } elseif (str_contains($message, 'show') || str_contains($message, 'view') || str_contains($message, 'get') || str_contains($message, 'find')) {
            $intent['type'] = 'client';
            $intent['action'] = 'read';
            $intent['confidence'] = 0.7;
            $intent['data'] = $data;
        } elseif (str_contains($message, 'list') || str_contains($message, 'all')) {
            $intent['type'] = 'client';
            $intent['action'] = 'list';
            $intent['confidence'] = 0.8;
        } elseif (str_contains($message, 'delete') || str_contains($message, 'remove')) {
            $intent['type'] = 'client';
            $intent['action'] = 'delete';
            $intent['confidence'] = 0.7;
            $intent['data'] = $data;
        }

        return $intent;
    }

    /**
     * Extract client data from message with enhanced pattern matching
     */
    private function extractClientData(string $message): array
    {
        $data = [];

        // Extract name from "named" pattern
        if (preg_match('/named\s+([^,\s]+(?:\s+[^,\s]+)*)/i', $message, $matches)) {
            $name = trim($matches[1]);
            $nameParts = explode(' ', $name);
            if (count($nameParts) >= 2) {
                $data['first_name'] = ucfirst(strtolower($nameParts[0]));
                $data['last_name'] = ucfirst(strtolower($nameParts[1]));
            } else {
                $data['name'] = strtolower($name);
            }
        }

        // Extract name from "Show client" pattern
        if (preg_match('/show\s+client\s+([^,\s]+(?:\s+[^,\s]+)*)/i', $message, $matches)) {
            $name = trim($matches[1]);
            $data['name'] = strtolower($name);
        }

        // Extract name from "find the client" pattern
        if (preg_match('/find\s+(?:the\s+)?client\s+([^,\s]+(?:\s+[^,\s]+)*)/i', $message, $matches)) {
            $name = trim($matches[1]);
            $data['name'] = strtolower($name);
        }

        // Enhanced name extraction for various patterns
        $namePatterns = [
            '/first\s+name\s+(?:is\s+)?([^,\s]+)/i',
            '/last\s+name\s+(?:is\s+)?([^,\s]+)/i',
            '/name\s+(?:is\s+)?([^,\s]+(?:\s+[^,\s]+)*)/i',
            '/it\'s\s+([^,\s]+(?:\s+[^,\s]+)*)/i',
            '/is\s+([^,\s]+(?:\s+[^,\s]+)*)/i'
        ];

        foreach ($namePatterns as $pattern) {
            if (preg_match($pattern, $message, $matches)) {
                $name = trim($matches[1]);
                $nameParts = explode(' ', $name);
                if (count($nameParts) >= 2) {
                    $data['first_name'] = ucfirst(strtolower($nameParts[0]));
                    $data['last_name'] = ucfirst(strtolower($nameParts[1]));
                } else {
                    $data['first_name'] = ucfirst(strtolower($name));
                }
                break;
            }
        }

        // Enhanced email extraction
        $emailPatterns = [
            '/email\s+(?:is\s+)?([^\s]+@[^\s]+)/i',
            '/email\s+address\s+(?:is\s+)?([^\s]+@[^\s]+)/i',
            '/([^\s]+@[^\s]+)/i' // General email pattern
        ];

        foreach ($emailPatterns as $pattern) {
            if (preg_match($pattern, $message, $matches)) {
                $data['email'] = $matches[1];
                break;
            }
        }

        // Enhanced phone extraction
        $phonePatterns = [
            '/phone\s+(?:is\s+)?([^\s]+)/i',
            '/phone\s+number\s+(?:is\s+)?([^\s]+)/i',
            '/(\d{3}[-.]?\d{3}[-.]?\d{4})/i', // General phone pattern
            '/(\+\d{1,3}[-.]?\d{1,4}[-.]?\d{1,4}[-.]?\d{1,4})/i' // International format
        ];

        foreach ($phonePatterns as $pattern) {
            if (preg_match($pattern, $message, $matches)) {
                $data['phone'] = $matches[1];
                break;
            }
        }

        // Enhanced company extraction
        $companyPatterns = [
            '/company\s+(?:is\s+)?([^,\s]+(?:\s+[^,\s]+)*)/i',
            '/company\s+name\s+(?:is\s+)?([^,\s]+(?:\s+[^,\s]+)*)/i',
            '/works?\s+at\s+([^,\s]+(?:\s+[^,\s]+)*)/i',
            '/from\s+([^,\s]+(?:\s+[^,\s]+)*)/i'
        ];

        foreach ($companyPatterns as $pattern) {
            if (preg_match($pattern, $message, $matches)) {
                $data['company_name'] = trim($matches[1]);
                break;
            }
        }

        return $data;
    }

    /**
     * Extract field response data based on context
     */
    private function extractFieldResponseData(string $message, array $context): array
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
     * Extract person name from message
     */
    private function extractPersonName(string $message): ?string
    {
        $message = strtolower(trim($message));

        // Common name patterns
        $namePatterns = [
            '/update\s+([a-z]+(?:\s+[a-z]+)*?)(?:\s+info|\s+details|\s+information)?/i',
            '/edit\s+([a-z]+(?:\s+[a-z]+)*?)(?:\s+info|\s+details|\s+information)?/i',
            '/change\s+([a-z]+(?:\s+[a-z]+)*?)(?:\s+info|\s+details|\s+information)?/i',
            '/modify\s+([a-z]+(?:\s+[a-z]+)*?)(?:\s+info|\s+details|\s+information)?/i',
            '/([a-z]+(?:\s+[a-z]+)*?)\s+info/i',
            '/([a-z]+(?:\s+[a-z]+)*?)\s+details/i'
        ];

        foreach ($namePatterns as $pattern) {
            if (preg_match($pattern, $message, $matches)) {
                $name = trim($matches[1]);
                // Filter out common words that aren't names
                $excludeWords = ['the', 'this', 'that', 'their', 'her', 'his', 'my', 'your', 'our'];
                if (!in_array(strtolower($name), $excludeWords) && strlen($name) > 1) {
                    return ucwords($name);
                }
            }
        }

        return null;
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
     * Check if message is an update field response
     */
    private function isUpdateFieldResponse(string $message, array $context): bool
    {
        // Check if we're in an update context
        if (isset($context['update_action']) || isset($context['client_identifier'])) {
            $message = strtolower(trim($message));

            // Check for update patterns
            $updatePatterns = [
                '/update\s+her\s+(\w+)/i',
                '/update\s+his\s+(\w+)/i',
                '/change\s+her\s+(\w+)/i',
                '/change\s+his\s+(\w+)/i',
                '/set\s+her\s+(\w+)/i',
                '/set\s+his\s+(\w+)/i',
                '/to\s+([^\s]+@[^\s]+)/i', // email pattern
                '/email\s+to\s+([^\s]+@[^\s]+)/i',
                '/phone\s+to\s+([^\s]+)/i'
            ];

            foreach ($updatePatterns as $pattern) {
                if (preg_match($pattern, $message)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Extract update field data from message
     */
    private function extractUpdateFieldData(string $message, array $context): array
    {
        $data = [];
        $message = strtolower(trim($message));

        // Extract email updates
        if (preg_match('/email\s+to\s+([^\s]+@[^\s]+)/i', $message, $matches)) {
            $data['email'] = $matches[1];
        } elseif (preg_match('/to\s+([^\s]+@[^\s]+)/i', $message, $matches)) {
            $data['email'] = $matches[1];
        }

        // Extract phone updates
        if (preg_match('/phone\s+to\s+([^\s]+)/i', $message, $matches)) {
            $data['phone'] = $matches[1];
        }

        // Extract name updates
        if (preg_match('/name\s+to\s+([^,\s]+(?:\s+[^,\s]+)*)/i', $message, $matches)) {
            $name = trim($matches[1]);
            $nameParts = explode(' ', $name);
            if (count($nameParts) >= 2) {
                $data['first_name'] = ucfirst(strtolower($nameParts[0]));
                $data['last_name'] = ucfirst(strtolower($nameParts[1]));
            } else {
                $data['first_name'] = ucfirst(strtolower($name));
            }
        }

        return $data;
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
