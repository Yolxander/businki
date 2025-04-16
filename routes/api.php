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

use App\Http\Controllers\Api\ProposalController;
use App\Http\Controllers\Api\ProposalContentController;
use App\Http\Controllers\Api\ProposalVersionController;
use App\Http\Controllers\Api\ProposalCommentController;
use App\Http\Controllers\Api\ProposalAttachmentController;
use App\Http\Controllers\Api\ProposalSignatureController;
use App\Http\Controllers\Api\ProposalTemplateController;
use App\Http\Controllers\Api\ProposalExportController;

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

//Proposal
Route::prefix('proposals')->group(function () {
    Route::get('/', [ProposalController::class, 'index']);
    Route::get('/create', [ProposalController::class, 'create']);
    Route::post('/', [ProposalController::class, 'store']);
    Route::get('/{proposal}', [ProposalController::class, 'show']);
    Route::get('/{proposal}/edit', [ProposalController::class, 'edit']);
    Route::put('/{proposal}', [ProposalController::class, 'update']);
    Route::delete('/{proposal}', [ProposalController::class, 'destroy']);

    Route::post('/{proposal}/send', [ProposalController::class, 'send']);
    Route::post('/{proposal}/accept', [ProposalController::class, 'accept']);
    Route::post('/{proposal}/reject', [ProposalController::class, 'reject']);
    Route::post('/{proposal}/duplicate', [ProposalController::class, 'duplicate']);
    Route::post('/{proposal}/convert-to-template', [ProposalController::class, 'convertToTemplate']);

    Route::prefix('{proposal}/content')->group(function () {
        Route::get('/', [ProposalContentController::class, 'show']);
        Route::put('/', [ProposalContentController::class, 'update']);
    });

    Route::prefix('{proposal}/versions')->group(function () {
        Route::get('/', [ProposalVersionController::class, 'index']);
        Route::get('/{version}', [ProposalVersionController::class, 'show']);
        Route::post('/{version}/restore', [ProposalVersionController::class, 'restore']);
    });

    Route::prefix('{proposal}/comments')->group(function () {
        Route::get('/', [ProposalCommentController::class, 'index']);
        Route::post('/', [ProposalCommentController::class, 'store']);
        Route::put('/{comment}', [ProposalCommentController::class, 'update']);
        Route::delete('/{comment}', [ProposalCommentController::class, 'destroy']);
    });

    Route::prefix('{proposal}/attachments')->group(function () {
        Route::get('/', [ProposalAttachmentController::class, 'index']);
        Route::post('/', [ProposalAttachmentController::class, 'store']);
        Route::delete('/{attachment}', [ProposalAttachmentController::class, 'destroy']);
        Route::get('/{attachment}/download', [ProposalAttachmentController::class, 'download']);
    });

    Route::prefix('{proposal}/signatures')->group(function () {
        Route::get('/', [ProposalSignatureController::class, 'index']);
        Route::post('/', [ProposalSignatureController::class, 'store']);
        Route::get('/{signature}', [ProposalSignatureController::class, 'show']);
    });

    Route::prefix('templates')->group(function () {
        Route::get('/', [ProposalTemplateController::class, 'index']);
        Route::get('/create', [ProposalTemplateController::class, 'create']);
        Route::post('/', [ProposalTemplateController::class, 'store']);
        Route::get('/{template}', [ProposalTemplateController::class, 'show']);
        Route::get('/{template}/edit', [ProposalTemplateController::class, 'edit']);
        Route::put('/{template}', [ProposalTemplateController::class, 'update']);
        Route::delete('/{template}', [ProposalTemplateController::class, 'destroy']);
        Route::post('/{template}/use', [ProposalTemplateController::class, 'useTemplate']);
    });

    Route::prefix('{proposal}/export')->group(function () {
        Route::get('/pdf', [ProposalExportController::class, 'pdf']);
        Route::get('/docx', [ProposalExportController::class, 'docx']);
    });

    Route::get('/search', [ProposalController::class, 'search']);
    Route::get('/filter', [ProposalController::class, 'filter']);
});
