<?php

use Illuminate\Support\Facades\Route;

// Super Admin routes
Route::prefix('super-admin')->middleware('super-admin')->group(function () {
    // Example route for super admin
    Route::get('/get-auth-user', function () {
        return response()->json([
            'user' => auth()->user()
        ]);
    });

    // Add more super admin routes here
});
