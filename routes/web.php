<?php

use App\Http\Controllers\Api\ProjectController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/project',[ProjectController::class,'index']);
Route::get('/projects/by-client', [ProjectController::class, 'getByClient']);

Route::get('/projects/all',[ProjectController::class,'allProjects']);
