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
                'prompt_id' => 'nullable',
                'model_id' => 'required|exists:ai_models,id',
                'optimization_type' => 'required|string|in:clarity,conciseness,effectiveness,creativity,structure',
                'prompt_content' => 'required|string|max:10000',
                'prompt_title' => 'nullable|string|max:255',
                'prompt_description' => 'nullable|string|max:1000',
                'prompt_context' => 'nullable|string|max:255',
                'prompt_tags' => 'nullable|array'
            ]);

            // This would integrate with AI to optimize the prompt
            // For now, return a placeholder response
            $optimizedPrompt = $this->optimizePromptWithAI(
                $request->prompt_content,
                $request->optimization_type,
                $request->only(['prompt_title', 'prompt_description', 'prompt_context', 'prompt_tags'])
            );

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
    private function optimizePromptWithAI(string $prompt, string $optimizationType, array $metadata = []): string
    {
        // This would integrate with OpenAI or other AI service
        // For now, return a simple optimization that considers metadata

        // Enhanced optimization based on type and metadata
        switch ($optimizationType) {
            case 'clarity':
                // Add specific instructions for clarity
                if (strpos($prompt, 'branding') !== false) {
                    return "Create 10 distinctive branding concepts for a new eco-friendly product. For each idea, include:\n- Brand name and tagline\n- Visual identity description\n- Target audience\n- Key messaging points\n- Sustainability angle\n\nFocus on originality and market differentiation.";
                }
                return "Please provide a detailed and well-structured response to: " . $prompt . "\n\nInclude specific examples, clear explanations, and actionable insights.";

            case 'conciseness':
                // Make the prompt more concise while keeping the core request
                $words = explode(' ', $prompt);
                if (count($words) > 10) {
                    $core = array_slice($words, 0, 8);
                    return implode(' ', $core) . "...";
                }
                return $prompt;

            case 'effectiveness':
                // Enhance for better AI responses
                if (strpos($prompt, 'branding') !== false) {
                    return "Generate 10 innovative branding strategies for an eco-friendly product. Each strategy should include:\n\n1. Brand Identity: Name, logo concept, color palette\n2. Target Market: Specific demographic and psychographic profile\n3. Value Proposition: Unique selling points and benefits\n4. Marketing Approach: Key channels and messaging strategy\n5. Sustainability Focus: Environmental benefits and green credentials\n\nEnsure each idea is distinct, memorable, and commercially viable.";
                }

                if (strpos($prompt, 'email') !== false || strpos($prompt, 'follow') !== false) {
                    return "Draft a professional follow-up email that:\n\n- Acknowledges the previous interaction\n- Provides clear next steps or updates\n- Maintains a warm, professional tone\n- Includes a specific call-to-action\n- Is concise but comprehensive\n\n" . $prompt;
                }

                if (strpos($prompt, 'checklist') !== false || strpos($prompt, 'audit') !== false) {
                    return "Create a comprehensive checklist for " . $prompt . " that includes:\n\n- Essential items and requirements\n- Quality standards and criteria\n- Common issues to watch for\n- Best practices and recommendations\n- Success metrics and validation steps";
                }

                // Default enhancement for other prompts
                return "Please provide a comprehensive and detailed response to: " . $prompt . "\n\nStructure your response with clear sections, include relevant examples, and provide actionable insights that can be immediately applied.";

            case 'creativity':
                // Add creative elements
                if (strpos($prompt, 'branding') !== false) {
                    return "Brainstorm 10 wildly creative and innovative branding concepts for an eco-friendly product. Think outside the box and consider:\n\n- Unexpected brand names and personalities\n- Bold visual concepts and color schemes\n- Unique storytelling angles\n- Memorable taglines and slogans\n- Creative marketing approaches\n- Viral potential and shareability\n\nPush boundaries and create concepts that would stand out in a crowded market.";
                }
                return "Approach this creatively: " . $prompt . "\n\nThink outside the box, consider unconventional angles, and provide innovative solutions that break from traditional approaches.";

            case 'structure':
                // Restructure for better organization
                if (strpos($prompt, 'branding') !== false) {
                    return "Please organize your response to this branding request in the following structure:\n\n1. MARKET ANALYSIS\n   - Target audience insights\n   - Competitive landscape\n   - Market opportunities\n\n2. BRAND STRATEGY\n   - Brand positioning\n   - Core values and personality\n   - Unique value proposition\n\n3. CREATIVE CONCEPTS\n   - 10 distinct brand ideas\n   - Visual identity elements\n   - Messaging framework\n\n4. IMPLEMENTATION PLAN\n   - Launch strategy\n   - Marketing channels\n   - Success metrics\n\n" . $prompt;
                }
                return "Please structure your response to: " . $prompt . "\n\nOrganize your answer with clear headings, logical flow, and easy-to-follow sections that build upon each other.";

            default:
                return $prompt;
        }
    }
}
