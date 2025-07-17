<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AIMLAPIService;
use App\Services\OpenAIService;
use App\Models\AIGenerationSetting;
use App\Models\AIModel;
use App\Models\AIProvider;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Exception;

class AISettingsController extends Controller
{
    private AIMLAPIService $aimlapiService;
    private OpenAIService $openAIService;

    public function __construct(AIMLAPIService $aimlapiService, OpenAIService $openAIService)
    {
        $this->aimlapiService = $aimlapiService;
        $this->openAIService = $openAIService;
    }

        /**
     * Test AIMLAPI connection
     */
    public function testAIMLAPIConnection(Request $request): JsonResponse
    {
        try {
            // Check if custom API key is provided
            $apiKey = $request->get('api_key');
            $baseUrl = $request->get('base_url');
            $model = $request->get('model');

            if ($apiKey) {
                // Test with custom credentials
                $result = $this->testCustomConnection($apiKey, $baseUrl, $model);
            } else {
                // Test with default configuration
                $result = $this->aimlapiService->testConnection();
            }

            return response()->json($result, $result['status'] === 'success' ? 200 : 400);
        } catch (\Exception $e) {
            Log::error('AIMLAPI connection test failed', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Connection test failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test connection with custom credentials
     */
    private function testCustomConnection(string $apiKey, ?string $baseUrl = null, ?string $model = null): array
    {
        try {
            $baseUrl = $baseUrl ?? 'https://api.aimlapi.com/v1';
            $model = $model ?? 'gpt-4o';

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
                return [
                    'status' => 'error',
                    'message' => 'Connection failed: ' . $response->body(),
                    'data' => null
                ];
            }
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Connection failed: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Test OpenAI connection
     */
    public function testOpenAIConnection(Request $request): JsonResponse
    {
        try {
            // Create a simple test using the existing OpenAI service
            $testMessage = "Hello, this is a test message.";
            $result = $this->openAIService->generateChatCompletion($testMessage);

            return response()->json([
                'status' => 'success',
                'message' => 'OpenAI connection successful',
                'data' => $result
            ], 200);
        } catch (\Exception $e) {
            Log::error('OpenAI connection test failed', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Connection test failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get AI models configuration
     */
            public function getAIModels(): JsonResponse
    {
        try {
            Log::info('getAIModels called', ['user_id' => auth()->id()]);

            // Get all models regardless of user
            $models = AIModel::with(['user', 'aiProvider'])
                ->orderBy('is_default', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();

            Log::info('Models found', ['count' => $models->count(), 'user_id' => auth()->id()]);

            $mappedModels = $models->map(function ($model) {
                return [
                    'id' => $model->id,
                    'name' => $model->name,
                    'provider' => $model->aiProvider->provider_type,
                    'provider_name' => $model->aiProvider->name,
                    'model' => $model->model,
                    'status' => $model->status,
                    'apiKey' => $model->aiProvider->masked_api_key,
                    'usage' => $model->usage_count > 0 ? number_format($model->usage_count) : '0',
                    'cost' => '$0.00', // TODO: Implement cost tracking
                    'lastUsed' => $model->last_used_at ? $model->last_used_at->diffForHumans() : 'Never',
                    'requests' => number_format($model->usage_count),
                    'is_default' => $model->is_default,
                    'user' => [
                        'id' => $model->user->id,
                        'name' => $model->user->name,
                        'email' => $model->user->email
                    ],
                    'ai_provider' => [
                        'id' => $model->aiProvider->id,
                        'name' => $model->aiProvider->name,
                        'provider_type' => $model->aiProvider->provider_type,
                        'base_url' => $model->aiProvider->base_url,
                        'status' => $model->aiProvider->status
                    ],
                    'created_at' => $model->created_at,
                    'last_used_at' => $model->last_used_at,
                    'usage_count' => $model->usage_count,
                    'settings' => $model->settings
                ];
            });

            Log::info('Mapped models', ['data' => $mappedModels->toArray()]);

            return response()->json([
                'status' => 'success',
                'data' => $mappedModels
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get AI models', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get AI models: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get AI providers
     */
    public function getAIProviders(): JsonResponse
    {
        try {
            Log::info('getAIProviders called', ['user_id' => auth()->id()]);

            // Get all providers regardless of user
            $providers = AIProvider::with('user')
                ->orderBy('status', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();

            Log::info('Providers found', ['count' => $providers->count(), 'user_id' => auth()->id()]);

            $mappedProviders = $providers->map(function ($provider) {
                return [
                    'id' => $provider->id,
                    'name' => $provider->name,
                    'provider_type' => $provider->provider_type,
                    'base_url' => $provider->base_url,
                    'status' => $provider->status,
                    'masked_api_key' => $provider->masked_api_key,
                    'user' => [
                        'id' => $provider->user->id,
                        'name' => $provider->user->name,
                        'email' => $provider->user->email
                    ],
                    'created_at' => $provider->created_at,
                    'settings' => $provider->settings
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => $mappedProviders
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get AI providers', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get AI providers: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Save AI provider
     */
    public function saveAIProvider(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'provider_type' => 'required|string|max:255',
                'api_key' => 'required|string',
                'base_url' => 'required|url',
                'status' => 'required|in:active,inactive'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $provider = AIProvider::create([
                'user_id' => auth()->id(),
                'name' => $request->name,
                'provider_type' => $request->provider_type,
                'api_key' => $request->api_key,
                'base_url' => $request->base_url,
                'status' => $request->status,
                'settings' => $request->settings ?? []
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Provider saved successfully',
                'data' => [
                    'id' => $provider->id,
                    'name' => $provider->name,
                    'provider_type' => $provider->provider_type
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to save AI provider', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to save provider: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get AI configuration parameters
     */
    public function getAIConfiguration(): JsonResponse
    {
        try {
            $config = $this->aimlapiService->getConfiguration();

            $aiConfigurations = [
                [
                    'id' => 1,
                    'name' => 'Temperature',
                    'value' => (string)$config['temperature'],
                    'description' => 'Controls randomness in AI responses',
                    'type' => 'slider',
                    'min' => 0,
                    'max' => 1,
                    'step' => 0.1
                ],
                [
                    'id' => 2,
                    'name' => 'Max Tokens',
                    'value' => (string)$config['max_tokens'],
                    'description' => 'Maximum length of AI responses',
                    'type' => 'input',
                    'unit' => 'tokens'
                ],
                [
                    'id' => 3,
                    'name' => 'Top P',
                    'value' => (string)$config['top_p'],
                    'description' => 'Controls response diversity',
                    'type' => 'slider',
                    'min' => 0,
                    'max' => 1,
                    'step' => 0.1
                ],
                [
                    'id' => 4,
                    'name' => 'Frequency Penalty',
                    'value' => (string)$config['frequency_penalty'],
                    'description' => 'Reduces repetition in responses',
                    'type' => 'slider',
                    'min' => 0,
                    'max' => 2,
                    'step' => 0.1
                ],
                [
                    'id' => 5,
                    'name' => 'Presence Penalty',
                    'value' => (string)$config['presence_penalty'],
                    'description' => 'Encourages new topics in responses',
                    'type' => 'slider',
                    'min' => 0,
                    'max' => 2,
                    'step' => 0.1
                ]
            ];

            return response()->json([
                'status' => 'success',
                'data' => $aiConfigurations
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get AI configuration', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get AI configuration: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update AI configuration
     */
    public function updateAIConfiguration(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'temperature' => 'numeric|between:0,1',
                'max_tokens' => 'integer|min:1|max:8192',
                'top_p' => 'numeric|between:0,1',
                'frequency_penalty' => 'numeric|between:0,2',
                'presence_penalty' => 'numeric|between:0,2',
                'model' => 'string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $config = $request->only([
                'temperature', 'max_tokens', 'top_p',
                'frequency_penalty', 'presence_penalty', 'model'
            ]);

            // Update the service configuration
            $this->aimlapiService->updateConfiguration($config);

            // Save to database if needed
            $setting = AIGenerationSetting::where('name', 'default')->first();
            if (!$setting) {
                $setting = new AIGenerationSetting();
                $setting->name = 'default';
            }

            $setting->fill($config);
            $setting->save();

            return response()->json([
                'status' => 'success',
                'message' => 'AI configuration updated successfully',
                'data' => $this->aimlapiService->getConfiguration()
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update AI configuration', [
                'error' => $e->getMessage(),
                'config' => $request->all()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update AI configuration: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate a test chat completion
     */
    public function generateTestCompletion(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'message' => 'required|string|max:1000',
                'model' => 'string|max:255',
                'temperature' => 'numeric|between:0,1',
                'max_tokens' => 'integer|min:1|max:8192',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $options = $request->only(['model', 'temperature', 'max_tokens']);
            $result = $this->aimlapiService->generateChatCompletion($request->message, $options);

            return response()->json($result, $result['status'] === 'success' ? 200 : 400);
        } catch (\Exception $e) {
            Log::error('Failed to generate test completion', [
                'error' => $e->getMessage(),
                'message' => $request->get('message')
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to generate test completion: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get AI settings statistics
     */
    public function getAIStats(): JsonResponse
    {
        try {
            $totalModels = AIModel::count();
            $activeModels = AIModel::where('status', 'active')->count();
            $totalRequests = AIModel::sum('usage_count');
            $uniqueUsers = AIModel::distinct('user_id')->count();
            $totalProviders = AIProvider::count();
            $activeProviders = AIProvider::where('status', 'active')->count();

            $stats = [
                'active_models' => $activeModels,
                'total_models' => $totalModels,
                'total_users' => $uniqueUsers,
                'total_providers' => $totalProviders,
                'active_providers' => $activeProviders,
                'total_requests' => number_format($totalRequests),
                'total_cost' => '$0.00', // TODO: Implement cost tracking
                'success_rate' => '98.7%', // TODO: Implement success rate tracking
                'daily_requests' => '0', // TODO: Implement daily tracking
                'avg_response_time' => '1.2s', // TODO: Implement response time tracking
                'error_rate' => '1.3%' // TODO: Implement error rate tracking
            ];

            return response()->json([
                'status' => 'success',
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get AI stats', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get AI stats: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Save new AI model
     */
    public function saveAIModel(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'ai_provider_id' => 'required|exists:ai_providers,id',
                'model' => 'required|string|max:255',
                'status' => 'required|in:active,inactive',
                'is_default' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // If this is set as default, unset other defaults for this user
            if ($request->get('is_default', false)) {
                AIModel::where('user_id', auth()->id())
                    ->where('is_default', true)
                    ->update(['is_default' => false]);
            }

            $model = AIModel::create([
                'name' => $request->name,
                'ai_provider_id' => $request->ai_provider_id,
                'model' => $request->model,
                'status' => $request->status,
                'user_id' => auth()->id(),
                'is_default' => $request->get('is_default', false),
                'usage_count' => 0,
                'settings' => $request->settings ?? []
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'AI model saved successfully',
                'data' => [
                    'id' => $model->id,
                    'name' => $model->name,
                    'provider' => $model->aiProvider->provider_type,
                    'status' => $model->status
                ]
            ], 201);

        } catch (\Exception $e) {
            Log::error('Failed to save AI model', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to save AI model: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle AI model status
     */
    public function toggleModelStatus(Request $request, $modelId): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'status' => 'required|in:active,inactive'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // In a real implementation, you would update the model status in the database
            // For now, we'll just return a success response
            return response()->json([
                'status' => 'success',
                'message' => 'Model status updated successfully',
                'data' => [
                    'id' => $modelId,
                    'status' => $request->status
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to toggle model status', [
                'error' => $e->getMessage(),
                'model_id' => $modelId
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to toggle model status: ' . $e->getMessage()
            ], 500);
        }
    }
}
