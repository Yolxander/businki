<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClientIntakeController;
use App\Http\Controllers\ProposalController;
use App\Http\Controllers\ToolController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/openai-tester', function () {
    return view('openai-tester-page');
})->name('openai.tester');

// Proposal Routes
Route::middleware(['auth'])->group(function () {

});

