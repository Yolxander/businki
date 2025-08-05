<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClientIntakeController;
use App\Http\Controllers\ProposalController;
use App\Http\Controllers\ToolController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProjectController;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\PromptController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/openai-tester', function () {
    return view('openai-tester-page');
})->name('openai.tester');

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register'])->name('register');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Dashboard Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/api-dashboard', function () {
        return Inertia::render('ApiDashboard', [
            'auth' => [
                'user' => Auth::user(),
            ],
        ]);
    })->name('api-dashboard');
    Route::get('/user-management', function () {
        return Inertia::render('UserManagement', [
            'auth' => [
                'user' => Auth::user(),
            ],
        ]);
    })->name('user-management');
    Route::get('/analytics', function () {
        return Inertia::render('Analytics', [
            'auth' => [
                'user' => Auth::user(),
            ],
        ]);
    })->name('analytics');
    Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
    Route::get('/clients', [App\Http\Controllers\ClientController::class, 'index'])->name('clients');
    Route::get('/clients/create', function () {
        return Inertia::render('CreateClient', [
            'auth' => [
                'user' => Auth::user(),
            ],
        ]);
    })->name('clients.create');
    Route::post('/clients', [App\Http\Controllers\ClientController::class, 'store'])->name('clients.store');
    Route::get('/clients/{id}/edit', function ($id) {
        return Inertia::render('EditClient', [
            'auth' => [
                'user' => Auth::user(),
            ],
            'clientId' => $id,
        ]);
    })->name('clients.edit');
    Route::get('/clients/{id}', function ($id) {
        return Inertia::render('ClientDetails', [
            'auth' => [
                'user' => Auth::user(),
            ],
            'clientId' => $id,
        ]);
    })->name('clients.show');
    Route::put('/clients/{id}', [App\Http\Controllers\ClientController::class, 'update'])->name('clients.update');
    Route::delete('/clients/{id}', [App\Http\Controllers\ClientController::class, 'destroy'])->name('clients.destroy');
    Route::get('/proposals/{id}', function ($id) {
        return Inertia::render('ProposalDetails', [
            'auth' => [
                'user' => Auth::user(),
            ],
            'proposalId' => $id,
        ]);
    })->name('proposals.show');
    Route::get('/proposals', function () {
        return Inertia::render('Proposals', [
            'auth' => [
                'user' => Auth::user(),
            ],
        ]);
    })->name('proposals.index');
    Route::get('/proposals/create', function () {
        return Inertia::render('CreateProposal', [
            'auth' => [
                'user' => Auth::user(),
            ],
        ]);
    })->name('proposals.create');
    Route::get('/subscriptions', function () {
        return Inertia::render('Subscriptions', [
            'auth' => [
                'user' => Auth::user(),
            ],
        ]);
    })->name('subscriptions');
    Route::get('/subscriptions/{id}', function ($id) {
        return Inertia::render('SubscriptionDetails', [
            'auth' => [
                'user' => Auth::user(),
            ],
            'subscriptionId' => $id,
        ]);
    })->name('subscriptions.show');
    Route::get('/bobbi-flow', [App\Http\Controllers\TaskController::class, 'bobbiFlow'])->name('bobbi-flow');
    Route::get('/tasks', function () {
        return Inertia::render('Tasks', [
            'auth' => [
                'user' => Auth::user(),
            ],
        ]);
    })->name('tasks.index');
    Route::get('/tasks/create', [App\Http\Controllers\TaskController::class, 'create'])->name('tasks.create');

Route::get('/tasks/{task}', [App\Http\Controllers\TaskController::class, 'show'])->name('tasks.show');

Route::get('/tasks/{task}/edit', [App\Http\Controllers\TaskController::class, 'edit'])->name('tasks.edit');

