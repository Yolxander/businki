<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\IntakeController;
use App\Http\Controllers\ProposalController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\SubtaskController;
use App\Http\Controllers\ToolController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\PackageController;
use App\Http\Controllers\Api\ListingController;
use App\Http\Controllers\Api\AIGenerationController;
use App\Http\Controllers\Api\AISettingsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::options('/register', function () {
    return response()->json(['status' => 'ok']);
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::middleware(['web', 'auth'])->group(function () {
    Route::post('/profiles', [ProfileController::class, 'store']);
    Route::put('/profiles/{profile}', [ProfileController::class, 'update']);
    Route::get('/profiles/{profile}', [ProfileController::class, 'show']);

    // Client routes
    Route::get('/clients', [ClientController::class, 'index']);
    Route::post('/clients', [ClientController::class, 'store']);
    Route::get('/clients/{id}', [ClientController::class, 'show']);
    Route::put('/clients/{id}', [ClientController::class, 'update']);
    Route::delete('/clients/{id}', [ClientController::class, 'destroy']);
    Route::get('/clients/user/me', [ClientController::class, 'getClientsByUser']);
    Route::post('/clients/{id}/connect', [ClientController::class, 'connectClient']);
    Route::delete('/clients/{id}/disconnect', [ClientController::class, 'disconnectClient']);

    Route::apiResource('intakes', IntakeController::class);
    Route::post('intakes/{intake}/forms', [IntakeController::class, 'storeForm']);
    Route::post('intakes/{intake}/attachments', [IntakeController::class, 'storeAttachment']);
    Route::delete('intakes/{intake}/attachments/{attachment}', [IntakeController::class, 'deleteAttachment']);
    Route::get('intakes/link/{link}', [IntakeController::class, 'findByLink']);

    Route::resource('proposals', ProposalController::class);

    // Project routes


    // Subscription routes
    Route::prefix('subscriptions')->group(function () {
        Route::get('/', [SubscriptionController::class, 'index']);
        Route::post('/', [SubscriptionController::class, 'store']);
        Route::get('/stats', [SubscriptionController::class, 'stats']);
        Route::get('/clients', [SubscriptionController::class, 'getClients']);
        Route::get('/{subscription}', [SubscriptionController::class, 'show']);
        Route::put('/{subscription}', [SubscriptionController::class, 'update']);
        Route::delete('/{subscription}', [SubscriptionController::class, 'destroy']);
    });

    // Task routes
    Route::prefix('tasks')->group(function () {
        // Basic CRUD
        Route::get('/', [TaskController::class, 'index']);
        Route::post('/', [TaskController::class, 'store']);
        Route::get('/{task}', [TaskController::class, 'show']);
        Route::put('/{task}', [TaskController::class, 'update']);
        Route::delete('/{task}', [TaskController::class, 'destroy']);

        // Project and Phase related routes
        Route::get('/project/{projectId}', [TaskController::class, 'getByProject']);
        Route::get('/phase/{phaseId}', [TaskController::class, 'getByPhase']);

        // Proposal Timeline related routes
        Route::get('/project/{projectId}/timeline', [TaskController::class, 'getProjectTimeline']);
        Route::post('/{task}/connect-timeline', [TaskController::class, 'connectToTimeline']);
        Route::delete('/{task}/disconnect-timeline', [TaskController::class, 'disconnectFromTimeline']);

        // Subtask routes (nested under tasks)
        Route::get('/{task}/subtasks', [SubtaskController::class, 'index']);
        Route::post('/{task}/subtasks', [SubtaskController::class, 'store']);

        // Get tasks by project
        Route::get('/project/{projectId}/tasks', [TaskController::class, 'getByProject']);
    });

    // Subtask routes (direct access)
    Route::prefix('subtasks')->group(function () {
        Route::get('/{subtask}', [SubtaskController::class, 'show']);
        Route::put('/{subtask}', [SubtaskController::class, 'update']);
        Route::delete('/{subtask}', [SubtaskController::class, 'destroy']);
        Route::patch('/{subtask}/status', [SubtaskController::class, 'updateStatus']);
    });

    // Tool routes
    Route::prefix('tools')->group(function () {
        // Basic CRUD
        Route::get('/', [ToolController::class, 'index']);
        Route::post('/', [ToolController::class, 'store']);
        Route::get('/{tool}', [ToolController::class, 'show']);
        Route::put('/{tool}', [ToolController::class, 'update']);
        Route::delete('/{tool}', [ToolController::class, 'destroy']);

        // Additional routes
        Route::get('/user/{userId}', [ToolController::class, 'getByUser']);
        Route::get('/status/{status}', [ToolController::class, 'getByStatus']);
    });

    // Service routes
    Route::prefix('services')->group(function () {
        // Basic CRUD
        Route::get('/', [ServiceController::class, 'index']);
        Route::post('/', [ServiceController::class, 'store']);
        Route::get('/{id}', [ServiceController::class, 'show']);
        Route::put('/{id}', [ServiceController::class, 'update']);
        Route::delete('/{id}', [ServiceController::class, 'destroy']);

        // Additional routes
        Route::get('/category/{category}', [ServiceController::class, 'getByCategory']);
        Route::get('/pricing-type/{pricingType}', [ServiceController::class, 'getByPricingType']);
    });

    // Package routes
    Route::prefix('packages')->group(function () {
        // Basic CRUD
        Route::get('/', [PackageController::class, 'index']);
        Route::post('/', [PackageController::class, 'store']);
        Route::get('/{id}', [PackageController::class, 'show']);
        Route::put('/{id}', [PackageController::class, 'update']);
        Route::delete('/{id}', [PackageController::class, 'destroy']);

        // Additional routes
        Route::get('/type/{type}', [PackageController::class, 'getByType']);
        Route::get('/billing-cycle/{billingCycle}', [PackageController::class, 'getByBillingCycle']);
    });

    // Listing routes
    Route::apiResource('listings', ListingController::class);

    // Additional proposal routes
    Route::post('proposals/{proposal}/send', [ProposalController::class, 'send'])->name('proposals.send');
    Route::post('proposals/{proposal}/save-draft', [ProposalController::class, 'saveDraft'])->name('proposals.save-draft');
    Route::get('proposals/{proposal}/preview', [ProposalController::class, 'preview'])->name('proposals.preview');

    // AI Generation routes
    Route::post('intake-responses/{id}/generate-proposal', [AIGenerationController::class, 'generateProposal']);
    Route::post('proposals/{id}/generate-project', [AIGenerationController::class, 'generateProject']);
    Route::post('projects/{projectId}/generate-tasks', [AIGenerationController::class, 'generateTasks']);
    Route::post('tasks/{taskId}/generate-subtasks', [AIGenerationController::class, 'generateSubtasks']);

    // AI Models routes
    Route::get('ai-models/default', [AISettingsController::class, 'getDefaultModel']);

    // Prompt Engineering routes
    Route::post('prompt-engineering/make-reusable', [App\Http\Controllers\Api\PromptEngineeringController::class, 'makeReusable']);

    // Context Engineering routes
    Route::prefix('context-engineering')->group(function () {
        Route::get('/documents', [App\Http\Controllers\Api\ContextEngineeringController::class, 'index']);
        Route::post('/documents', [App\Http\Controllers\Api\ContextEngineeringController::class, 'store']);
        Route::get('/documents/{document}', [App\Http\Controllers\Api\ContextEngineeringController::class, 'show']);
        Route::put('/documents/{document}', [App\Http\Controllers\Api\ContextEngineeringController::class, 'update']);
        Route::delete('/documents/{document}', [App\Http\Controllers\Api\ContextEngineeringController::class, 'destroy']);
        Route::get('/documents/{document}/download', [App\Http\Controllers\Api\ContextEngineeringController::class, 'download']);
        Route::post('/documents/{document}/upload', [App\Http\Controllers\Api\ContextEngineeringController::class, 'upload']);
        Route::post('/documents/{document}/version', [App\Http\Controllers\Api\ContextEngineeringController::class, 'createVersion']);
        Route::post('/documents/{document}/activate', [App\Http\Controllers\Api\ContextEngineeringController::class, 'activate']);
        Route::get('/templates', [App\Http\Controllers\Api\ContextEngineeringController::class, 'templates']);
        Route::get('/types', [App\Http\Controllers\Api\ContextEngineeringController::class, 'types']);
        Route::get('/stats', [App\Http\Controllers\Api\ContextEngineeringController::class, 'stats']);
        Route::post('/documents/{document}/regenerate', [App\Http\Controllers\Api\ContextEngineeringController::class, 'regenerateDocument']);
        Route::post('/generate', [App\Http\Controllers\Api\ContextEngineeringController::class, 'generate']);
    });


});

// Public routes
Route::post('intakes/{intake}/response', [IntakeController::class, 'storeResponse']);
Route::get('/profiles/slug/{slug}', [ProfileController::class, 'showBySlug']);


