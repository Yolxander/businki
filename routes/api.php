<?php

use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\CollaborationController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\ProjectTimelineEventController;
use App\Http\Controllers\Api\ProviderController;
use App\Http\Controllers\Api\SubtaskController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\TaskIssueController;
use App\Http\Controllers\Api\TeamMemberController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::options('/register', function () {
    return response()->json(['status' => 'ok']);
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');


//Clients
Route::prefix('clients')->group(function () {
    Route::get('/', [ClientController::class, 'index']);
    Route::get('/{id}', [ClientController::class, 'show']);
    Route::post('/', [ClientController::class, 'store']);
    Route::put('/{id}', [ClientController::class, 'update']);
    Route::delete('/{id}', [ClientController::class, 'destroy']);
});


//Projects
Route::prefix('projects')->group(function () {
    Route::get('/', [ProjectController::class, 'index']);
    Route::get('/{id}', [ProjectController::class, 'show']);
    Route::post('/', [ProjectController::class, 'store']);
    Route::put('/{id}', [ProjectController::class, 'update']);
    Route::delete('/{id}', [ProjectController::class, 'destroy']);
});

// Extra route for projects by client
Route::get('/clients/{clientId}/projects', [ProjectController::class, 'getProjectsByClient']);

//Task
Route::prefix('tasks')->group(function () {
    Route::get('/', [TaskController::class, 'index']);
    Route::get('/{id}', [TaskController::class, 'show']);
    Route::post('/', [TaskController::class, 'store']);
    Route::put('/{id}', [TaskController::class, 'update']);
    Route::delete('/{id}', [TaskController::class, 'destroy']);
    Route::put('/{id}/toggle-completion', [TaskController::class, 'toggleCompletion']);
});

// Extra route for tasks by project
Route::get('/projects/{projectId}/tasks', [TaskController::class, 'getTasksByProject']);


//Providers
Route::prefix('providers')->group(function () {
    Route::get('/{id}', [ProviderController::class, 'show']);
    Route::put('/{id}', [ProviderController::class, 'update']);
    Route::get('/{id}/dashboard', [ProviderController::class, 'dashboard']);
});

//team-members
Route::prefix('team-members')->group(function () {
    Route::get('/', [TeamMemberController::class, 'index']); // ?provider_id=1
    Route::post('/invite', [TeamMemberController::class, 'invite']);
    Route::put('/{id}', [TeamMemberController::class, 'update']);
});

//collaboration
Route::prefix('collaborations')->group(function () {
    Route::get('/', [CollaborationController::class, 'index']); // ?provider_id=1
    Route::post('/invite', [CollaborationController::class, 'invite']);
    Route::put('/{id}', [CollaborationController::class, 'update']);
});

//Project timeline
Route::prefix('projects/{projectId}/timeline')->group(function () {
    Route::get('/', [ProjectTimelineEventController::class, 'index']);
    Route::post('/', [ProjectTimelineEventController::class, 'store']);
});

Route::prefix('timeline')->group(function () {
    Route::put('/{id}', [ProjectTimelineEventController::class, 'update']);
    Route::delete('/{id}', [ProjectTimelineEventController::class, 'destroy']);
});

//subtasks
Route::prefix('tasks/{taskId}/subtasks')->group(function () {
    Route::get('/', [SubtaskController::class, 'index']);
    Route::post('/', [SubtaskController::class, 'store']);
});

Route::prefix('subtasks')->group(function () {
    Route::put('/{id}', [SubtaskController::class, 'update']);
    Route::delete('/{id}', [SubtaskController::class, 'destroy']);
});

//task issues
Route::prefix('tasks/{taskId}/issues')->group(function () {
    Route::get('/', [TaskIssueController::class, 'index']);
    Route::post('/', [TaskIssueController::class, 'store']);
});

Route::prefix('issues')->group(function () {
    Route::put('/{id}', [TaskIssueController::class, 'update']);
    Route::delete('/{id}', [TaskIssueController::class, 'destroy']);
});
