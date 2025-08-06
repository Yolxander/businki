<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AIIntentDetectionService;
use App\Services\AIMLAPIService;
use App\Models\AIModel;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Exception;

class IntentDetectionController extends Controller
{
    private AIIntentDetectionService $aiIntentDetectionService;
    private AIMLAPIService $aimlapiService;

    public function __construct(
        AIIntentDetectionService $aiIntentDetectionService,
        AIMLAPIService $aimlapiService
    ) {
        $this->aiIntentDetectionService = $aiIntentDetectionService;
        $this->aimlapiService = $aimlapiService;
    }

    /**
     * Detect intent from user message
     */
    public function detectIntent(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'message' => 'required|string|max:1000',
                'context' => 'array',
                'ai_model_id' => 'nullable|exists:ai_models,id',
                'use_ai' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $message = $request->input('message');
            $context = $request->input('context', []);
            $useAI = $request->input('use_ai', true);
            $aiModelId = $request->input('ai_model_id');

            $aiModel = null;
            if ($aiModelId) {
                $aiModel = AIModel::find($aiModelId);
            }

            if ($useAI) {
                $intent = $this->aiIntentDetectionService->detectIntent($message, $context, $aiModel);
            } else {
                // Use rule-based detection only
                $intent = $this->aiIntentDetectionService->detectIntentWithRules($message, $context);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Intent detected successfully',
                'data' => $intent
            ]);

        } catch (Exception $e) {
            Log::error('Intent detection failed', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Intent detection failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test intent detection with different AI models
     */
    public function testIntentDetection(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'message' => 'required|string|max:1000',
                'context' => 'array',
                'model' => 'string',
                'provider' => 'string|in:openai,aimlapi'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $message = $request->input('message');
            $context = $request->input('context', []);
            $model = $request->input('model', 'gpt-4o');
            $provider = $request->input('provider', 'aimlapi');

            $options = [
                'model' => $model,
                'temperature' => 0.1,
                'max_tokens' => 1000,
                'top_p' => 0.9
            ];

            $result = [];
            $startTime = microtime(true);

            // Use AIMLAPI for all providers (including OpenAI models)
            $result = $this->aimlapiService->generateIntentDetection($message, $context, $options);

            $endTime = microtime(true);
            $responseTime = ($endTime - $startTime) * 1000; // Convert to milliseconds

            return response()->json([
                'status' => 'success',
                'message' => 'Intent detection test completed',
                'data' => [
                    'intent' => $result['parsed_intent'] ?? [],
                    'raw_response' => $result['content'] ?? '',
                    'tokens' => $result['tokens'] ?? 0,
                    'cost' => $result['cost'] ?? '$0.00',
                    'response_time_ms' => round($responseTime, 2),
                    'model' => $model,
                    'provider' => $provider
                ]
            ]);

        } catch (Exception $e) {
            Log::error('Intent detection test failed', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Intent detection test failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available AI models for intent detection
     */
    public function getAvailableModels(): JsonResponse
    {
        try {
            $aimlapiModels = $this->aimlapiService->getIntentDetectionModels();

            // Get available models from database
            $dbModels = AIModel::active()->with('aiProvider')->get()->map(function ($model) {
                return [
                    'id' => $model->id,
                    'name' => $model->name,
                    'model' => $model->model,
                    'provider' => $model->aiProvider->provider_type,
                    'provider_name' => $model->aiProvider->name,
                    'is_default' => $model->is_default,
                    'usage_count' => $model->usage_count
                ];
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Available models retrieved successfully',
                'data' => [
                    'aimlapi_models' => $aimlapiModels,
                    'database_models' => $dbModels
                ]
            ]);

        } catch (Exception $e) {
            Log::error('Failed to get available models', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get available models: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get intent detection statistics
     */
    public function getStats(): JsonResponse
    {
        try {
            $stats = $this->aiIntentDetectionService->getIntentDetectionStats();

            return response()->json([
                'status' => 'success',
                'message' => 'Statistics retrieved successfully',
                'data' => $stats
            ]);

        } catch (Exception $e) {
            Log::error('Failed to get intent detection stats', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get statistics: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Batch intent detection for multiple messages
     */
    public function batchDetectIntent(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'messages' => 'required|array|min:1|max:10',
                'messages.*' => 'required|string|max:1000',
                'context' => 'array',
                'ai_model_id' => 'nullable|exists:ai_models,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $messages = $request->input('messages');
            $context = $request->input('context', []);
            $aiModelId = $request->input('ai_model_id');

            $aiModel = null;
            if ($aiModelId) {
                $aiModel = AIModel::find($aiModelId);
            }

            $results = [];
            $startTime = microtime(true);

            foreach ($messages as $index => $message) {
                $messageStartTime = microtime(true);

                try {
                    $intent = $this->aiIntentDetectionService->detectIntent($message, $context, $aiModel);
                    $messageEndTime = microtime(true);

                    $results[] = [
                        'index' => $index,
                        'message' => $message,
                        'intent' => $intent,
                        'response_time_ms' => round(($messageEndTime - $messageStartTime) * 1000, 2),
                        'status' => 'success'
                    ];
                } catch (Exception $e) {
                    $results[] = [
                        'index' => $index,
                        'message' => $message,
                        'intent' => [
                            'type' => 'general',
                            'action' => 'none',
                            'confidence' => 0.0,
                            'entities' => [],
                            'error' => $e->getMessage()
                        ],
                        'status' => 'error',
                        'error' => $e->getMessage()
                    ];
                }
            }

            $endTime = microtime(true);
            $totalTime = ($endTime - $startTime) * 1000;

            return response()->json([
                'status' => 'success',
                'message' => 'Batch intent detection completed',
                'data' => [
                    'results' => $results,
                    'total_messages' => count($messages),
                    'successful_detections' => count(array_filter($results, fn($r) => $r['status'] === 'success')),
                    'failed_detections' => count(array_filter($results, fn($r) => $r['status'] === 'error')),
                    'total_time_ms' => round($totalTime, 2),
                    'average_time_ms' => round($totalTime / count($messages), 2)
                ]
            ]);

        } catch (Exception $e) {
            Log::error('Batch intent detection failed', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Batch intent detection failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
