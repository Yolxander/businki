<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\IntakeController;
use App\Http\Controllers\ProposalController;
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

    // Additional proposal routes
    Route::post('proposals/{proposal}/send', [ProposalController::class, 'send'])->name('proposals.send');
    Route::post('proposals/{proposal}/save-draft', [ProposalController::class, 'saveDraft'])->name('proposals.save-draft');
    Route::get('proposals/{proposal}/preview', [ProposalController::class, 'preview'])->name('proposals.preview');
});

// Public routes
Route::post('intakes/{intake}/response', [IntakeController::class, 'storeResponse']);
Route::get('/profiles/slug/{slug}', [ProfileController::class, 'showBySlug']);


