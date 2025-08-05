<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DashboardWidget;
use App\Services\OpenAIService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class DashboardWidgetController extends Controller
{
    private OpenAIService $openAIService;

    public function __construct(OpenAIService $openAIService)
    {
        $this->openAIService = $openAIService;
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

            // Build AI prompt for widget generation
            $aiPrompt = $this->buildWidgetPrompt($widgetType, $userPrompt, $currentConfig);

            // Generate content using AI
            $response = $this->openAIService->generateChatCompletionWithParams(
                $aiPrompt,
                config('services.openai.model'),
                0.7,
                4000
            );

            if (!isset($response['content'])) {
                throw new \Exception('Failed to generate widget content');
            }

            $generatedContent = json_decode($response['content'], true);

            if (!$generatedContent) {
                throw new \Exception('Invalid JSON response from AI');
            }

            // Save or update widget
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
                    ],
                    'ai_response' => $generatedContent,
                    'generation_metadata' => [
                        'tokens_used' => $response['usage']['total_tokens'] ?? null,
                        'cost' => $response['cost'] ?? null,
                        'model' => config('services.openai.model'),
                        'temperature' => 0.7,
                        'max_tokens' => 4000,
                    ],
                    'is_ai_generated' => true,
                    'is_active' => true,
                ]
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Widget generated successfully',
                'data' => $widget
            ]);

        } catch (\Exception $e) {
            Log::error('Error generating widget', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'widget_type' => $request->widget_type,
                'widget_key' => $request->widget_key,
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to generate widget: ' . $e->getMessage()
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
     * Build AI prompt for widget generation
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
