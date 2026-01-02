<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\ProjectController; 
use App\Http\Controllers\MessageController;


Route::post('/signup', [AuthController::class, 'signup']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/validate-token', [AuthController::class, 'validateToken']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/profile', [UserProfileController::class, 'show']);
    Route::put('/profile', [UserProfileController::class, 'update']);

    Route::get('/projects', [ProjectController::class, 'index']);
    Route::post('/projects', [ProjectController::class, 'store']);
    Route::get('/projects/{project}', [ProjectController::class, 'show']);

    Route::get('/projects/{project}/messages', [MessageController::class, 'index']);
    Route::post('/projects/{project}/messages', [MessageController::class, 'store']);
    Route::get('/messages/{message}', [MessageController::class, 'show']);
});
