<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClientIntakeController;
use App\Http\Controllers\ProposalController;
use App\Http\Controllers\ToolController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AuthController;

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
});

// Proposal Routes
Route::middleware(['auth'])->group(function () {

});

