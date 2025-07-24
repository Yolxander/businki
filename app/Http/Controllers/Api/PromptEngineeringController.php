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
     * Make a prompt reusable by converting it to a template
     */
    public function makeReusable(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'prompt_content' => 'required|string|max:10000',
                'model_id' => 'required|exists:ai_models,id',
                'prompt_title' => 'nullable|string|max:255',
                'prompt_description' => 'nullable|string|max:1000',
                'prompt_context' => 'nullable|string|max:255',
                'prompt_tags' => 'nullable|array'
            ]);

            // Convert prompt to reusable template using AI
            $reusableTemplate = $this->makePromptReusable(
                $request->prompt_content,
                $request->only(['prompt_title', 'prompt_description', 'prompt_context', 'prompt_tags'])
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Prompt converted to reusable template successfully',
                'data' => [
                    'original' => $request->prompt_content,
                    'reusable_template' => $reusableTemplate,
                    'placeholders' => $this->extractPlaceholders($reusableTemplate),
                    'usage_instructions' => $this->generateUsageInstructions($reusableTemplate),
                    'created_at' => now()
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to make prompt reusable', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to make prompt reusable: ' . $e->getMessage()
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

    /**
     * Convert a prompt to a reusable template with placeholders
     */
    private function makePromptReusable(string $prompt, array $metadata = []): string
    {
        // This would integrate with OpenAI or other AI service
        // For now, return a smart template conversion

        $lowerPrompt = strtolower($prompt);

        // Branding prompts
        if (strpos($lowerPrompt, 'branding') !== false && strpos($lowerPrompt, 'eco-friendly') !== false) {
            return "Generate {number} unique {target_type} ideas for a new {product_type} product.";
        }

        // Email prompts
        if (strpos($lowerPrompt, 'email') !== false || strpos($lowerPrompt, 'follow') !== false) {
            return "Draft a {email_type} email to {recipient} about {subject}.";
        }

        // Checklist/Audit prompts
        if (strpos($lowerPrompt, 'checklist') !== false || strpos($lowerPrompt, 'audit') !== false) {
            return "Create a comprehensive {audit_type} checklist for {target_area}.";
        }

        // Content generation prompts
        if (strpos($lowerPrompt, 'generate') !== false || strpos($lowerPrompt, 'create') !== false) {
            if (strpos($lowerPrompt, '10') !== false) {
                return "Generate {number} {content_type} for {target_subject}.";
            }
            return "Generate {content_type} for {target_subject}.";
        }

        // Project planning prompts
        if (strpos($lowerPrompt, 'project') !== false || strpos($lowerPrompt, 'plan') !== false) {
            return "Create a {project_type} plan for {project_name}.";
        }

        // Task generation prompts
        if (strpos($lowerPrompt, 'task') !== false) {
            return "Generate {number} {task_type} tasks for {project_context}.";
        }

        // Default template conversion
        return $this->createGenericTemplate($prompt);
    }

    /**
     * Create a generic template from any prompt
     */
    private function createGenericTemplate(string $prompt): string
    {
        // Replace specific nouns with placeholders
        $template = $prompt;

        // Replace numbers with {number}
        $template = preg_replace('/\b\d+\b/', '{number}', $template);

        // Replace specific product types
        $template = preg_replace('/\b(eco-friendly|sustainable|digital|physical|online|offline)\s+product\b/i', '{product_type} product', $template);

        // Replace specific content types
        $template = preg_replace('/\b(branding|marketing|content|design|strategy|plan)\s+ideas?\b/i', '{content_type}', $template);

        // Replace specific subjects
        $template = preg_replace('/\b(branding|marketing|design|development|content|strategy)\b/i', '{target_type}', $template);

        // Replace specific contexts
        $template = preg_replace('/\b(new|existing|current|future)\b/i', '{context}', $template);

        return $template;
    }

    /**
     * Extract placeholders from a template
     */
    private function extractPlaceholders(string $template): array
    {
        preg_match_all('/\{([^}]+)\}/', $template, $matches);
        $placeholders = array_unique($matches[1] ?? []);

        $placeholderInfo = [];
        foreach ($placeholders as $placeholder) {
            $placeholderInfo[] = [
                'name' => $placeholder,
                'description' => $this->getPlaceholderDescription($placeholder),
                'example' => $this->getPlaceholderExample($placeholder)
            ];
        }

        return $placeholderInfo;
    }

    /**
     * Get description for a placeholder
     */
    private function getPlaceholderDescription(string $placeholder): string
    {
        $descriptions = [
            'number' => 'The quantity or amount needed',
            'target_type' => 'The type of content or service',
            'product_type' => 'The category of product',
            'content_type' => 'The type of content to generate',
            'target_subject' => 'The subject or topic to focus on',
            'email_type' => 'The type of email (follow-up, introduction, etc.)',
            'recipient' => 'The person or group to send the email to',
            'subject' => 'The main topic or purpose of the email',
            'audit_type' => 'The type of audit or review',
            'target_area' => 'The area or component to audit',
            'project_type' => 'The type of project',
            'project_name' => 'The name or title of the project',
            'task_type' => 'The type of tasks to generate',
            'project_context' => 'The context or scope of the project',
            'context' => 'The context or timeframe'
        ];

        return $descriptions[$placeholder] ?? 'A variable that can be customized';
    }

    /**
     * Get example for a placeholder
     */
    private function getPlaceholderExample(string $placeholder): string
    {
        $examples = [
            'number' => '5, 10, 20',
            'target_type' => 'branding, marketing, design',
            'product_type' => 'eco-friendly, digital, physical',
            'content_type' => 'ideas, strategies, plans',
            'target_subject' => 'website redesign, product launch',
            'email_type' => 'follow-up, introduction, proposal',
            'recipient' => 'client, team, stakeholder',
            'subject' => 'project update, meeting request',
            'audit_type' => 'UX, SEO, content',
            'target_area' => 'website, mobile app, marketing',
            'project_type' => 'web development, marketing campaign',
            'project_name' => 'Acme Website Redesign',
            'task_type' => 'development, design, content',
            'project_context' => 'website redesign, product launch',
            'context' => 'new, existing, current'
        ];

        return $examples[$placeholder] ?? 'custom value';
    }

    /**
     * Generate usage instructions for a template
     */
    private function generateUsageInstructions(string $template): string
    {
        $placeholders = $this->extractPlaceholders($template);

        if (empty($placeholders)) {
            return "This template is ready to use as-is.";
        }

        $instructions = "To use this template, replace the following placeholders:\n\n";

        foreach ($placeholders as $placeholder) {
            $instructions .= "• {" . $placeholder['name'] . "}: " . $placeholder['description'] . " (e.g., " . $placeholder['example'] . ")\n";
        }

        $instructions .= "\nExample usage:\n";
        $instructions .= "Original: " . $template . "\n";
        $instructions .= "Filled: " . $this->createExampleUsage($template, $placeholders);

        return $instructions;
    }

    /**
     * Create an example usage of the template
     */
    private function createExampleUsage(string $template, array $placeholders): string
    {
        $example = $template;

        foreach ($placeholders as $placeholder) {
            $example = str_replace(
                '{' . $placeholder['name'] . '}',
                $placeholder['example'],
                $example
            );
        }

        return $example;
    }
}
