<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\SellerEmailVerificationController;
use App\Http\Controllers\Seller\InventoryController;
use App\Http\Controllers\Seller\OrderController;

/*
|--------------------------------------------------------------------------
| Seller Routes
|--------------------------------------------------------------------------
*/

Route::prefix('seller')
    ->name('seller.')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | PUBLIC EMAIL VERIFICATION ROUTES
        |--------------------------------------------------------------------------
        |
        | Hindi dapat may "auth" dito dahil logged out ang seller
        | habang kino-complete ang OTP verification.
        |
        */

        Route::get('/verify-email', [
            SellerEmailVerificationController::class,
            'show',
        ])->name('verify-email.show');

        Route::post('/verify-email', [
            SellerEmailVerificationController::class,
            'verify',
        ])->name('verify-email.verify');

        Route::post('/verify-email/resend', [
            SellerEmailVerificationController::class,
            'resend',
        ])
            ->middleware('throttle:6,1')
            ->name('verify-email.resend');


        /*
        |--------------------------------------------------------------------------
        | PROTECTED SELLER PORTAL
        |--------------------------------------------------------------------------
        |
        | Requirements:
        | - logged in
        | - seller account
        | - email verified
        | - admin approved
        |
        */

        Route::middleware([
            'auth',
            'approved.role:seller',
        ])->group(function () {

            /*
            |--------------------------------------------------------------------------
            | Dashboard
            |--------------------------------------------------------------------------
            */

            // Dashboard fulfillment counts follow this Seller's persisted Orders.
            Route::get('/dashboard', [OrderController::class, 'dashboard'])
                ->name('dashboard');


            /*
            |--------------------------------------------------------------------------
            | Inventory
            |--------------------------------------------------------------------------
            */

            Route::get('/inventory', [
                InventoryController::class,
                'index',
            ])->name('inventory');

            Route::post('/inventory', [
                InventoryController::class,
                'store',
            ])->name('inventory.products.store');

            Route::patch(
                '/inventory/{product}/stock',
                [InventoryController::class, 'updateStock']
            )->name('inventory.products.stock');

            Route::patch(
                '/inventory/variants/{variant}/stock',
                [InventoryController::class, 'updateVariantStock']
            )->name('inventory.products.variants.stock');

            Route::patch(
                '/inventory/{product}/archive',
                [InventoryController::class, 'archive']
            )->name('inventory.products.archive');


            /*
            |--------------------------------------------------------------------------
            | Vouchers
            |--------------------------------------------------------------------------
            */

            Route::post(
                '/inventory/vouchers',
                [InventoryController::class, 'storeVoucher']
            )->name('inventory.vouchers.store');

            Route::patch(
                '/inventory/vouchers/{voucher}/toggle',
                [InventoryController::class, 'toggleVoucher']
            )->name('inventory.vouchers.toggle');

            Route::delete(
                '/inventory/vouchers/{voucher}',
                [InventoryController::class, 'destroyVoucher']
            )->name('inventory.vouchers.destroy');


            /*
            |--------------------------------------------------------------------------
            | Orders
            |--------------------------------------------------------------------------
            */

            // Seller pages now read seller-scoped Orders; only preparation and readiness accept CSRF-protected mutations.
            Route::get('/orders/notifications', [OrderController::class, 'index'])
                ->name('orders.notifications');
            Route::get('/orders/prepare', [OrderController::class, 'prepare'])
                ->name('orders.prepare');
            Route::get('/orders/courier', [OrderController::class, 'courier'])
                ->name('orders.courier');
            Route::get('/orders/confirm', [OrderController::class, 'confirm'])
                ->name('orders.confirm');
            Route::get('/orders/{order}', [OrderController::class, 'show'])
                ->name('orders.show');
            Route::patch('/orders/{order}/prepare', [OrderController::class, 'startPreparation'])
                ->name('orders.start-preparation');
            Route::patch('/orders/{order}/ready', [OrderController::class, 'markReady'])
                ->name('orders.mark-ready');


            /*
            |--------------------------------------------------------------------------
            | Feedback
            |--------------------------------------------------------------------------
            */

            Route::view('/feedback', 'seller.feedback')
                ->name('feedback');


            /*
            |--------------------------------------------------------------------------
            | Reports
            |--------------------------------------------------------------------------
            */

            Route::view('/reports', 'seller.reports')
                ->name('reports');


            /*
            |--------------------------------------------------------------------------
            | Chat
            |--------------------------------------------------------------------------
            */

            Route::view('/chat', 'seller.chat')
                ->name('chat');


            /*
            |--------------------------------------------------------------------------
            | Account
            |--------------------------------------------------------------------------
            */

            Route::view('/account', 'seller.account')
                ->name('account');


            /*
            |--------------------------------------------------------------------------
            | Storefront Management
            |--------------------------------------------------------------------------
            */

            Route::view('/storefront', 'seller.storefront')
                ->name('storefront');
        });
    });
