<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Import modular route files
require __DIR__.'/public.api.php';
require __DIR__.'/auth.api.php';
require __DIR__.'/admin.api.php';
require __DIR__.'/super-admin.api.php';
