<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\IntakeController;
use App\Http\Controllers\ProposalController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\SubtaskController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::options('/register', function () {
    return response()->json(['status' => 'ok']);
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
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

    Route::apiResource('intakes', IntakeController::class);
    Route::post('intakes/{intake}/forms', [IntakeController::class, 'storeForm']);
    Route::post('intakes/{intake}/attachments', [IntakeController::class, 'storeAttachment']);
    Route::delete('intakes/{intake}/attachments/{attachment}', [IntakeController::class, 'deleteAttachment']);
    Route::get('intakes/link/{link}', [IntakeController::class, 'findByLink']);

    Route::resource('proposals', ProposalController::class);

    // Project routes
    Route::apiResource('projects', ProjectController::class);

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
    });

    // Subtask routes (direct access)
    Route::prefix('subtasks')->group(function () {
        Route::get('/{subtask}', [SubtaskController::class, 'show']);
        Route::put('/{subtask}', [SubtaskController::class, 'update']);
        Route::delete('/{subtask}', [SubtaskController::class, 'destroy']);
        Route::patch('/{subtask}/status', [SubtaskController::class, 'updateStatus']);
    });

    // Additional proposal routes
    Route::post('proposals/{proposal}/send', [ProposalController::class, 'send'])->name('proposals.send');
    Route::post('proposals/{proposal}/save-draft', [ProposalController::class, 'saveDraft'])->name('proposals.save-draft');
    Route::get('proposals/{proposal}/preview', [ProposalController::class, 'preview'])->name('proposals.preview');
});

// Public routes
Route::post('intakes/{intake}/response', [IntakeController::class, 'storeResponse']);
Route::get('/profiles/slug/{slug}', [ProfileController::class, 'showBySlug']);


