<?php

use Illuminate\Support\Facades\Route;

// Authenticated user routes
Route::middleware('auth:sanctum')->group(function () {

    Route::get('/get-auth-user', function () {
        return response()->json([
            'user' => auth()->user()
        ]);
    });
    // Add more authenticated routes here
});
