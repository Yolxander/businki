<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Models\Subtask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class TaskController extends Controller
{
    /**
     * Show the BobbiFlow kanban board
     */
    public function bobbiFlow()
    {
        Log::info('Loading BobbiFlow page', ['user_id' => auth()->id()]);

        try {
            // Fetch tasks for the current user
            $tasks = Task::with(['project.client', 'assignedUser', 'subtasks'])
                ->where('user_id', auth()->id())
                ->orderBy('created_at', 'desc')
                ->get();

            Log::info('BobbiFlow page loaded successfully', [
                'user_id' => auth()->id(),
                'tasks_count' => $tasks->count(),
                'tasks' => $tasks->toArray()
            ]);

            return Inertia::render('BobbiFlow', [
                'auth' => ['user' => auth()->user()],
                'tasks' => $tasks
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to load BobbiFlow page', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return Inertia::render('BobbiFlow', [
                'auth' => ['user' => auth()->user()],
                'tasks' => collect([]),
                'error' => 'Failed to load tasks'
            ]);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        Log::info('Loading task creation form', ['user_id' => auth()->id()]);

        try {
            // Fetch projects for the current user
            $projects = Project::where('user_id', auth()->id())
                ->orderBy('created_at', 'desc')
                ->get(['id', 'name', 'description']);

            Log::info('Task creation form loaded successfully', [
                'user_id' => auth()->id(),
                'projects_count' => $projects->count(),
                'projects' => $projects->toArray()
            ]);

            return Inertia::render('CreateTask', [
                'auth' => ['user' => auth()->user()],
                'projects' => $projects
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to load task creation form', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return Inertia::render('CreateTask', [
                'auth' => ['user' => auth()->user()],
                'projects' => collect([]),
                'error' => 'Failed to load projects'
            ]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Task $task)
    {
        Log::info('Loading task edit form', ['task_id' => $task->id, 'user_id' => auth()->id()]);

        try {
            // Load task with relationships
            $task->load(['project.client', 'assignedUser', 'subtasks', 'user']);

            // Fetch projects for the current user
            $projects = Project::where('user_id', auth()->id())
                ->orderBy('created_at', 'desc')
                ->get(['id', 'name', 'description']);

            Log::info('Task edit form loaded successfully', [
                'task_id' => $task->id,
                'user_id' => auth()->id(),
                'projects_count' => $projects->count()
            ]);

            return Inertia::render('EditTask', [
                'auth' => ['user' => auth()->user()],
                'task' => $task,
                'projects' => $projects
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to load task edit form', [
                'task_id' => $task->id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return Inertia::render('EditTask', [
                'auth' => ['user' => auth()->user()],
                'task' => null,
                'projects' => collect([]),
                'error' => 'Failed to load task data'
            ]);
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Log::info('Fetching all tasks with relationships');
        $tasks = Task::with(['project', 'assignedUser'])->get();
        Log::info('Successfully retrieved tasks', ['count' => $tasks->count()]);
        return response()->json($tasks);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Log::info('Creating new task', ['request_data' => $request->all()]);

        $validator = Validator::make($request->all(), [
            'project_id' => 'required|exists:projects,id',
            'phase_id' => 'nullable|integer',
            'title' => 'required|string|max:255',
            'status' => 'required|in:todo,in_progress,done,inbox,in-progress,waiting,review',
            'due_date' => 'nullable|date',
            'assigned_to' => 'nullable|integer',
            'description' => 'nullable|string',
            'priority' => 'nullable|in:low,medium,high',
            'tags' => 'nullable|array',
            'estimated_hours' => 'nullable|numeric|min:0|max:999.99',
            'subtasks' => 'nullable|array',
            'subtasks.*' => 'string|max:255'
        ]);

        if ($validator->fails()) {
            Log::warning('Task creation validation failed', ['errors' => $validator->errors()->toArray()]);

            if (request()->header('X-Inertia')) {
                return back()->withErrors($validator->errors());
            }

            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Validate phase_id exists in project's proposal timeline if provided
        if ($request->has('phase_id')) {
            Log::info('Validating phase_id against project proposal timeline', ['phase_id' => $request->phase_id]);
            $project = Project::findOrFail($request->project_id);
            if (!$project->proposal || !collect($project->proposal->timeline)->contains('id', $request->phase_id)) {
                Log::warning('Invalid phase_id for project', [
                    'phase_id' => $request->phase_id,
                    'project_id' => $request->project_id
                ]);
                return response()->json(['errors' => ['phase_id' => ['The selected phase does not exist in the project\'s proposal timeline.']]], 422);
            }
        }

        try {
            // Create the task first
            $taskData = $request->except(['subtasks']);

            // Handle empty estimated_hours
            if (empty($taskData['estimated_hours'])) {
                $taskData['estimated_hours'] = null;
            }

            // Handle empty assigned_to
            if (empty($taskData['assigned_to'])) {
                $taskData['assigned_to'] = null;
            }

            // Map frontend status values to database values
            $statusMap = [
                'inbox' => 'todo',
                'in-progress' => 'in_progress',
                'waiting' => 'todo',
                'review' => 'in_progress',
                'done' => 'done'
            ];

            if (isset($taskData['status']) && isset($statusMap[$taskData['status']])) {
                $taskData['status'] = $statusMap[$taskData['status']];
            }

            // Add user_id to the task
            $taskData['user_id'] = auth()->id();

            $task = Task::create($taskData);
            Log::info('Task created successfully', ['task_id' => $task->id]);

            // Create subtasks if they exist in the request
            if ($request->has('subtasks') && is_array($request->subtasks)) {
                $subtasks = [];
                foreach ($request->subtasks as $subtaskDescription) {
                    if (!empty($subtaskDescription)) {
                        $subtask = Subtask::create([
                            'task_id' => $task->id,
                            'description' => $subtaskDescription,
                            'status' => 'todo'
                        ]);
                        $subtasks[] = $subtask;
                    }
                }
                Log::info('Subtasks created successfully', [
                    'task_id' => $task->id,
                    'subtask_count' => count($subtasks)
                ]);
            }

            // Handle Inertia.js requests
            if (request()->header('X-Inertia')) {
                return redirect()->route('tasks.show', $task->id)
                    ->with('success', 'Task created successfully!');
            }

            // Return the task with its subtasks for API requests
            return response()->json($task->load(['subtasks', 'project', 'assignedUser']), 201);
        } catch (\Exception $e) {
            Log::error('Failed to create task', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if (request()->header('X-Inertia')) {
                return back()->withErrors(['error' => 'Failed to create task']);
            }

            return response()->json(['error' => 'Failed to create task'], 500);
        }
    }

    /**
     * Show the start work page for the specified task.
     */
    public function startWork(Task $task)
    {
        Log::info('Loading start work page', ['task_id' => $task->id, 'user_id' => auth()->id()]);

        try {
            // Load task with relationships
            $task->load(['project.client', 'assignedUser', 'subtasks', 'user']);

            Log::info('Start work page loaded successfully', [
                'task_id' => $task->id,
                'user_id' => auth()->id()
            ]);

            return Inertia::render('StartWork', [
                'auth' => ['user' => auth()->user()],
                'task' => $task
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to load start work page', [
                'task_id' => $task->id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return Inertia::render('StartWork', [
                'auth' => ['user' => auth()->user()],
                'task' => null,
                'error' => 'Failed to load task data'
            ]);
        }
    }

    /**
     * Add a subtask to the specified task.
     */
    public function addSubtask(Request $request, Task $task)
    {
        Log::info('Adding subtask to task', ['task_id' => $task->id, 'user_id' => auth()->id()]);

        try {
            $validator = Validator::make($request->all(), [
                'description' => 'required|string|max:500',
            ]);

            if ($validator->fails()) {
                Log::warning('Subtask creation validation failed', ['errors' => $validator->errors()]);
                if (request()->header('X-Inertia')) {
                    return back()->withErrors($validator->errors());
                }
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $subtask = Subtask::create([
                'task_id' => $task->id,
                'description' => $request->description,
                'status' => 'todo'
            ]);

            Log::info('Subtask created successfully', [
                'task_id' => $task->id,
                'subtask_id' => $subtask->id,
                'user_id' => auth()->id()
            ]);

            if (request()->header('X-Inertia')) {
                return redirect()->route('tasks.show', $task->id)
                    ->with('success', 'Subtask added successfully!');
            }

            return response()->json([
                'subtask' => $subtask,
                'message' => 'Subtask added successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to add subtask', [
                'task_id' => $task->id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if (request()->header('X-Inertia')) {
                return back()->withErrors(['error' => 'Failed to add subtask']);
            }

            return response()->json(['error' => 'Failed to add subtask'], 500);
        }
    }

    /**
     * Update a subtask status.
     */
    public function updateSubtask(Request $request, Task $task, Subtask $subtask)
    {
        Log::info('Updating subtask', [
            'task_id' => $task->id,
            'subtask_id' => $subtask->id,
            'user_id' => auth()->id()
        ]);

        try {
            $validator = Validator::make($request->all(), [
                'status' => 'required|in:todo,done',
            ]);

            if ($validator->fails()) {
                Log::warning('Subtask update validation failed', ['errors' => $validator->errors()]);
                if (request()->header('X-Inertia')) {
                    return back()->withErrors($validator->errors());
                }
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $subtask->update([
                'status' => $request->status
            ]);

            Log::info('Subtask updated successfully', [
                'task_id' => $task->id,
                'subtask_id' => $subtask->id,
                'status' => $request->status,
                'user_id' => auth()->id()
            ]);

            if (request()->header('X-Inertia')) {
                return redirect()->route('tasks.show', $task->id)
                    ->with('success', 'Subtask updated successfully!');
            }

            return response()->json([
                'subtask' => $subtask,
                'message' => 'Subtask updated successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to update subtask', [
                'task_id' => $task->id,
                'subtask_id' => $subtask->id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if (request()->header('X-Inertia')) {
                return back()->withErrors(['error' => 'Failed to update subtask']);
            }

            return response()->json(['error' => 'Failed to update subtask'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task)
    {
        Log::info('Fetching task details', ['task_id' => $task->id]);

        try {
            // Load the task with all necessary relationships
            $task->load(['project.client', 'assignedUser', 'subtasks', 'user']);

            return Inertia::render('TaskDetails', [
                'auth' => ['user' => auth()->user()],
                'task' => $task
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to load task details', [
                'task_id' => $task->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return Inertia::render('TaskDetails', [
                'auth' => ['user' => auth()->user()],
                'task' => null,
                'error' => 'Failed to load task details'
            ]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Task $task)
    {
        Log::info('Updating task', [
            'task_id' => $task->id,
            'request_data' => $request->all()
        ]);

        $validator = Validator::make($request->all(), [
            'project_id' => 'sometimes|required|exists:projects,id',
            'phase_id' => 'nullable|integer',
            'title' => 'sometimes|required|string|max:255',
            'status' => 'sometimes|required|in:todo,in_progress,done',
            'due_date' => 'nullable|date',
            'assigned_to' => 'nullable|integer',
            'description' => 'nullable|string',
            'priority' => 'nullable|in:low,medium,high',
            'tags' => 'nullable|array',
            'estimated_hours' => 'nullable|numeric|min:0|max:999.99'
        ]);

        if ($validator->fails()) {
            Log::warning('Task update validation failed', [
                'task_id' => $task->id,
                'errors' => $validator->errors()->toArray()
            ]);
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Validate phase_id exists in project's proposal timeline if provided
        if ($request->has('phase_id')) {
            Log::info('Validating phase_id for update', ['phase_id' => $request->phase_id]);
            $project = Project::findOrFail($request->project_id ?? $task->project_id);
            if (!$project->proposal || !collect($project->proposal->timeline)->contains('id', $request->phase_id)) {
                Log::warning('Invalid phase_id for project update', [
                    'phase_id' => $request->phase_id,
                    'project_id' => $project->id
                ]);
                return response()->json(['errors' => ['phase_id' => ['The selected phase does not exist in the project\'s proposal timeline.']]], 422);
            }
        }

        try {
            $task->update($request->all());
            Log::info('Task updated successfully', ['task_id' => $task->id]);

            // Handle Inertia.js requests
            if (request()->header('X-Inertia')) {
                return redirect()->route('tasks.show', $task->id)
                    ->with('success', 'Task updated successfully!');
            }

            return response()->json($task->load(['project', 'assignedUser']));
        } catch (\Exception $e) {
            Log::error('Failed to update task', [
                'task_id' => $task->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if (request()->header('X-Inertia')) {
                return back()->withErrors(['error' => 'Failed to update task']);
            }

            return response()->json(['error' => 'Failed to update task'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task)
    {
        Log::info('Deleting task', ['task_id' => $task->id]);

        // Store task name for success message
        $taskName = $task->title;

        try {
            $task->delete();
            Log::info('Task deleted successfully', ['task_id' => $task->id]);

            // Handle Inertia.js requests
            if (request()->header('X-Inertia')) {
                return Inertia::location(route('bobbi-flow'));
            }

            return response()->json(null, 204);
        } catch (\Exception $e) {
            Log::error('Failed to delete task', [
                'task_id' => $task->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if (request()->header('X-Inertia')) {
                return back()->withErrors(['error' => 'Failed to delete task']);
            }

            return response()->json(['error' => 'Failed to delete task'], 500);
        }
    }

    public function getByProject($projectId)
    {
        Log::info('Fetching tasks by project', ['project_id' => $projectId]);
        try {
        $tasks = Task::with(['assignedUser'])
            ->where('project_id', $projectId)
            ->get();
            Log::info('Successfully retrieved tasks for project', [
                'project_id' => $projectId,
                'task_count' => $tasks->count()
            ]);
        return response()->json($tasks);
        } catch (\Exception $e) {
            Log::error('Failed to fetch tasks by project', [
                'project_id' => $projectId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Failed to fetch tasks'], 500);
        }
    }

    public function getByPhase($phaseId)
    {
        Log::info('Fetching tasks by phase', ['phase_id' => $phaseId]);
        try {
        $tasks = Task::with(['project', 'assignedUser'])
            ->where('phase_id', $phaseId)
            ->get();
            Log::info('Successfully retrieved tasks for phase', [
                'phase_id' => $phaseId,
                'task_count' => $tasks->count()
            ]);
        return response()->json($tasks);
        } catch (\Exception $e) {
            Log::error('Failed to fetch tasks by phase', [
                'phase_id' => $phaseId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Failed to fetch tasks'], 500);
        }
    }

    /**
     * Get the proposal timeline for a project
     */
    public function getProjectTimeline($projectId)
    {
        Log::info('Fetching project timeline', ['project_id' => $projectId]);
        try {
        $project = Project::findOrFail($projectId);

        if (!$project->proposal) {
                Log::warning('No proposal found for project', ['project_id' => $projectId]);
            return response()->json(['message' => 'No proposal found for this project'], 404);
        }

            $tasks = Task::where('project_id', $projectId)
                ->whereNotNull('phase_id')
                ->get()
                ->map(function ($task) {
                    return [
                        'task_id' => $task->id,
                        'title' => $task->title,
                        'phase_id' => $task->phase_id,
                        'status' => $task->status
                    ];
                });

            Log::info('Successfully retrieved project timeline', [
                'project_id' => $projectId,
                'task_count' => $tasks->count()
            ]);

            return response()->json([
                'timeline' => $project->proposal->timeline,
                'tasks' => $tasks
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch project timeline', [
                'project_id' => $projectId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Failed to fetch project timeline'], 500);
        }
    }

    /**
     * Connect a task to a specific phase in the proposal timeline
     */
    public function connectToTimeline(Request $request, Task $task)
    {
        Log::info('Connect to Timeline Request Started', [
            'task_id' => $task->id,
            'request_data' => $request->all(),
            'current_task' => $task->toArray()
        ]);

        $validator = Validator::make($request->all(), [
            'phase_id' => 'required|integer'
        ]);

        if ($validator->fails()) {
            Log::warning('Connect to Timeline Validation Failed', [
                'errors' => $validator->errors()->toArray(),
                'request_data' => $request->all()
            ]);
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $project = Project::findOrFail($task->project_id);
            Log::info('Project Found', [
                'project_id' => $project->id,
                'has_proposal' => !is_null($project->proposal),
                'proposal_data' => $project->proposal ? $project->proposal->toArray() : null
            ]);

            if (!$project->proposal) {
                Log::warning('No Proposal Found for Project', [
                    'project_id' => $project->id
                ]);
                return response()->json(['message' => 'No proposal found for this project'], 404);
            }

            Log::info('Checking Timeline', [
                'proposal_timeline' => $project->proposal->timeline,
                'requested_phase_id' => $request->phase_id,
                'timeline_contains_phase' => collect($project->proposal->timeline)->contains('id', $request->phase_id)
            ]);

            if (!collect($project->proposal->timeline)->contains('id', $request->phase_id)) {
                Log::warning('Invalid Phase ID', [
                    'phase_id' => $request->phase_id,
                    'available_phases' => collect($project->proposal->timeline)->pluck('id'),
                    'project_id' => $project->id
                ]);
                return response()->json(['errors' => ['phase_id' => ['The selected phase does not exist in the project\'s proposal timeline.']]], 422);
            }

            $task->update(['phase_id' => $request->phase_id]);
            Log::info('Task Updated Successfully', [
                'task_id' => $task->id,
                'new_phase_id' => $request->phase_id,
                'updated_task' => $task->fresh()->toArray()
            ]);

            return response()->json([
                'message' => 'Task successfully connected to timeline phase',
                'task' => $task->load(['project', 'assignedUser'])
            ]);

        } catch (\Exception $e) {
            Log::error('Error in connectToTimeline', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'task_id' => $task->id,
                'request_data' => $request->all()
            ]);
            return response()->json(['error' => 'Failed to connect task to timeline: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Disconnect a task from the proposal timeline
     */
    public function disconnectFromTimeline(Task $task)
    {
        Log::info('Disconnecting task from timeline', ['task_id' => $task->id]);
        try {
        $task->update(['phase_id' => null]);
            Log::info('Task successfully disconnected from timeline', ['task_id' => $task->id]);
        return response()->json([
            'message' => 'Task successfully disconnected from timeline phase',
            'task' => $task->load(['project', 'assignedUser'])
        ]);
        } catch (\Exception $e) {
            Log::error('Failed to disconnect task from timeline', [
                'task_id' => $task->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Failed to disconnect task from timeline'], 500);
        }
    }
}
