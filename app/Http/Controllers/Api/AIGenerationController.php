<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\IntakeResponse;
use App\Models\Proposal;
use App\Models\Project;
use App\Models\Task;
use App\Models\Subtask;
use App\Services\OpenAIService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AIGenerationController extends Controller
{
    private OpenAIService $openAIService;

    public function __construct(OpenAIService $openAIService)
    {
        $this->openAIService = $openAIService;
    }

    /**
     * Generate proposal from intake response
     */
    public function generateProposal(Request $request, $intakeResponseId): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'include_deliverables' => 'boolean',
                'include_timeline' => 'boolean',
                'include_pricing' => 'boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $intakeResponse = IntakeResponse::findOrFail($intakeResponseId);

            // Check if proposal already exists
            if ($intakeResponse->proposal) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Proposal already exists for this intake response'
                ], 409);
            }

            $options = [
                'include_deliverables' => $request->get('include_deliverables', true),
                'include_timeline' => $request->get('include_timeline', true),
                'include_pricing' => $request->get('include_pricing', true),
            ];

            $aiResponse = $this->openAIService->generateProposal($intakeResponse->toArray(), $options);

            // Create the proposal
            $proposal = DB::transaction(function () use ($aiResponse, $intakeResponse) {
                $proposal = Proposal::create([
                    'intake_response_id' => $intakeResponse->id,
                    'user_id' => auth()->id(),
                    'title' => $aiResponse['title'],
                    'scope' => $aiResponse['scope'],
                    'deliverables' => $aiResponse['deliverables'],
                    'timeline' => $aiResponse['timeline'],
                    'price' => $aiResponse['total_price'],
                    'status' => 'draft'
                ]);

                Log::info('AI-generated proposal created', [
                    'proposal_id' => $proposal->id,
                    'intake_response_id' => $intakeResponse->id,
                    'user_id' => auth()->id()
                ]);

                return $proposal;
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Proposal generated successfully',
                'data' => $proposal->load('intakeResponse')
            ], 201);

        } catch (\Exception $e) {
            Log::error('Failed to generate proposal', [
                'intake_response_id' => $intakeResponseId,
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to generate proposal: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate project from proposal
     */
    public function generateProject(Request $request, $proposalId): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'proposal_data' => 'array',
                'include_timeline' => 'boolean',
                'include_phases' => 'boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $proposal = Proposal::findOrFail($proposalId);

            // Check if project already exists
            if ($proposal->project) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Project already exists for this proposal'
                ], 409);
            }

            $options = [
                'include_timeline' => $request->get('include_timeline', true),
                'include_phases' => $request->get('include_phases', true),
            ];

            $proposalData = $request->get('proposal_data', $proposal->toArray());
            $aiResponse = $this->openAIService->generateProject($proposalData, $options);

            // Create the project
            $project = DB::transaction(function () use ($aiResponse, $proposal) {
                $project = Project::create([
                    'proposal_id' => $proposal->id,
                    'title' => $aiResponse['title'],
                    'status' => 'not_started',
                    'current_phase' => $aiResponse['current_phase'],
                    'kickoff_date' => $aiResponse['kickoff_date'],
                    'expected_delivery' => $aiResponse['expected_delivery'],
                    'notes' => $aiResponse['notes'] ?? null,
                ]);

                Log::info('AI-generated project created', [
                    'project_id' => $project->id,
                    'proposal_id' => $proposal->id,
                    'user_id' => auth()->id()
                ]);

                return $project;
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Project generated successfully',
                'data' => $project->load('proposal')
            ], 201);

        } catch (\Exception $e) {
            Log::error('Failed to generate project', [
                'proposal_id' => $proposalId,
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to generate project: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate personal AI project
     */
    public function generatePersonalProject(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'project_type' => 'required|string|max:255',
                'description' => 'required|string',
                'include_in_portfolio' => 'boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $projectData = [
                'project_type' => $request->project_type,
                'description' => $request->description,
                'include_in_portfolio' => $request->get('include_in_portfolio', false),
            ];

            $aiResponse = $this->openAIService->generatePersonalProject($projectData);

            // Create the project
            $project = DB::transaction(function () use ($aiResponse) {
                $project = Project::create([
                    'proposal_id' => null, // Personal project, no proposal
                    'title' => $aiResponse['title'],
                    'status' => 'not_started',
                    'current_phase' => $aiResponse['current_phase'],
                    'kickoff_date' => $aiResponse['kickoff_date'],
                    'expected_delivery' => $aiResponse['expected_delivery'],
                    'notes' => $aiResponse['notes'] ?? null,
                ]);

                Log::info('AI-generated personal project created', [
                    'project_id' => $project->id,
                    'user_id' => auth()->id()
                ]);

                return $project;
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Personal project generated successfully',
                'data' => $project
            ], 201);

        } catch (\Exception $e) {
            Log::error('Failed to generate personal project', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to generate personal project: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate tasks for a project
     */
    public function generateTasks(Request $request, $projectId): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'project_description' => 'required|string',
                'project_scope' => 'required|string',
                'timeline' => 'array',
                'max_tasks' => 'integer|min:1|max:20',
                'include_subtasks' => 'boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $project = Project::findOrFail($projectId);

            $projectData = [
                'project_title' => $project->title,
                'project_description' => $request->project_description,
                'project_scope' => $request->project_scope,
                'timeline' => $request->get('timeline', []),
            ];

            $options = [
                'max_tasks' => $request->get('max_tasks', 10),
                'include_subtasks' => $request->get('include_subtasks', false),
            ];

            $aiResponse = $this->openAIService->generateTasks($projectData, $options);

            // Create tasks and subtasks
            $createdTasks = DB::transaction(function () use ($aiResponse, $project) {
                $tasks = [];

                foreach ($aiResponse['tasks'] as $taskData) {
                    $task = Task::create([
                        'project_id' => $project->id,
                        'title' => $taskData['title'],
                        'description' => $taskData['description'],
                        'status' => 'todo',
                        'priority' => $taskData['priority'] ?? 'medium',
                        'estimated_hours' => $taskData['estimated_hours'] ?? null,
                        'tags' => $taskData['tags'] ?? [],
                    ]);

                    // Create subtasks if they exist
                    if (!empty($taskData['subtasks'])) {
                        foreach ($taskData['subtasks'] as $subtaskDescription) {
                            Subtask::create([
                                'task_id' => $task->id,
                                'description' => $subtaskDescription,
                                'status' => 'todo'
                            ]);
                        }
                    }

                    $tasks[] = $task->load('subtasks');
                }

                Log::info('AI-generated tasks created', [
                    'project_id' => $project->id,
                    'task_count' => count($tasks),
                    'user_id' => auth()->id()
                ]);

                return $tasks;
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Tasks generated successfully',
                'data' => $createdTasks
            ], 201);

        } catch (\Exception $e) {
            Log::error('Failed to generate tasks', [
                'project_id' => $projectId,
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to generate tasks: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate personal project tasks
     */
    public function generatePersonalTasks(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'project_type' => 'required|string|max:255',
                'project_title' => 'required|string|max:255',
                'description' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $projectData = [
                'project_type' => $request->project_type,
                'project_title' => $request->project_title,
                'description' => $request->description,
            ];

            $aiResponse = $this->openAIService->generatePersonalTasks($projectData);

            return response()->json([
                'status' => 'success',
                'message' => 'Personal tasks generated successfully',
                'data' => $aiResponse
            ], 200);

        } catch (\Exception $e) {
            Log::error('Failed to generate personal tasks', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to generate personal tasks: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate subtasks for a specific task
     */
    public function generateSubtasks(Request $request, $taskId): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'max_subtasks' => 'integer|min:1|max:10',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $task = Task::with(['project', 'project.client'])->findOrFail($taskId);

            // Build context for AI generation
            $context = [
                'task_title' => $task->title,
                'task_description' => $task->description,
                'task_priority' => $task->priority,
                'task_status' => $task->status,
                'project_title' => $task->project->title ?? 'No Project',
                'project_description' => $task->project->description ?? '',
                'client_name' => $task->project->client ?
                    $task->project->client->first_name . ' ' . $task->project->client->last_name : 'No Client',
            ];

            $maxSubtasks = $request->get('max_subtasks', 5);

            // Generate subtasks using AI
            $prompt = $this->buildSubtaskPrompt($context, $maxSubtasks);
            $aiResponse = $this->openAIService->generateChatCompletionWithParams(
                $prompt,
                config('services.openai.model'),
                0.7,
                2000
            );

            if (!isset($aiResponse['content'])) {
                throw new \Exception('Failed to generate subtasks');
            }

            // Parse the AI response to extract subtasks
            $subtasks = $this->parseSubtasksFromResponse($aiResponse['content']);

            // Create subtasks in database
            $createdSubtasks = DB::transaction(function () use ($subtasks, $task) {
                $createdSubtasks = [];

                foreach ($subtasks as $subtaskText) {
                    $subtask = Subtask::create([
                        'task_id' => $task->id,
                        'description' => $subtaskText,
                        'status' => 'todo'
                    ]);

                    $createdSubtasks[] = $subtask;
                }

                Log::info('AI-generated subtasks created', [
                    'task_id' => $task->id,
                    'subtask_count' => count($createdSubtasks),
                    'user_id' => auth()->id()
                ]);

                return $createdSubtasks;
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Subtasks generated successfully',
                'data' => $createdSubtasks
            ], 201);

        } catch (\Exception $e) {
            Log::error('Failed to generate subtasks', [
                'task_id' => $taskId,
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to generate subtasks: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Build prompt for subtask generation
     */
    private function buildSubtaskPrompt(array $context, int $maxSubtasks): string
    {
        return "You are a professional project management assistant. Based on the following task and project information, generate {$maxSubtasks} specific, actionable subtasks that will help complete this task.

Task Information:
- Title: {$context['task_title']}
- Description: {$context['task_description']}
- Priority: {$context['task_priority']}
- Status: {$context['task_status']}

Project Information:
- Project: {$context['project_title']}
- Project Description: {$context['project_description']}
- Client: {$context['client_name']}

Please generate exactly {$maxSubtasks} subtasks that are:
1. Specific and actionable
2. Logical steps to complete the main task
3. Clear and concise (1-2 sentences each)
4. Appropriate for the task priority and complexity

Format your response as a numbered list, with each subtask on a new line starting with a number and period (e.g., '1. First subtask').";
    }

    /**
     * Parse subtasks from AI response
     */
    private function parseSubtasksFromResponse(string $response): array
    {
        $lines = explode("\n", trim($response));
        $subtasks = [];

        foreach ($lines as $line) {
            $line = trim($line);

            // Match numbered lines (1. 2. 3. etc.)
            if (preg_match('/^\d+\.\s*(.+)$/', $line, $matches)) {
                $subtasks[] = trim($matches[1]);
            }
            // Also match lines that start with - or *
            elseif (preg_match('/^[-*]\s*(.+)$/', $line, $matches)) {
                $subtasks[] = trim($matches[1]);
            }
        }

        return array_filter($subtasks); // Remove empty entries
    }
}
