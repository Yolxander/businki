<?php

use App\Http\Controllers\Api\CalendarEventController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\CodeSnippetController;
use App\Http\Controllers\Api\CollaborationController;
use App\Http\Controllers\Api\FileController;
use App\Http\Controllers\Api\FolderController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\ProjectTimelineEventController;
use App\Http\Controllers\Api\ProviderController;
use App\Http\Controllers\Api\ProviderTypeController;
use App\Http\Controllers\Api\SubtaskController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\TaskIssueController;
use App\Http\Controllers\Api\TeamMemberController;
use App\Http\Controllers\AuthController;
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


//Clients
Route::prefix('clients')->group(function () {
    Route::get('/', [ClientController::class, 'index']);
    Route::get('info/{id}', [ClientController::class, 'show']);
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
    Route::post('/', [ProviderController::class, 'store']); // 👈 Add this line
    Route::get('/by-user/{id}',[ProviderController::class,'getByUser']);
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
    Route::post('/', [SubtaskController::class, 'store']); // ✅ taskId passed from route
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

//code snippets
Route::prefix('tasks/{taskId}/code-snippets')->group(function () {
    Route::get('/', [CodeSnippetController::class, 'indexForTask']);
    Route::post('/', [CodeSnippetController::class, 'storeForTask']);
});

Route::prefix('issues/{issueId}/code-snippets')->group(function () {
    Route::get('/', [CodeSnippetController::class, 'indexForIssue']);
    Route::post('/', [CodeSnippetController::class, 'storeForIssue']);
});

Route::prefix('code-snippets')->group(function () {
    Route::put('/{id}', [CodeSnippetController::class, 'update']);
    Route::delete('/{id}', [CodeSnippetController::class, 'destroy']);
});


//provider types
Route::prefix('provider-types')->group(function () {
    Route::get('/', [ProviderTypeController::class, 'index']);
    Route::get('/{id}', [ProviderTypeController::class, 'show']);
    Route::post('/', [ProviderTypeController::class, 'store']);
    Route::put('/{id}', [ProviderTypeController::class, 'update']);
    Route::delete('/{id}', [ProviderTypeController::class, 'destroy']);
});

//calendar
Route::prefix('calendar-events')->group(function () {
    Route::get('/', [CalendarEventController::class, 'index']);
    Route::post('/', [CalendarEventController::class, 'store']);
    Route::get('/{id}', [CalendarEventController::class, 'show']);
    Route::put('/{id}', [CalendarEventController::class, 'update']);
    Route::delete('/{id}', [CalendarEventController::class, 'destroy']);
});

// Folders
Route::prefix('folders')->group(function () {
    Route::get('/', [FolderController::class, 'index']); // ?provider_id=&project_id=&parent_id=
    Route::post('/', [FolderController::class, 'store']);
    Route::get('/{id}', [FolderController::class, 'show']);
    Route::put('/{id}', [FolderController::class, 'update']);
    Route::delete('/{id}', [FolderController::class, 'destroy']);
});

// Files
Route::prefix('folders/{folderId}/files')->group(function () {
    Route::get('/', [FileController::class, 'index']);
    Route::post('/', [FileController::class, 'store']);
});

Route::prefix('files')->group(function () {
    Route::get('/{id}', [FileController::class, 'show']);
    Route::post('/{id}', [FileController::class, 'update']);
    Route::delete('/{id}', [FileController::class, 'destroy']);
    Route::get('/{id}/download', [FileController::class, 'download']);
    Route::get('/{providerId}/files', [FileController::class, 'getFilesByProvider']);
});

