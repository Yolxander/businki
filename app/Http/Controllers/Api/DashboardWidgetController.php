<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DashboardWidget;
use App\Services\OpenAIService;
use App\Services\ContextAwareService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class DashboardWidgetController extends Controller
{
    private OpenAIService $openAIService;
    private ContextAwareService $contextAwareService;

    public function __construct(OpenAIService $openAIService, ContextAwareService $contextAwareService)
    {
        $this->openAIService = $openAIService;
        $this->contextAwareService = $contextAwareService;
    }

    /**
     * Get widget information for editing
     */
    public function getWidgetInfo(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'widget_type' => 'required|string',
            'widget_key' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $widget = DashboardWidget::where('user_id', Auth::id())
                ->where('widget_type', $request->widget_type)
                ->where('widget_key', $request->widget_key)
                ->first();

            if (!$widget) {
                // Create default widget info
                $widget = new DashboardWidget([
                    'user_id' => Auth::id(),
                    'widget_type' => $request->widget_type,
                    'widget_key' => $request->widget_key,
                    'title' => $this->getDefaultTitle($request->widget_type),
                    'description' => $this->getDefaultDescription($request->widget_type),
                    'configuration' => $this->getDefaultConfiguration($request->widget_type),
                    'is_ai_generated' => false,
                ]);
            }

            return response()->json([
                'status' => 'success',
                'data' => $widget
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting widget info', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'widget_type' => $request->widget_type,
                'widget_key' => $request->widget_key,
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get widget information'
            ], 500);
        }
    }

        /**
     * Generate widget content using AI
     */
    public function generateWidget(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'widget_type' => 'required|string',
            'widget_key' => 'required|string',
            'user_prompt' => 'required|string',
            'current_configuration' => 'array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $widgetType = $request->widget_type;
            $userPrompt = $request->user_prompt;
            $currentConfig = $request->current_configuration ?? [];

            // Get platform and user context for AI
            $this->contextAwareService->refreshUserContext();
            $platformContext = $this->contextAwareService->getPlatformContext();
            $userContext = $this->contextAwareService->getUserContext();

            // Build comprehensive AI prompt with full context - let AI handle the analysis
            $aiPrompt = $this->buildContextAwareWidgetPrompt($widgetType, $userPrompt, $currentConfig, $platformContext, $userContext);

            // Generate content using AI
            $response = $this->openAIService->generateChatCompletionWithParams(
                $aiPrompt,
                config('services.openai.model'),
                0.7,
                4000
            );

            if (!isset($response['content'])) {
                Log::error('OpenAI response missing content', [
                    'response' => $response,
                    'user_id' => Auth::id(),
                    'widget_type' => $widgetType,
                    'widget_key' => $request->widget_key,
                ]);
                throw new \Exception('Failed to generate widget content');
            }

            // Log the raw AI response for debugging
            Log::info('Raw AI Response', [
                'user_id' => Auth::id(),
                'widget_type' => $widgetType,
                'widget_key' => $request->widget_key,
                'raw_response' => $response['content']
            ]);

            try {
                $generatedContent = json_decode($response['content'], true);

                if (!$generatedContent) {
                    throw new \Exception('Invalid JSON response from AI: ' . $response['content']);
                }
            } catch (\Exception $e) {
                Log::error('JSON decode error', [
                    'error' => $e->getMessage(),
                    'raw_response' => $response['content'],
                    'user_id' => Auth::id(),
                    'widget_type' => $widgetType,
                    'widget_key' => $request->widget_key,
                ]);
                throw new \Exception('Failed to parse AI response: ' . $e->getMessage());
            }

            // Log the AI response for debugging
            Log::info('AI Response for widget generation', [
                'user_id' => Auth::id(),
                'widget_type' => $widgetType,
                'widget_key' => $request->widget_key,
                'user_prompt' => $userPrompt,
                'ai_response' => $generatedContent,
                'raw_response' => $response['content']
            ]);

            // Validate AI response structure
            if (!is_array($generatedContent)) {
                throw new \Exception('AI response is not an array: ' . json_encode($generatedContent));
            }

            // Ensure required fields are strings if they exist
            $requiredStringFields = ['title', 'description'];
            foreach ($requiredStringFields as $field) {
                if (isset($generatedContent[$field]) && !is_string($generatedContent[$field])) {
                    throw new \Exception("AI response field '{$field}' must be a string, got: " . gettype($generatedContent[$field]));
                }
            }

            // Ensure configuration is an array if it exists
            if (isset($generatedContent['configuration']) && !is_array($generatedContent['configuration'])) {
                throw new \Exception("AI response field 'configuration' must be an array, got: " . gettype($generatedContent['configuration']));
            }

            // Check if AI determined the request cannot be fulfilled
            if (isset($generatedContent['can_fulfill']) && !$generatedContent['can_fulfill']) {
                return response()->json([
                    'status' => 'error',
                    'message' => $generatedContent['reason'] ?? 'Request cannot be fulfilled',
                    'can_fulfill' => false,
                    'suggestions' => $generatedContent['suggestions'] ?? [],
                    'available_options' => $generatedContent['available_options'] ?? []
                ], 400);
            }

            // Validate the generated configuration if it exists
            if (isset($generatedContent['configuration'])) {
                try {
                    $validation = $this->contextAwareService->validateWidgetConfiguration($generatedContent['configuration']);

                    if (!$validation['is_valid']) {
                        return response()->json([
                            'status' => 'error',
                            'message' => 'Generated widget configuration is invalid',
                            'errors' => $validation['errors'],
                            'warnings' => $validation['warnings'],
                            'suggestions' => $validation['suggestions']
                        ], 400);
                    }
                } catch (\Exception $e) {
                    Log::error('Configuration validation error', [
                        'error' => $e->getMessage(),
                        'configuration' => $generatedContent['configuration']
                    ]);
                    throw new \Exception('Configuration validation failed: ' . $e->getMessage());
                }
            }

            // Save or update widget
            try {
                $widget = DashboardWidget::updateOrCreate(
                    [
                        'user_id' => Auth::id(),
                        'widget_type' => $widgetType,
                        'widget_key' => $request->widget_key,
                    ],
                    [
                        'title' => $generatedContent['title'] ?? $this->getDefaultTitle($widgetType),
                        'description' => $generatedContent['description'] ?? $this->getDefaultDescription($widgetType),
                        'configuration' => $generatedContent['configuration'] ?? $currentConfig,
                        'ai_prompt' => [
                            'user_prompt' => $userPrompt,
                            'system_prompt' => $aiPrompt,
                            'platform_context' => $platformContext,
                            'user_context' => $userContext
                        ],
                        'ai_response' => $generatedContent,
                        'generation_metadata' => [
                            'tokens_used' => $response['usage']['total_tokens'] ?? null,
                            'cost' => $response['cost'] ?? null,
                            'model' => config('services.openai.model'),
                            'temperature' => 0.7,
                            'max_tokens' => 4000,
                            'response_keys' => array_keys($response),
                        ],
                        'is_ai_generated' => true,
                        'is_active' => true,
                    ]
                );
            } catch (\Exception $e) {
                Log::error('Database save error', [
                    'error' => $e->getMessage(),
                    'generated_content' => $generatedContent,
                    'user_id' => Auth::id(),
                    'widget_type' => $widgetType,
                    'widget_key' => $request->widget_key,
                ]);
                throw new \Exception('Failed to save widget to database: ' . $e->getMessage());
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Widget generated successfully',
                'data' => $widget
            ]);

        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();

            // Safely log the error without causing array to string conversion
            Log::error('Error generating widget', [
                'error' => $errorMessage,
                'user_id' => Auth::id(),
                'widget_type' => $request->widget_type ?? 'unknown',
                'widget_key' => $request->widget_key ?? 'unknown',
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to generate widget: ' . $errorMessage
            ], 500);
        }
    }

    /**
     * Update widget configuration
     */
    public function updateWidget(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'widget_type' => 'required|string',
            'widget_key' => 'required|string',
            'title' => 'required|string',
            'description' => 'nullable|string',
            'configuration' => 'array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $widget = DashboardWidget::updateOrCreate(
                [
                    'user_id' => Auth::id(),
                    'widget_type' => $request->widget_type,
                    'widget_key' => $request->widget_key,
                ],
                [
                    'title' => $request->title,
                    'description' => $request->description,
                    'configuration' => $request->configuration,
                    'is_ai_generated' => false,
                    'is_active' => true,
                ]
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Widget updated successfully',
                'data' => $widget
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating widget', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update widget'
            ], 500);
        }
    }

    /**
     * Get user's dashboard widgets
     */
    public function getUserWidgets(): JsonResponse
    {
        try {
            $widgets = DashboardWidget::where('user_id', Auth::id())
                ->where('is_active', true)
                ->orderBy('position')
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $widgets
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting user widgets', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get widgets'
            ], 500);
        }
    }

    /**
     * Delete a widget
     */
    public function deleteWidget(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'widget_type' => 'required|string',
            'widget_key' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $widget = DashboardWidget::where('user_id', Auth::id())
                ->where('widget_type', $request->widget_type)
                ->where('widget_key', $request->widget_key)
                ->first();

            if (!$widget) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Widget not found'
                ], 404);
            }

            // Soft delete by setting is_active to false
            $widget->update(['is_active' => false]);

            return response()->json([
                'status' => 'success',
                'message' => 'Widget deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error deleting widget', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete widget'
            ], 500);
        }
    }

        /**
     * Build context-aware AI prompt for widget generation
     */
    private function buildContextAwareWidgetPrompt(string $widgetType, string $userPrompt, array $currentConfig, array $platformContext, array $userContext): string
    {
        $basePrompt = "You are an expert dashboard widget designer for a business management platform. Your job is to understand user requests and create appropriate widget configurations based on the available platform data.\n\n";

        $widgetDescriptions = [
            'quick_stats' => 'Quick statistics cards showing key metrics like projects, clients, tasks, revenue',
            'recent_tasks' => 'List of recent tasks with status, priority, due dates, and project information',
            'recent_projects' => 'List of recent projects with progress, status, client, and timeline information',
            'quick_actions' => 'Quick action buttons for common tasks like creating projects, clients, tasks',
            'recent_proposals' => 'List of recent proposals with status, client, and creation date',
        ];

        $prompt = $basePrompt;
        $prompt .= "PLATFORM CONTEXT:\n";
        $prompt .= "- Platform Type: " . ($platformContext['platform_description'] ?? 'Business Management Platform') . "\n";
        $prompt .= "- Available Tables: " . implode(', ', array_keys($platformContext['available_tables'])) . "\n";
        $prompt .= "- Table Descriptions: " . json_encode($platformContext['table_descriptions'] ?? []) . "\n";
        $prompt .= "- Supported Metrics: " . json_encode($platformContext['supported_metrics']) . "\n\n";

        $prompt .= "USER CONTEXT:\n";
        $prompt .= "- User Stats: " . json_encode($userContext['user_stats'] ?? []) . "\n";
        $prompt .= "- User Permissions: " . json_encode($userContext['user_permissions'] ?? []) . "\n\n";

        $prompt .= "WIDGET REQUIREMENTS:\n";
        $prompt .= "- Widget Type: " . ($widgetDescriptions[$widgetType] ?? $widgetType) . "\n";
        $prompt .= "- Current Configuration: " . json_encode($currentConfig) . "\n";
        $prompt .= "- User Request: " . $userPrompt . "\n\n";

        $prompt .= "ANALYSIS INSTRUCTIONS:\n";
        $prompt .= "1. First, analyze if the user's request can be fulfilled with the available data\n";
        $prompt .= "2. If the request mentions data that doesn't exist (like 'school', 'students', etc.), explain why it's not available\n";
        $prompt .= "3. If the request can be fulfilled, suggest the best matching metrics and configurations\n";
        $prompt .= "4. Consider synonyms and variations (e.g., 'ticket' = 'task', 'proposal' = 'quote')\n";
        $prompt .= "5. Use only the available metrics and tables from the platform context\n\n";

        $prompt .= "RESPONSE FORMAT:\n";
        $prompt .= "If the request CAN be fulfilled, respond with:\n";
        $prompt .= "{\n";
        $prompt .= "  \"can_fulfill\": true,\n";
        $prompt .= "  \"title\": \"Widget Title\",\n";
        $prompt .= "  \"description\": \"Widget description\",\n";
        $prompt .= "  \"configuration\": {\n";
        $prompt .= "    \"metric_type\": \"one of: clients, projects, tasks, subtasks, proposals, revenue\",\n";
        $prompt .= "    \"metric_filter\": \"appropriate filter for the metric type\",\n";
        $prompt .= "    \"icon\": \"appropriate Lucide React icon name\",\n";
        $prompt .= "    \"trend\": \"trend information if applicable\",\n";
        $prompt .= "    \"show_trend\": true/false,\n";
        $prompt .= "    \"refresh_interval\": 300\n";
        $prompt .= "  }\n";
        $prompt .= "}\n\n";

        $prompt .= "If the request CANNOT be fulfilled, respond with:\n";
        $prompt .= "{\n";
        $prompt .= "  \"can_fulfill\": false,\n";
        $prompt .= "  \"reason\": \"Explanation of why the request cannot be fulfilled\",\n";
        $prompt .= "  \"suggestions\": [\"List of alternative suggestions\"],\n";
        $prompt .= "  \"available_options\": [\"List of what is actually available\"]\n";
        $prompt .= "}\n";

        return $prompt;
    }

    /**
     * Build AI prompt for widget generation (legacy method)
     */
    private function buildWidgetPrompt(string $widgetType, string $userPrompt, array $currentConfig): string
    {
        $basePrompt = "You are an expert dashboard widget designer. Generate a JSON response for a dashboard widget based on the user's requirements.\n\n";

        $widgetDescriptions = [
            'quick_stats' => 'Quick statistics cards showing key metrics like projects, clients, tasks, revenue',
            'recent_tasks' => 'List of recent tasks with status, priority, due dates, and project information',
            'recent_projects' => 'List of recent projects with progress, status, client, and timeline information',
            'quick_actions' => 'Quick action buttons for common tasks like creating projects, clients, tasks',
            'recent_proposals' => 'List of recent proposals with status, client, and creation date',
        ];

        $prompt = $basePrompt;
        $prompt .= "Widget Type: " . ($widgetDescriptions[$widgetType] ?? $widgetType) . "\n";
        $prompt .= "Current Configuration: " . json_encode($currentConfig) . "\n";
        $prompt .= "User Requirements: " . $userPrompt . "\n\n";
        $prompt .= "Generate a JSON response with the following structure:\n";
        $prompt .= "{\n";
        $prompt .= "  \"title\": \"Widget title\",\n";
        $prompt .= "  \"description\": \"Widget description\",\n";
        $prompt .= "  \"configuration\": {\n";
        $prompt .= "    // Widget-specific configuration\n";
        $prompt .= "  }\n";
        $prompt .= "}\n\n";
        $prompt .= "Make the widget personalized and useful based on the user's requirements. Return only valid JSON.";

        return $prompt;
    }

    /**
     * Get default title for widget type
     */
    private function getDefaultTitle(string $widgetType): string
    {
        $titles = [
            'quick_stats' => 'Quick Stats',
            'recent_tasks' => 'Recent Tasks',
            'recent_projects' => 'Recent Projects',
            'quick_actions' => 'Quick Actions',
            'recent_proposals' => 'Recent Proposals',
        ];

        return $titles[$widgetType] ?? 'Widget';
    }

    /**
     * Get default description for widget type
     */
    private function getDefaultDescription(string $widgetType): string
    {
        $descriptions = [
            'quick_stats' => 'Key metrics and statistics',
            'recent_tasks' => 'Latest task updates',
            'recent_projects' => 'Your active and recent projects',
            'quick_actions' => 'Get things done faster',
            'recent_proposals' => 'Latest client proposals',
        ];

        return $descriptions[$widgetType] ?? 'Dashboard widget';
    }

    /**
     * Get default configuration for widget type
     */
    private function getDefaultConfiguration(string $widgetType): array
    {
        $configs = [
            'quick_stats' => [
                'show_trends' => true,
                'refresh_interval' => 300,
            ],
            'recent_tasks' => [
                'max_items' => 5,
                'show_priority' => true,
                'show_due_date' => true,
            ],
            'recent_projects' => [
                'max_items' => 5,
                'show_progress' => true,
                'show_client' => true,
            ],
            'quick_actions' => [
                'actions' => [
                    'create_project' => true,
                    'create_client' => true,
                    'create_task' => true,
                    'zen_mode' => true,
                ],
            ],
            'recent_proposals' => [
                'max_items' => 5,
                'show_status' => true,
                'show_client' => true,
            ],
        ];

        return $configs[$widgetType] ?? [];
    }
}
