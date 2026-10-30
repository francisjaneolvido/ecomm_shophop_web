<?php

use App\Http\Controllers\Api\MobileAuthController;
use App\Http\Controllers\Api\MobileBuyerAuthController;
use App\Http\Controllers\Api\MobileBuyerCatalogController;
use App\Http\Controllers\Api\MobileBuyerCartController;
use App\Http\Controllers\Api\MobileBuyerCheckoutController;
use App\Http\Controllers\Api\MobileBuyerOrderController;
use App\Http\Controllers\Api\MobileBuyerAddressController;
use App\Http\Controllers\Api\MobileBuyerFavoriteController;
use App\Http\Controllers\Api\MobileRiderAuthController;
use App\Http\Controllers\Api\MobileSellerAuthController;
use App\Http\Controllers\Api\MobileSellerEarningsController;
use App\Http\Controllers\Api\MobileSellerWorkspaceController;
use App\Http\Controllers\Api\MobileRiderDeliveryController;
use App\Http\Controllers\Api\MobileLogisticsScannerController;
use App\Http\Controllers\Api\MobileLinehaulController;
use App\Http\Controllers\Api\MobileRiderTransitController;
use App\Http\Controllers\Api\MobileRiderCodController;
use App\Http\Controllers\Api\MobileRiderReturnController;
use App\Http\Controllers\Api\MobileRiderScannerController;
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

    // Seller registration/verification; authenticated workspace is registered below.
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

        Route::get('/seller/earnings', [MobileSellerEarningsController::class, 'index']);

        // Seller workspace mirrors Seller Web; all actions remain owner-scoped in the controller.
        Route::prefix('seller')->group(function () {
            Route::get('/dashboard', [MobileSellerWorkspaceController::class, 'dashboard']);
            Route::get('/orders', [MobileSellerWorkspaceController::class, 'orders']);
            Route::get('/inventory', [MobileSellerWorkspaceController::class, 'inventory']);
            Route::get('/reports', [MobileSellerWorkspaceController::class, 'reports']);
            Route::get('/profile', [MobileSellerWorkspaceController::class, 'profile']);
            Route::get('/orders/{order}/couriers', [MobileSellerWorkspaceController::class, 'couriers'])->whereNumber('order');
            Route::post('/orders/{order}/accept', [MobileSellerWorkspaceController::class, 'accept'])->whereNumber('order')->middleware('throttle:20,1');
            Route::post('/orders/{order}/prepare', [MobileSellerWorkspaceController::class, 'prepare'])->whereNumber('order')->middleware('throttle:20,1');
            Route::post('/orders/{order}/ready', [MobileSellerWorkspaceController::class, 'ready'])->whereNumber('order')->middleware('throttle:20,1');
            Route::patch('/inventory/{product}/stock', [MobileSellerWorkspaceController::class, 'updateStock'])->whereNumber('product')->middleware('throttle:30,1');
            Route::post('/inventory/products', [MobileSellerWorkspaceController::class, 'saveProduct'])->middleware('throttle:12,1');
            Route::post('/inventory/products/{product}', [MobileSellerWorkspaceController::class, 'saveProduct'])->whereNumber('product')->middleware('throttle:12,1');
            Route::post('/inventory/products/{product}/toggle', [MobileSellerWorkspaceController::class, 'toggleProduct'])->whereNumber('product')->middleware('throttle:12,1');
        });

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
            Route::get('/scanner/manifests', [MobileLogisticsScannerController::class, 'manifests']);
            Route::get('/scanner/load-preview', [MobileLogisticsScannerController::class, 'loadPreview'])->middleware('throttle:60,1');
            Route::post('/scanner/load', [MobileLogisticsScannerController::class, 'loadParcel'])->middleware('throttle:60,1');
            Route::post('/scanner/manifest/batch-load', [MobileLogisticsScannerController::class, 'batchLoad'])->middleware('throttle:30,1');
            Route::get('/scanner/inbound-manifest/preview', [MobileLogisticsScannerController::class, 'inboundManifestPreview'])->middleware('throttle:60,1');
            Route::post('/scanner/inbound-manifest/arrive', [MobileLogisticsScannerController::class, 'acknowledgeManifestArrival'])->middleware('throttle:30,1');
            Route::post('/scanner/inbound-manifest/receive-all', [MobileLogisticsScannerController::class, 'receiveManifestAll'])->middleware('throttle:30,1');
            Route::get('/scanner/manifest-summary', [MobileLogisticsScannerController::class, 'manifestSummary'])->middleware('throttle:60,1');
            Route::get('/scanner/preview', [MobileLogisticsScannerController::class, 'preview'])->middleware('throttle:60,1');
            Route::get('/scanner/history', [MobileLogisticsScannerController::class, 'history'])->middleware('throttle:60,1');
            Route::post('/scanner/receive', [MobileLogisticsScannerController::class, 'receive'])->middleware('throttle:30,1');
        });

        Route::prefix('linehaul')->group(function () {
            Route::get('/trips', [MobileLinehaulController::class, 'trips']);
            Route::get('/earnings', [MobileLinehaulController::class, 'earnings']);
            Route::get('/manifest/preview', [MobileLinehaulController::class, 'preview'])->middleware('throttle:60,1');
            Route::post('/manifest/confirm', [MobileLinehaulController::class, 'confirm'])->middleware('throttle:30,1');
        });

        Route::prefix('rider')->group(function () {
            Route::get('/scanner/preview', [MobileRiderScannerController::class, 'preview'])->middleware('throttle:60,1');
            Route::post('/scanner/confirm', [MobileRiderScannerController::class, 'confirm'])->middleware('throttle:30,1');
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
            | Buyer Mobile Checkout (Cash on Delivery only)
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

        });
    });
});
