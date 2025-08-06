<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// Public authentication routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::post('/request-password-reset', [AuthController::class, 'requestPasswordReset']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
