<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Exception;

class OpenAIService
{
    private string $apiKey;
    private string $model;
    private int $maxTokens;
    private float $temperature;

    public function __construct()
    {
        $this->apiKey = config('services.openai.api_key');
        $this->model = config('services.openai.model');
        $this->maxTokens = config('services.openai.max_tokens');
        $this->temperature = config('services.openai.temperature');
    }

    /**
     * Generate a proposal from intake response data
     */
    public function generateProposal(array $intakeData, array $options = []): array
    {
        $includeDeliverables = $options['include_deliverables'] ?? true;
        $includeTimeline = $options['include_timeline'] ?? true;
        $includePricing = $options['include_pricing'] ?? true;

        $prompt = $this->buildProposalPrompt($intakeData, $includeDeliverables, $includeTimeline, $includePricing);

        $functions = [
            [
                'name' => 'create_proposal',
                'description' => 'Create a comprehensive proposal based on the intake response',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'title' => [
                            'type' => 'string',
                            'description' => 'Professional proposal title'
                        ],
                        'scope' => [
                            'type' => 'string',
                            'description' => 'Detailed project scope and objectives'
                        ],
                        'deliverables' => [
                            'type' => 'array',
                            'items' => ['type' => 'string'],
                            'description' => 'List of specific deliverables'
                        ],
                        'timeline' => [
                            'type' => 'array',
                            'items' => [
                                'type' => 'object',
                                'properties' => [
                                    'id' => ['type' => 'string'],
                                    'description' => ['type' => 'string'],
                                    'duration' => ['type' => 'string'],
                                    'price' => ['type' => 'number']
                                ],
                                'required' => ['id', 'description', 'duration', 'price']
                            ],
                            'description' => 'Project phases with pricing'
                        ],
                        'total_price' => [
                            'type' => 'number',
                            'description' => 'Total project cost'
                        ]
                    ],
                    'required' => ['title', 'scope', 'deliverables', 'timeline', 'total_price']
                ]
            ]
        ];

        return $this->makeFunctionCall($prompt, $functions, 'create_proposal');
    }

    /**
     * Generate a project from proposal data
     */
    public function generateProject(array $proposalData, array $options = []): array
    {
        $includeTimeline = $options['include_timeline'] ?? true;
        $includePhases = $options['include_phases'] ?? true;

        $prompt = $this->buildProjectPrompt($proposalData, $includeTimeline, $includePhases);

        $functions = [
            [
                'name' => 'create_project',
                'description' => 'Create a project structure from proposal data',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'title' => [
                            'type' => 'string',
                            'description' => 'Project title'
                        ],
                        'current_phase' => [
                            'type' => 'string',
                            'description' => 'Current project phase'
                        ],
                        'kickoff_date' => [
                            'type' => 'string',
                            'description' => 'Project kickoff date (YYYY-MM-DD)'
                        ],
                        'expected_delivery' => [
                            'type' => 'string',
                            'description' => 'Expected delivery date (YYYY-MM-DD)'
                        ],
                        'notes' => [
                            'type' => 'string',
                            'description' => 'Project notes and additional information'
                        ]
                    ],
                    'required' => ['title', 'current_phase', 'kickoff_date', 'expected_delivery']
                ]
            ]
        ];

        return $this->makeFunctionCall($prompt, $functions, 'create_project');
    }

    /**
     * Generate a personal AI project
     */
    public function generatePersonalProject(array $projectData): array
    {
        $prompt = $this->buildPersonalProjectPrompt($projectData);

        $functions = [
            [
                'name' => 'create_personal_project',
                'description' => 'Create a personal project structure',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'title' => [
                            'type' => 'string',
                            'description' => 'Project title'
                        ],
                        'current_phase' => [
                            'type' => 'string',
                            'description' => 'Current project phase'
                        ],
                        'kickoff_date' => [
                            'type' => 'string',
                            'description' => 'Project kickoff date (YYYY-MM-DD)'
                        ],
                        'expected_delivery' => [
                            'type' => 'string',
                            'description' => 'Expected delivery date (YYYY-MM-DD)'
                        ],
                        'notes' => [
                            'type' => 'string',
                            'description' => 'Project notes and additional information'
                        ]
                    ],
                    'required' => ['title', 'current_phase', 'kickoff_date', 'expected_delivery']
                ]
            ]
        ];

        return $this->makeFunctionCall($prompt, $functions, 'create_personal_project');
    }

    /**
     * Generate tasks for a project
     */
    public function generateTasks(array $projectData, array $options = []): array
    {
        $maxTasks = $options['max_tasks'] ?? 10;
        $includeSubtasks = $options['include_subtasks'] ?? false;

        $prompt = $this->buildTasksPrompt($projectData, $maxTasks, $includeSubtasks);

        $taskProperties = [
            'title' => ['type' => 'string', 'description' => 'Task title'],
            'description' => ['type' => 'string', 'description' => 'Task description'],
            'priority' => ['type' => 'string', 'enum' => ['low', 'medium', 'high'], 'description' => 'Task priority'],
            'estimated_hours' => ['type' => 'number', 'description' => 'Estimated hours to complete'],
            'tags' => ['type' => 'array', 'items' => ['type' => 'string'], 'description' => 'Task tags']
        ];

        if ($includeSubtasks) {
            $taskProperties['subtasks'] = [
                'type' => 'array',
                'items' => ['type' => 'string'],
                'description' => 'List of subtasks'
            ];
        }

        $functions = [
            [
                'name' => 'create_tasks',
                'description' => 'Create tasks for the project',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'tasks' => [
                            'type' => 'array',
                            'items' => [
                                'type' => 'object',
                                'properties' => $taskProperties,
                                'required' => ['title', 'description', 'priority']
                            ],
                            'description' => 'List of tasks for the project'
                        ]
                    ],
                    'required' => ['tasks']
                ]
            ]
        ];

        return $this->makeFunctionCall($prompt, $functions, 'create_tasks');
    }

    /**
     * Generate personal project tasks
     */
    public function generatePersonalTasks(array $projectData): array
    {
        $prompt = $this->buildPersonalTasksPrompt($projectData);

        $functions = [
            [
                'name' => 'create_personal_tasks',
                'description' => 'Create tasks for a personal project',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'tasks' => [
                            'type' => 'array',
                            'items' => [
                                'type' => 'object',
                                'properties' => [
                                    'title' => ['type' => 'string', 'description' => 'Task title'],
                                    'description' => ['type' => 'string', 'description' => 'Task description'],
                                    'priority' => ['type' => 'string', 'enum' => ['low', 'medium', 'high'], 'description' => 'Task priority'],
                                    'estimated_hours' => ['type' => 'number', 'description' => 'Estimated hours to complete'],
                                    'tags' => ['type' => 'array', 'items' => ['type' => 'string'], 'description' => 'Task tags']
                                ],
                                'required' => ['title', 'description', 'priority']
                            ],
                            'description' => 'List of tasks for the personal project'
                        ]
                    ],
                    'required' => ['tasks']
                ]
            ]
        ];

        return $this->makeFunctionCall($prompt, $functions, 'create_personal_tasks');
    }

    /**
     * Make a function call to OpenAI API
     */
    private function makeFunctionCall(string $prompt, array $functions, string $functionName): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/chat/completions', [
                'model' => $this->model,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a professional project management assistant. Generate high-quality, practical content that can be directly used in production.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'functions' => $functions,
                'function_call' => ['name' => $functionName],
                'max_tokens' => $this->maxTokens,
                'temperature' => $this->temperature,
            ]);

            if (!$response->successful()) {
                Log::error('OpenAI API error', [
                    'status' => $response->status(),
                    'response' => $response->json()
                ]);
                throw new Exception('OpenAI API request failed: ' . $response->status());
            }

            $data = $response->json();
            $functionCall = $data['choices'][0]['message']['function_call'] ?? null;

            if (!$functionCall) {
                throw new Exception('No function call returned from OpenAI');
            }

            $arguments = json_decode($functionCall['arguments'], true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Invalid JSON in function call arguments');
            }

            return $arguments;

        } catch (Exception $e) {
            Log::error('OpenAI service error', [
                'error' => $e->getMessage(),
                'prompt' => $prompt
            ]);
            throw $e;
        }
    }

    /**
     * Build proposal generation prompt
     */
    private function buildProposalPrompt(array $intakeData, bool $includeDeliverables, bool $includeTimeline, bool $includePricing): string
    {
        $prompt = "Create a professional proposal based on the following client intake information:\n\n";
        $prompt .= "Client: {$intakeData['full_name']}\n";
        $prompt .= "Company: {$intakeData['company_name']}\n";
        $prompt .= "Project Description: {$intakeData['project_description']}\n";
        $prompt .= "Budget Range: {$intakeData['budget_range']}\n";
        $prompt .= "Deadline: {$intakeData['deadline']}\n";
        $prompt .= "Project Type: {$intakeData['project_type']}\n";

        if (!empty($intakeData['project_examples'])) {
            $prompt .= "Project Examples: " . implode(', ', $intakeData['project_examples']) . "\n";
        }

        $prompt .= "\nRequirements:\n";
        $prompt .= "- Create a compelling proposal title\n";
        $prompt .= "- Write a detailed project scope\n";

        if ($includeDeliverables) {
            $prompt .= "- Include specific deliverables\n";
        }

        if ($includeTimeline) {
            $prompt .= "- Create a timeline with phases\n";
        }

        if ($includePricing) {
            $prompt .= "- Include pricing for each phase\n";
        }

        $prompt .= "- Ensure the total price fits within the budget range\n";
        $prompt .= "- Make the proposal professional and client-ready\n";

        return $prompt;
    }

    /**
     * Build project generation prompt
     */
    private function buildProjectPrompt(array $proposalData, bool $includeTimeline, bool $includePhases): string
    {
        $prompt = "Create a project structure based on the following proposal:\n\n";
        $prompt .= "Proposal Title: {$proposalData['title']}\n";
        $prompt .= "Scope: {$proposalData['scope']}\n";
        $prompt .= "Deliverables: " . implode(', ', $proposalData['deliverables']) . "\n";
        $prompt .= "Total Price: \${$proposalData['price']}\n";

        if ($includeTimeline && !empty($proposalData['timeline'])) {
            $prompt .= "Timeline:\n";
            foreach ($proposalData['timeline'] as $phase) {
                $prompt .= "- {$phase['description']}: {$phase['duration']} (\${$phase['price']})\n";
            }
        }

        $prompt .= "\nRequirements:\n";
        $prompt .= "- Create a project title\n";
        $prompt .= "- Set appropriate kickoff and delivery dates\n";
        $prompt .= "- Determine current phase\n";
        $prompt .= "- Add relevant project notes\n";

        return $prompt;
    }

    /**
     * Build personal project generation prompt
     */
    private function buildPersonalProjectPrompt(array $projectData): string
    {
        $prompt = "Create a personal project structure based on the following information:\n\n";
        $prompt .= "Project Type: {$projectData['project_type']}\n";
        $prompt .= "Description: {$projectData['description']}\n";
        $prompt .= "Include in Portfolio: " . ($projectData['include_in_portfolio'] ? 'Yes' : 'No') . "\n";

        $prompt .= "\nRequirements:\n";
        $prompt .= "- Create a compelling project title\n";
        $prompt .= "- Set realistic kickoff and delivery dates\n";
        $prompt .= "- Determine appropriate project phase\n";
        $prompt .= "- Add relevant project notes\n";
        $prompt .= "- Make it suitable for portfolio if requested\n";

        return $prompt;
    }

    /**
     * Build tasks generation prompt
     */
    private function buildTasksPrompt(array $projectData, int $maxTasks, bool $includeSubtasks): string
    {
        $prompt = "Create up to {$maxTasks} tasks for the following project:\n\n";
        $prompt .= "Project Title: {$projectData['project_title']}\n";
        $prompt .= "Project Description: {$projectData['project_description']}\n";
        $prompt .= "Project Scope: {$projectData['project_scope']}\n";

        if (!empty($projectData['timeline'])) {
            $prompt .= "Timeline: " . json_encode($projectData['timeline']) . "\n";
        }

        $prompt .= "\nRequirements:\n";
        $prompt .= "- Create practical, actionable tasks\n";
        $prompt .= "- Include appropriate priorities (low, medium, high)\n";
        $prompt .= "- Provide realistic time estimates\n";
        $prompt .= "- Add relevant tags\n";

        if ($includeSubtasks) {
            $prompt .= "- Include subtasks for complex tasks\n";
        }

        return $prompt;
    }

    /**
     * Build personal tasks generation prompt
     */
    private function buildPersonalTasksPrompt(array $projectData): string
    {
        $prompt = "Create tasks for the following personal project:\n\n";
        $prompt .= "Project Type: {$projectData['project_type']}\n";
        $prompt .= "Project Title: {$projectData['project_title']}\n";
        $prompt .= "Description: {$projectData['description']}\n";

        $prompt .= "\nRequirements:\n";
        $prompt .= "- Create practical, actionable tasks\n";
        $prompt .= "- Include appropriate priorities\n";
        $prompt .= "- Provide realistic time estimates\n";
        $prompt .= "- Add relevant tags\n";
        $prompt .= "- Make tasks suitable for personal project management\n";

        return $prompt;
    }

    /**
     * Regenerate a prompt template while preserving variables
     */
    public function regenerateTemplate(string $currentTemplate, string $templateType, string $templateName): string
    {
        // Extract variables from the current template
        $variables = $this->extractVariablesFromTemplate($currentTemplate);

        $prompt = "You are a professional prompt template designer. I need you to regenerate a prompt template while preserving all the variables.\n\n";
        $prompt .= "Current Template Type: {$templateType}\n";
        $prompt .= "Template Name: {$templateName}\n";
        $prompt .= "Current Template:\n{$currentTemplate}\n\n";

        if (!empty($variables)) {
            $prompt .= "Variables to preserve: " . implode(', ', $variables) . "\n\n";
        }

        $prompt .= "Requirements:\n";
        $prompt .= "- Keep all existing variables exactly as they are (e.g., {full_name}, {company_name})\n";
        $prompt .= "- Improve the template structure and wording\n";
        $prompt .= "- Make it more professional and effective\n";
        $prompt .= "- Maintain the same general purpose and flow\n";
        $prompt .= "- Return only the template text, no explanations\n";

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/chat/completions', [
                'model' => $this->model,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a professional prompt template designer. Generate improved templates while preserving all variables exactly as they appear.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'max_tokens' => $this->maxTokens,
                'temperature' => 0.7,
            ]);

            if (!$response->successful()) {
                Log::error('OpenAI API error during template regeneration', [
                    'status' => $response->status(),
                    'response' => $response->json()
                ]);
                throw new Exception('OpenAI API request failed: ' . $response->status());
            }

            $data = $response->json();
            $generatedTemplate = $data['choices'][0]['message']['content'] ?? '';

            if (empty($generatedTemplate)) {
                throw new Exception('No content returned from OpenAI');
            }

            return trim($generatedTemplate);

        } catch (Exception $e) {
            Log::error('OpenAI service error during template regeneration', [
                'error' => $e->getMessage(),
                'template' => $currentTemplate
            ]);
            throw $e;
        }
    }

    /**
     * Extract variables from a template string
     */
    private function extractVariablesFromTemplate(string $template): array
    {
        $variables = [];
        preg_match_all('/{(\w+)}/', $template, $matches);

        if (!empty($matches[1])) {
            $variables = array_unique($matches[1]);
        }

        return $variables;
    }

    /**
     * Generate a simple chat completion for testing
     */
    public function generateChatCompletion(string $message, array $options = []): array
    {
        try {
            $model = $options['model'] ?? $this->model;
            $maxTokens = $options['max_tokens'] ?? $this->maxTokens;
            $temperature = $options['temperature'] ?? $this->temperature;

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/chat/completions', [
                'model' => $model,
                'messages' => [
                    ['role' => 'user', 'content' => $message]
                ],
                'max_tokens' => $maxTokens,
                'temperature' => $temperature,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'status' => 'success',
                    'message' => 'Chat completion generated successfully',
                    'data' => $data,
                    'content' => $data['choices'][0]['message']['content'] ?? null
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Failed to generate chat completion: ' . $response->body(),
                    'data' => null
                ];
            }
        } catch (Exception $e) {
            Log::error('OpenAI chat completion failed', [
                'error' => $e->getMessage(),
                'message' => $message
            ]);

            return [
                'status' => 'error',
                'message' => 'Failed to generate chat completion: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }
}
