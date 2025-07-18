<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PromptTemplate;
use App\Models\SavedPrompt;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Exception;

class PromptEngineeringController extends Controller
{
    /**
     * Get saved prompts
     */
    public function getSavedPrompts(): JsonResponse
    {
        try {
            $savedPrompts = SavedPrompt::where('user_id', auth()->id())
                ->orderBy('created_at', 'desc')
                ->get();

            $mappedPrompts = $savedPrompts->map(function ($prompt) {
                return [
                    'id' => $prompt->id,
                    'title' => $prompt->title,
                    'content' => $prompt->content,
                    'category' => $prompt->category,
                    'tags' => $prompt->tags,
                    'usage_count' => $prompt->usage_count,
                    'created_at' => $prompt->created_at,
                    'updated_at' => $prompt->updated_at
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => $mappedPrompts
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get saved prompts', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get saved prompts: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new template
     */
    public function createTemplate(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'type' => 'required|string|max:50',
                'description' => 'required|string|max:500',
                'template' => 'required|string|max:10000',
                'is_active' => 'boolean'
            ]);

            $template = PromptTemplate::create([
                'name' => $request->name,
                'type' => $request->type,
                'description' => $request->description,
                'template' => $request->template,
                'is_active' => $request->is_active ?? true
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Template created successfully',
                'data' => $template
            ], 201);
        } catch (\Exception $e) {
            Log::error('Failed to create template', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create template: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update an existing template
     */
    public function updateTemplate(Request $request, $id): JsonResponse
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'type' => 'required|string|max:50',
                'description' => 'required|string|max:500',
                'template' => 'required|string|max:10000',
                'is_active' => 'boolean'
            ]);

            $template = PromptTemplate::findOrFail($id);

            $template->update([
                'name' => $request->name,
                'type' => $request->type,
                'description' => $request->description,
                'template' => $request->template,
                'is_active' => $request->is_active ?? $template->is_active
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Template updated successfully',
                'data' => $template
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update template', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update template: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a template
     */
    public function deleteTemplate($id): JsonResponse
    {
        try {
            $template = PromptTemplate::findOrFail($id);
            $template->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Template deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to delete template', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete template: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Save a prompt
     */
    public function savePrompt(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'title' => 'required|string|max:255',
                'content' => 'required|string|max:10000',
                'category' => 'nullable|string|max:50',
                'tags' => 'nullable|array'
            ]);

            $savedPrompt = SavedPrompt::create([
                'user_id' => auth()->id(),
                'title' => $request->title,
                'content' => $request->content,
                'category' => $request->category,
                'tags' => $request->tags ?? [],
                'usage_count' => 0
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Prompt saved successfully',
                'data' => $savedPrompt
            ], 201);
        } catch (\Exception $e) {
            Log::error('Failed to save prompt', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to save prompt: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Optimize a prompt
     */
    public function optimizePrompt(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'prompt_id' => 'required',
                'model_id' => 'required|exists:ai_models,id',
                'optimization_type' => 'required|string|in:clarity,conciseness,effectiveness,creativity,structure',
                'prompt_content' => 'required|string|max:10000'
            ]);

            // This would integrate with AI to optimize the prompt
            // For now, return a placeholder response
            $optimizedPrompt = $this->optimizePromptWithAI($request->prompt_content, $request->optimization_type);

            return response()->json([
                'status' => 'success',
                'message' => 'Prompt optimized successfully',
                'data' => [
                    'original' => $request->prompt_content,
                    'optimized' => $optimizedPrompt,
                    'optimization_type' => $request->optimization_type,
                    'model_id' => $request->model_id,
                    'created_at' => now(),
                    'improvements' => [
                        'clarity' => 'Improved clarity and structure for better understanding',
                        'conciseness' => 'Reduced word count while maintaining meaning and impact',
                        'effectiveness' => 'Enhanced for better AI responses and higher success rates',
                        'creativity' => 'Added creative elements and variations for more engaging content',
                        'structure' => 'Restructured for better organization and flow'
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to optimize prompt', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to optimize prompt: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get prompt details
     */
    public function getPromptDetails($id): JsonResponse
    {
        try {
            // Try to find in PromptTemplate first
            $prompt = PromptTemplate::find($id);

            if (!$prompt) {
                // Try to find in SavedPrompt
                $prompt = SavedPrompt::where('id', $id)
                    ->where('user_id', auth()->id())
                    ->first();
            }

            if (!$prompt) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Prompt not found'
                ], 404);
            }

            $data = [
                'id' => $prompt->id,
                'name' => $prompt->name ?? $prompt->title,
                'type' => $prompt->type ?? $prompt->category,
                'description' => $prompt->description ?? '',
                'content' => $prompt->content ?? null,
                'template' => $prompt->template ?? null,
                'is_active' => $prompt->is_active ?? true,
                'created_at' => $prompt->created_at,
                'updated_at' => $prompt->updated_at,
                'usage_count' => $prompt->usage_count ?? 0,
                'tags' => $prompt->tags ?? []
            ];

            return response()->json([
                'status' => 'success',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get prompt details', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get prompt details: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test a prompt with AI model
     */
    public function testPrompt(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'prompt_id' => 'required',
                'model_id' => 'required|exists:ai_models,id',
                'prompt_content' => 'required|string|max:10000'
            ]);

            // This would integrate with AI service to test the prompt
            // For now, return a placeholder response
            return response()->json([
                'status' => 'success',
                'message' => 'Prompt test completed',
                'data' => [
                    'response' => 'This is a test response from the AI model.',
                    'tokens_used' => 150,
                    'cost' => '$0.003',
                    'execution_time' => 2.5,
                    'quality_score' => 8.5
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to test prompt', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to test prompt: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Placeholder method for AI prompt optimization
     */
    private function optimizePromptWithAI(string $prompt, string $optimizationType): string
    {
        // This would integrate with OpenAI or other AI service
        // For now, return a simple optimization
        switch ($optimizationType) {
            case 'clarity':
                return "Clarified: " . $prompt;
            case 'conciseness':
                return "Concise: " . substr($prompt, 0, strlen($prompt) * 0.8);
            case 'effectiveness':
                return "Enhanced: " . $prompt . " (with improved structure)";
            case 'creativity':
                return "Creative: " . $prompt . " (with creative elements)";
            case 'structure':
                return "Restructured: " . $prompt . " (with better organization)";
            default:
                return $prompt;
        }
    }
}
