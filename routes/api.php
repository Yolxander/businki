<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\IntakeController;
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

    Route::apiResource('clients', ClientController::class);

    Route::apiResource('intakes', IntakeController::class);
    Route::post('intakes/{intake}/forms', [IntakeController::class, 'storeForm']);
    Route::post('intakes/{intake}/attachments', [IntakeController::class, 'storeAttachment']);
    Route::delete('intakes/{intake}/attachments/{attachment}', [IntakeController::class, 'deleteAttachment']);
    Route::get('intakes/link/{link}', [IntakeController::class, 'findByLink']);
});

// Public routes
Route::post('intakes/{intake}/response', [IntakeController::class, 'storeResponse']);
Route::get('/profiles/slug/{slug}', [ProfileController::class, 'showBySlug']);


