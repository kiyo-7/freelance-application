<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\contro;
use Illuminate\Support\Facades\Route;


Route::get("/hello ", [contro::class, "index"]);
Route::post('/signup', [AuthController::class, 'signup']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/validate-token', [AuthController::class, 'validateToken']);
