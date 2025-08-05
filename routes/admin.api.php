<?php

use Illuminate\Support\Facades\Route;

// Admin routes
Route::prefix('admin')->middleware('admin')->group(function () {
    Route::get('/get-auth-user', function () {
        return response()->json([
            'user' => auth()->user()
        ]);
    });
    // Add more admin routes here
});
