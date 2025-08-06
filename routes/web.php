<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'message' => 'Laravel API is running',
        'status' => 'success'
    ]);
});

Route::get('/reset-password/{token}', function ($token) {
    return "Password reset token: $token";
})->name('password.reset');