Route::get('/tasks/{task}/start-work', [App\Http\Controllers\TaskController::class, 'startWork'])->name('tasks.start-work');

    // Task web routes for Inertia.js
    Route::post('/tasks', [App\Http\Controllers\TaskController::class, 'store'])->name('tasks.store');
    Route::put('/tasks/{task}', [App\Http\Controllers\TaskController::class, 'update'])->name('tasks.update');
    Route::delete('/tasks/{task}', [App\Http\Controllers\TaskController::class, 'destroy'])->name('tasks.destroy');

    // Subtask routes
    Route::post('/tasks/{task}/subtasks', [App\Http\Controllers\TaskController::class, 'addSubtask'])->name('tasks.subtasks.store');
    Route::put('/tasks/{task}/subtasks/{subtask}', [App\Http\Controllers\TaskController::class, 'updateSubtask'])->name('tasks.subtasks.update');

    Route::get('/calendar', [App\Http\Controllers\CalendarController::class, 'index'])->name('calendar');
    Route::get('/projects/create', [ProjectController::class, 'create'])->name('projects.create');
    Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
    Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('projects.show');
    Route::get('/projects/{project}/edit', [ProjectController::class, 'edit'])->name('projects.edit');
    Route::put('/projects/{project}', [ProjectController::class, 'update'])->name('projects.update');
    Route::delete('/projects/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');
    Route::post('/projects/new-client-project', [ProjectController::class, 'newClientProject'])->name('projects.new-client-project');
    Route::post('/projects/connect-client', [ProjectController::class, 'connectClientForProject'])->name('projects.connect-client');
    Route::post('/ai/generate-project', [ProjectController::class, 'generateProjectWithAI'])->name('ai.generate-project');
    Route::get('/ai-settings', function () {
        return Inertia::render('AISettings', [
            'auth' => [
                'user' => Auth::user(),
            ],
        ]);
    })->name('ai-settings');

    Route::get('/ai-settings/models/new', function () {
        return Inertia::render('AddModel', [
            'auth' => [
                'user' => Auth::user(),
            ],
        ]);
    })->name('ai-settings.add-model');

    Route::get('/ai-settings/providers/new', function () {
        return Inertia::render('AddProvider', [
            'auth' => [
                'user' => Auth::user(),
            ],
        ]);
    })->name('ai-settings.add-provider');

    Route::get('/ai-settings/providers/{providerId}', function ($providerId) {
        return Inertia::render('ProviderDetails', [
            'auth' => [
                'user' => Auth::user(),
            ],
            'providerId' => $providerId,
        ]);
    })->name('ai-settings.provider-details');

    Route::get('/ai-settings/models/{modelId}', function ($modelId) {
        return Inertia::render('ModelDetails', [
            'auth' => [
                'user' => Auth::user(),
            ],
            'modelId' => $modelId,
        ]);
    })->name('ai-settings.model-details');
    Route::get('/playground', function () {
        return Inertia::render('Playground', [
            'auth' => [
                'user' => Auth::user(),
            ],
        ]);
    })->name('playground');

    Route::get('/prompt-engineering', function () {
        return Inertia::render('PromptEngineering', [
            'auth' => [
                'user' => Auth::user(),
            ],
        ]);
    })->name('prompt-engineering');

    Route::get('/prompt-engineering/prompts/{promptId}', function ($promptId) {
        return Inertia::render('PromptDetails', [
            'auth' => [
                'user' => Auth::user(),
            ],
            'promptId' => $promptId,
        ]);
    })->name('prompt-engineering.details');

    // Playground API routes
    Route::get('/api/playground/templates', [App\Http\Controllers\Api\PlaygroundController::class, 'getTemplates']);
    Route::post('/api/playground/generate', [App\Http\Controllers\Api\PlaygroundController::class, 'generate']);
    Route::post('/api/playground/test-template', [App\Http\Controllers\Api\PlaygroundController::class, 'testTemplate']);

    // CSRF token refresh route
    Route::get('/api/csrf-token', function () {
        return response()->json(['token' => csrf_token()]);
    });

    // Prompt Engineering API routes
    Route::get('/api/prompt-engineering/saved-prompts', [App\Http\Controllers\Api\PromptEngineeringController::class, 'getSavedPrompts']);
    Route::get('/api/prompt-engineering/prompts/{id}', [App\Http\Controllers\Api\PromptEngineeringController::class, 'getPromptDetails']);
    Route::post('/api/prompt-engineering/templates', [App\Http\Controllers\Api\PromptEngineeringController::class, 'createTemplate']);
    Route::put('/api/prompt-engineering/templates/{id}', [App\Http\Controllers\Api\PromptEngineeringController::class, 'updateTemplate']);
    Route::delete('/api/prompt-engineering/templates/{id}', [App\Http\Controllers\Api\PromptEngineeringController::class, 'deleteTemplate']);
    Route::post('/api/prompt-engineering/optimize', [App\Http\Controllers\Api\PromptEngineeringController::class, 'optimizePrompt']);
    Route::post('/api/prompt-engineering/test', [App\Http\Controllers\Api\PromptEngineeringController::class, 'testPrompt']);

    // AI Settings API routes (moved from api.php to work with web sessions)
    Route::post('/api/ai-settings/test-aimlapi-connection', [App\Http\Controllers\Api\AISettingsController::class, 'testAIMLAPIConnection']);
    Route::post('/api/ai-settings/test-openai-connection', [App\Http\Controllers\Api\AISettingsController::class, 'testOpenAIConnection']);
    Route::post('/api/ai-settings/providers/{providerId}/test-connection', [App\Http\Controllers\Api\AISettingsController::class, 'testProviderConnection']);
    Route::get('/api/ai-settings/providers', [App\Http\Controllers\Api\AISettingsController::class, 'getAIProviders']);
    Route::post('/api/ai-settings/providers', [App\Http\Controllers\Api\AISettingsController::class, 'saveAIProvider']);
    Route::get('/api/ai-settings/providers/{providerId}', [App\Http\Controllers\Api\AISettingsController::class, 'getAIProviderDetails']);
    Route::put('/api/ai-settings/providers/{providerId}', [App\Http\Controllers\Api\AISettingsController::class, 'updateAIProvider']);
    Route::delete('/api/ai-settings/providers/{providerId}', [App\Http\Controllers\Api\AISettingsController::class, 'deleteAIProvider']);
    Route::get('/api/ai-settings/providers/{providerId}/models', [App\Http\Controllers\Api\AISettingsController::class, 'getProviderModels']);
    Route::get('/api/ai-settings/providers/{providerId}/stats', [App\Http\Controllers\Api\AISettingsController::class, 'getProviderStats']);
    Route::get('/api/ai-settings/models', [App\Http\Controllers\Api\AISettingsController::class, 'getAIModels']);
    Route::post('/api/ai-settings/models', [App\Http\Controllers\Api\AISettingsController::class, 'saveAIModel']);
    Route::get('/api/ai-settings/configuration', [App\Http\Controllers\Api\AISettingsController::class, 'getAIConfiguration']);
    Route::put('/api/ai-settings/configuration', [App\Http\Controllers\Api\AISettingsController::class, 'updateAIConfiguration']);
    Route::post('/api/ai-settings/test-completion', [App\Http\Controllers\Api\AISettingsController::class, 'generateTestCompletion']);
    Route::get('/api/ai-settings/stats', [App\Http\Controllers\Api\AISettingsController::class, 'getAIStats']);
    Route::patch('/api/ai-settings/models/{modelId}/status', [App\Http\Controllers\Api\AISettingsController::class, 'toggleModelStatus']);

    // Context Engineering Routes
    Route::get('/context-engineering', [App\Http\Controllers\ContextEngineeringController::class, 'index'])->name('context-engineering');
    Route::get('/context-engineering/projects/{project}', [App\Http\Controllers\ContextEngineeringController::class, 'showProject'])->name('context-engineering.project');
    Route::get('/context-engineering/create', [App\Http\Controllers\ContextEngineeringController::class, 'create'])->name('context-engineering.create');
    Route::post('/context-engineering/project', [App\Http\Controllers\ContextEngineeringController::class, 'createProject'])->name('context-engineering.create-project');
    Route::get('/context-engineering/{document}', [App\Http\Controllers\ContextEngineeringController::class, 'show'])->name('context-engineering.show');
    Route::get('/context-engineering/{document}/edit', [App\Http\Controllers\ContextEngineeringController::class, 'edit'])->name('context-engineering.edit');
    Route::get('/context-engineering/{document}/download', [App\Http\Controllers\ContextEngineeringController::class, 'download'])->name('context-engineering.download');

    // Zen Mode Route
    Route::get('/zen-mode', function (Request $request) {
        $taskId = $request->query('task');
        $task = null;
        
        if ($taskId) {
            $task = \App\Models\Task::with(['project.client', 'subtasks'])->find($taskId);
            if ($task) {
                $task = [
                    'id' => $task->id,
                    'title' => $task->title,
                    'client' => $task->project?->client?->name || 'No Client',
                    'project' => $task->project?->name || 'No Project',
                    'dueDate' => $task->due_date ? $task->due_date->format('Y-m-d') : null,
                    'subtasks' => $task->subtasks->map(function($subtask) {
                        return [
                            'id' => $subtask->id,
                            'text' => $subtask->description,
                            'completed' => $subtask->status === 'done'
                        ];
                    })->toArray()
                ];
            }
        }
        
        return Inertia::render('ZenMode', [
            'auth' => [
                'user' => Auth::user(),
            ],
            'zenTask' => $task,
        ]);
    })->name('zen-mode');

    // Context Engineering API routes
    Route::get('/api/context-engineering/documents', [App\Http\Controllers\Api\ContextEngineeringController::class, 'index']);
    Route::get('/api/context-engineering/documents/{document}', [App\Http\Controllers\Api\ContextEngineeringController::class, 'show']);
    Route::post('/api/context-engineering/documents', [App\Http\Controllers\Api\ContextEngineeringController::class, 'store']);
    Route::put('/api/context-engineering/documents/{document}', [App\Http\Controllers\Api\ContextEngineeringController::class, 'update']);
    Route::delete('/api/context-engineering/documents/{document}', [App\Http\Controllers\Api\ContextEngineeringController::class, 'destroy']);
    Route::post('/api/context-engineering/generate', [App\Http\Controllers\Api\ContextEngineeringController::class, 'generate']);
    Route::post('/api/context-engineering/upload', [App\Http\Controllers\Api\ContextEngineeringController::class, 'upload']);
    Route::post('/api/context-engineering/documents/{document}/version', [App\Http\Controllers\Api\ContextEngineeringController::class, 'createVersion']);
    Route::patch('/api/context-engineering/documents/{document}/activate', [App\Http\Controllers\Api\ContextEngineeringController::class, 'activate']);
    Route::get('/api/context-engineering/templates', [App\Http\Controllers\Api\ContextEngineeringController::class, 'templates']);
    Route::post('/api/context-engineering/documents/{document}/regenerate', [App\Http\Controllers\Api\ContextEngineeringController::class, 'regenerateDocument']);
    Route::get('/api/context-engineering/types', [App\Http\Controllers\Api\ContextEngineeringController::class, 'types']);
    Route::get('/api/context-engineering/stats', [App\Http\Controllers\Api\ContextEngineeringController::class, 'stats']);

    // Chat API routes
    Route::get('/api/chats/recent', [App\Http\Controllers\ChatController::class, 'getRecentChats']);
    Route::get('/api/chats/all', [App\Http\Controllers\ChatController::class, 'getAllChats']);
    Route::get('/api/chats/{chatId}', [App\Http\Controllers\ChatController::class, 'getChat']);
    Route::post('/api/chats', [App\Http\Controllers\ChatController::class, 'createChat']);
    Route::post('/api/chats/{chatId}/messages', [App\Http\Controllers\ChatController::class, 'sendMessage']);
    Route::delete('/api/chats/{chatId}', [App\Http\Controllers\ChatController::class, 'deleteChat']);
});

// Proposal Routes
Route::middleware(['auth'])->group(function () {

});

Route::resource('prompts', PromptController::class);

Route::get('/prompt-management', function () {
    return Inertia::render('PromptManagement', [
        'auth' => [
            'user' => Auth::user(),
        ],
    ]);
})->name('prompt-management');

