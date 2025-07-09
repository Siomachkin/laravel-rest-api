<?php

use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\UserEmailController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // User routes
    Route::apiResource('users', UserController::class);

    // Send welcome email
    Route::post('users/{user}/send-welcome', [UserController::class, 'sendWelcome']);

    // User email routes
    Route::apiResource('users.emails', UserEmailController::class)->except(['show']);

    // Set primary email
    Route::patch('users/{user}/emails/{email}/set-primary', [UserEmailController::class, 'setPrimary']);
});
