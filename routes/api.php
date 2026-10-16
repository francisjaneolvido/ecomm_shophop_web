<?php

use App\Http\Controllers\Api\MobileAuthController;
use App\Http\Controllers\Api\MobileBuyerAuthController;
use App\Http\Controllers\Api\MobileBuyerCatalogController;
use App\Http\Controllers\Api\MobileBuyerCartController;
use App\Http\Controllers\Api\MobileBuyerCheckoutController;
use App\Http\Controllers\Api\MobileBuyerOrderController;
use App\Http\Controllers\Api\MobileBuyerAddressController;
use App\Http\Controllers\Api\MobileBuyerPaymentController;
use App\Http\Controllers\Api\MobileBuyerFavoriteController;
use App\Http\Controllers\Api\MobileRiderAuthController;
use App\Http\Controllers\Api\MobileSellerAuthController;
use App\Http\Controllers\Api\MobileRiderDeliveryController;
use App\Http\Controllers\Api\MobileLogisticsScannerController;
use App\Http\Controllers\Api\MobileRiderTransitController;
use App\Http\Controllers\Api\MobileRiderCodController;
use App\Http\Controllers\Api\MobileRiderReturnController;
use Illuminate\Support\Facades\Route;

Route::prefix('mobile')->group(function () {
    Route::post(
        '/login',
        [MobileAuthController::class, 'login']
    )->middleware('throttle:10,1');

    Route::prefix('rider')->group(function () {
        Route::get(
            '/logistics-centers',
            [MobileRiderAuthController::class, 'logisticsCenters']
        );

        Route::post(
            '/register',
            [MobileRiderAuthController::class, 'register']
        );

        Route::post(
            '/verify-email',
            [MobileRiderAuthController::class, 'verifyEmail']
        );

        Route::post(
            '/verify-email/resend',
            [MobileRiderAuthController::class, 'resendVerification']
        )->middleware('throttle:6,1');
    });

    // Mobile Seller onboarding only. Seller dashboard remains web-only.
    Route::prefix('seller')->group(function () {
        Route::post('/register', [MobileSellerAuthController::class, 'register'])
            ->middleware('throttle:5,1');
        Route::post('/verify-email', [MobileSellerAuthController::class, 'verifyEmail'])
            ->middleware('throttle:10,1');
        Route::post('/verify-email/resend', [MobileSellerAuthController::class, 'resendVerification'])
            ->middleware('throttle:6,1');
    });

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
        // No mobile Logistics registration: existing approved Web Logistics credentials only.
        Route::prefix('logistics')->group(function () {
            Route::get('/scanner/branches', [MobileLogisticsScannerController::class, 'branches']);
            Route::get('/scanner/preview', [MobileLogisticsScannerController::class, 'preview'])->middleware('throttle:60,1');
            Route::get('/scanner/history', [MobileLogisticsScannerController::class, 'history'])->middleware('throttle:60,1');
            Route::post('/scanner/receive', [MobileLogisticsScannerController::class, 'receive'])->middleware('throttle:30,1');
        });

        Route::prefix('rider')->group(function () {
            Route::get('/returns', [MobileRiderReturnController::class, 'index']);
            Route::post('/returns/{case}/pickup', [MobileRiderReturnController::class, 'pickup'])->whereNumber('case');
            Route::post('/returns/{case}/handover', [MobileRiderReturnController::class, 'handover'])->whereNumber('case');
            Route::get('/cod', [MobileRiderCodController::class, 'index']);
            Route::post('/cod/{delivery}/remit', [MobileRiderCodController::class, 'remit'])
                ->whereNumber('delivery')->middleware('throttle:10,1');
            Route::get('/transfers', [MobileRiderTransitController::class, 'index']);
            Route::post('/transfers/{transfer}/depart', [MobileRiderTransitController::class, 'depart'])->whereNumber('transfer');
            Route::get('/earnings', [MobileRiderTransitController::class, 'earnings']);
            Route::get('/assignments', [MobileRiderDeliveryController::class, 'index']);
            Route::get('/assignments/{delivery}', [MobileRiderDeliveryController::class, 'show'])->whereNumber('delivery');
            Route::post('/assignments/{delivery}/accept-pickup', [MobileRiderDeliveryController::class, 'acceptPickup'])->whereNumber('delivery');
            Route::post('/assignments/{delivery}/pickup', [MobileRiderDeliveryController::class, 'pickup'])->whereNumber('delivery');
            Route::post('/assignments/{delivery}/out-for-delivery', [MobileRiderDeliveryController::class, 'outForDelivery'])->whereNumber('delivery');
            Route::post('/assignments/{delivery}/complete', [MobileRiderDeliveryController::class, 'complete'])->whereNumber('delivery');
            Route::post('/assignments/{delivery}/fail', [MobileRiderDeliveryController::class, 'fail'])->whereNumber('delivery');
        });

        Route::prefix('buyer')->group(function () {
            Route::get('/address', [MobileBuyerAddressController::class, 'show']);
            Route::patch('/address', [MobileBuyerAddressController::class, 'update']);

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

            Route::get('/orders', [MobileBuyerOrderController::class, 'index']);
            Route::post('/orders/{order}/confirm-receipt', [MobileBuyerOrderController::class, 'confirmReceipt'])->whereNumber('order');

            Route::get(
                '/payments/{payment}',
                [MobileBuyerPaymentController::class, 'show']
            )->whereNumber('payment');

            Route::post(
                '/payments/{payment}/simulate',
                [MobileBuyerPaymentController::class, 'simulate']
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
