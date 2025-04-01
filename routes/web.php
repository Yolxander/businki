<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/project',[\App\Http\Controllers\Api\ProjectController::class,'index']);
