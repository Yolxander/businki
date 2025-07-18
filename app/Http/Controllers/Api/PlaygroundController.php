<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PromptTemplate;
use App\Models\AIModel;
use App\Models\AIGenerationLog;
use App\Services\OpenAIService;
use App\Services\AIMLAPIService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Exception;

class PlaygroundController extends Controller
{
    private OpenAIService $openAIService;
    private AIMLAPIService $aimlapiService;

    public function __construct(OpenAIService $openAIService, AIMLAPIService $aimlapiService)
    {
        $this->openAIService = $openAIService;
        $this->aimlapiService = $aimlapiService;
    }

    /**
     * Get all prompt templates
     */
    public function getTemplates(): JsonResponse
    {
        try {
            $templates = PromptTemplate::where('is_active', true)
                ->orderBy('name')
                ->get();

            $mappedTemplates = $templates->map(function ($template) {
                return [
                    'id' => $template->id,
                    'name' => $template->name,
                    'type' => $template->type,
                    'description' => $template->description,
                    'template' => $template->template,
                    'is_active' => $template->is_active,
                    'created_at' => $template->created_at,
                    'updated_at' => $template->updated_at
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => $mappedTemplates
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get prompt templates', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get prompt templates: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate AI response
     */
    public function generate(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'model_id' => 'required|exists:ai_models,id',
                'prompt' => 'required|string|max:10000',
                'template_id' => 'nullable|exists:prompt_templates,id',
                'parameters' => 'nullable|array'
            ]);

            $model = AIModel::with('aiProvider')->findOrFail($request->model_id);
            $template = $request->template_id ? PromptTemplate::find($request->template_id) : null;
            $parameters = $request->parameters ?? [];

            // Use the rendered prompt from the request (which includes variable replacements)
            $prompt = $request->prompt;

            // Generate response based on provider type
            $response = $this->generateResponse($model, $prompt, $parameters);

            // Log the generation
            $this->logGeneration($model, $prompt, $response, $template);

            return response()->json([
                'status' => 'success',
                'data' => $response
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to generate AI response', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to generate response: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test a prompt template
     */
    public function testTemplate(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'template_id' => 'required|exists:prompt_templates,id',
                'model_id' => 'nullable|exists:ai_models,id',
                'parameters' => 'nullable|array'
            ]);

            $template = PromptTemplate::findOrFail($request->template_id);
            $model = $request->model_id ? AIModel::with('aiProvider')->findOrFail($request->model_id) : null;
            $parameters = $request->parameters ?? [];

            // If no model specified, use the first available active model
            if (!$model) {
                $model = AIModel::with('aiProvider')
                    ->where('status', 'active')
                    ->orderBy('is_default', 'desc')
                    ->first();

                if (!$model) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'No active AI model available'
                    ], 400);
                }
            }

            // Generate response using the template
            $response = $this->generateResponse($model, $template->template, $parameters);

            // Log the generation
            $this->logGeneration($model, $template->template, $response, $template);

            return response()->json([
                'status' => 'success',
                'data' => $response
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to test template', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to test template: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate response using the appropriate service
     */
    private function generateResponse(AIModel $model, string $prompt, array $parameters): array
    {
        $providerType = $model->aiProvider->provider_type;
        $temperature = $parameters['temperature'] ?? 0.7;
        $maxTokens = $parameters['maxTokens'] ?? 2000;
        $topP = $parameters['topP'] ?? 1.0;

        try {
            if ($providerType === 'openai') {
                $result = $this->openAIService->generateChatCompletionWithParams(
                    $prompt,
                    $model->model,
                    $temperature,
                    $maxTokens,
                    $topP
                );
                        } else {
                // Use AIMLAPI for other providers
                // Get provider configuration
                $providerConfig = [
                    'api_key' => $model->aiProvider->api_key,
                    'base_url' => $model->aiProvider->base_url,
                    'settings' => $model->aiProvider->settings ?? []
                ];

                // Validate provider configuration
                $validationErrors = $this->aimlapiService->validateProviderConfig($providerConfig);
                if (!empty($validationErrors)) {
                    throw new Exception('Provider configuration error: ' . implode(', ', $validationErrors));
                }

                $result = $this->aimlapiService->generateChatCompletionWithParams(
                    $prompt,
                    $model->model,
                    $temperature,
                    $maxTokens,
                    $topP,
                    $providerConfig
                );
            }

            // Update model usage count
            $model->increment('usage_count');
            $model->update(['last_used_at' => now()]);

            return [
                'response' => $result['content'] ?? $result['response'] ?? 'No response generated',
                'tokens' => $result['tokens'] ?? 0,
                'cost' => $result['cost'] ?? '$0.00',
                'model' => $model->name,
                'provider' => $model->aiProvider->name
            ];
        } catch (Exception $e) {
            Log::error('AI generation failed', [
                'model' => $model->name,
                'provider' => $providerType,
                'provider_name' => $model->aiProvider->name,
                'error' => $e->getMessage()
            ]);

            // Provide more specific error messages
            $errorMessage = $e->getMessage();
            if (str_contains($errorMessage, '401')) {
                $errorMessage = 'Authentication failed. Please check your API key and try again.';
            } elseif (str_contains($errorMessage, '403')) {
                $errorMessage = 'Access denied. Please check your API permissions.';
            } elseif (str_contains($errorMessage, '429')) {
                $errorMessage = 'Rate limit exceeded. Please try again later.';
            } elseif (str_contains($errorMessage, '500')) {
                $errorMessage = 'Server error. Please try again later.';
            }

            throw new Exception('AI generation failed: ' . $errorMessage);
        }
    }

    /**
     * Log the AI generation
     */
    private function logGeneration(AIModel $model, string $prompt, array $response, ?PromptTemplate $template = null): void
    {
        try {
            AIGenerationLog::create([
                'user_id' => auth()->id(),
                'model_id' => $model->id,
                'prompt' => $prompt,
                'response' => $response['response'],
                'tokens_used' => $response['tokens'] ?? 0,
                'cost' => $response['cost'] ?? '$0.00',
                'template_id' => $template?->id,
                'status' => 'success',
                'execution_time' => 0, // TODO: Add execution time tracking
                'metadata' => [
                    'model_name' => $model->name,
                    'provider' => $model->aiProvider->name,
                    'template_name' => $template?->name
                ]
            ]);
        } catch (Exception $e) {
            Log::error('Failed to log AI generation', [
                'error' => $e->getMessage()
            ]);
        }
    }
}
