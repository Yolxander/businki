<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClientIntakeController;
use App\Http\Controllers\ProposalController;
use App\Http\Controllers\ToolController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AuthController;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

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
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Dashboard Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/user-management', function () {
        return Inertia::render('UserManagement', [
            'auth' => [
                'user' => Auth::user(),
            ],
        ]);
    })->name('user-management');
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

    // Context Engineering API routes
    Route::get('/api/context-engineering/documents', [App\Http\Controllers\Api\ContextEngineeringController::class, 'index']);
    Route::get('/api/context-engineering/documents/{document}', [App\Http\Controllers\Api\ContextEngineeringController::class, 'show']);
    Route::post('/api/context-engineering/documents', [App\Http\Controllers\Api\ContextEngineeringController::class, 'store']);
    Route::put('/api/context-engineering/documents/{document}', [App\Http\Controllers\Api\ContextEngineeringController::class, 'update']);
    Route::delete('/api/context-engineering/documents/{document}', [App\Http\Controllers\Api\ContextEngineeringController::class, 'destroy']);
    Route::post('/api/context-engineering/project', [App\Http\Controllers\Api\ContextEngineeringController::class, 'createProject']);
    Route::post('/api/context-engineering/generate', [App\Http\Controllers\Api\ContextEngineeringController::class, 'generate']);
    Route::post('/api/context-engineering/upload', [App\Http\Controllers\Api\ContextEngineeringController::class, 'upload']);
    Route::post('/api/context-engineering/documents/{document}/version', [App\Http\Controllers\Api\ContextEngineeringController::class, 'createVersion']);
    Route::patch('/api/context-engineering/documents/{document}/activate', [App\Http\Controllers\Api\ContextEngineeringController::class, 'activate']);
    Route::get('/api/context-engineering/templates', [App\Http\Controllers\Api\ContextEngineeringController::class, 'templates']);
    Route::get('/api/context-engineering/projects', [App\Http\Controllers\Api\ContextEngineeringController::class, 'projects']);
    Route::delete('/api/context-engineering/projects/{project}', [App\Http\Controllers\Api\ContextEngineeringController::class, 'deleteProject']);
    Route::post('/api/context-engineering/documents/{document}/regenerate', [App\Http\Controllers\Api\ContextEngineeringController::class, 'regenerateDocument']);
    Route::get('/api/context-engineering/types', [App\Http\Controllers\Api\ContextEngineeringController::class, 'types']);
    Route::get('/api/context-engineering/stats', [App\Http\Controllers\Api\ContextEngineeringController::class, 'stats']);
});

// Proposal Routes
Route::middleware(['auth'])->group(function () {

});

