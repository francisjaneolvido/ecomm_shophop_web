<?php

use App\Http\Controllers\Api\MobileAuthController;
use App\Http\Controllers\Api\MobileBuyerAuthController;
use App\Http\Controllers\Api\MobileBuyerCatalogController;
use App\Http\Controllers\Api\MobileBuyerCartController;
use App\Http\Controllers\Api\MobileBuyerCheckoutController;
use App\Http\Controllers\Api\MobileBuyerPaymentController;
use App\Http\Controllers\Api\MobileBuyerFavoriteController;
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

        /*
        |--------------------------------------------------------------------------
        | Buyer Mobile Catalog
        |--------------------------------------------------------------------------
        | Uses the same products table, Product::publiclyDiscoverable() scope,
        | seller data, and config/shophop_categories.php used by the web app.
        */
        Route::prefix('buyer')->group(function () {
            Route::get(
                '/home',
                [MobileBuyerCatalogController::class, 'home']
            );

            Route::get(
                '/categories',
                [MobileBuyerCatalogController::class, 'categories']
            );

            Route::get(
                '/products',
                [MobileBuyerCatalogController::class, 'products']
            );

            Route::get(
                '/products/{product}',
                [MobileBuyerCatalogController::class, 'show']
            )->whereNumber('product');


            Route::get(
                '/likes',
                [MobileBuyerFavoriteController::class, 'index']
            );

            Route::post(
                '/likes/{product}',
                [MobileBuyerFavoriteController::class, 'store']
            )->whereNumber('product');

            Route::delete(
                '/likes/{product}',
                [MobileBuyerFavoriteController::class, 'destroy']
            )->whereNumber('product');


            /*
            |--------------------------------------------------------------------------
            | Buyer Mobile Cart
            |--------------------------------------------------------------------------
            | Uses the same cart_items table and inventory rules as the Buyer web cart.
            */
            Route::get(
                '/cart',
                [MobileBuyerCartController::class, 'index']
            );

            Route::post(
                '/cart/add',
                [MobileBuyerCartController::class, 'add']
            );

            Route::post(
                '/cart/remove-many',
                [MobileBuyerCartController::class, 'removeMany']
            );

            Route::get(
                '/cart/count',
                [MobileBuyerCartController::class, 'count']
            );

            Route::patch(
                '/cart/{lineKey}',
                [MobileBuyerCartController::class, 'update']
            )->whereNumber('lineKey');

            Route::delete(
                '/cart/{lineKey}',
                [MobileBuyerCartController::class, 'remove']
            )->whereNumber('lineKey');


            /*
            |--------------------------------------------------------------------------
            | Buyer Mobile Checkout + Manual Online Payment
            |--------------------------------------------------------------------------
            | Checkout uses the same BuyerCheckoutService as the web checkout.
            */
            Route::post(
                '/checkout/preview',
                [MobileBuyerCheckoutController::class, 'preview']
            );

            Route::post(
                '/checkout/place-order',
                [MobileBuyerCheckoutController::class, 'place']
            );

            Route::get(
                '/payments/{payment}',
                [MobileBuyerPaymentController::class, 'show']
            )->whereNumber('payment');

            Route::post(
                '/payments/{payment}/submit',
                [MobileBuyerPaymentController::class, 'submit']
            )->whereNumber('payment');

            Route::post(
                '/payments/{payment}/cancel',
                [MobileBuyerPaymentController::class, 'cancel']
            )->whereNumber('payment');
        });
    });
});
