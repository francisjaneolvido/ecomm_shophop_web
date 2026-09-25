<?php

use App\Http\Controllers\Api\MobileAuthController;
use App\Http\Controllers\Api\MobileBuyerAuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('mobile')->group(function () {
    Route::post(
        '/login',
        [MobileAuthController::class, 'login']
    )->middleware('throttle:10,1');

    Route::prefix('buyer')->group(function () {
        Route::post(
            '/register',
            [MobileBuyerAuthController::class, 'register']
        );

        Route::post(
            '/verify-email',
            [MobileBuyerAuthController::class, 'verifyEmail']
        );

        Route::post(
            '/verify-email/resend',
            [MobileBuyerAuthController::class, 'resendVerification']
        )->middleware('throttle:6,1');
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::get(
            '/me',
            [MobileAuthController::class, 'me']
        );

        Route::post(
            '/logout',
            [MobileAuthController::class, 'logout']
        );
    });
});
