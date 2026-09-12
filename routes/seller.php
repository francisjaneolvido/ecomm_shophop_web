<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\SellerEmailVerificationController;
use App\Http\Controllers\Seller\InventoryController;

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

            Route::view('/dashboard', 'seller.dashboard')
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

            Route::view(
                '/orders/notifications',
                'seller.orders.notifications'
            )->name('orders.notifications');

            Route::view(
                '/orders/prepare',
                'seller.orders.prepare'
            )->name('orders.prepare');

            Route::view(
                '/orders/courier',
                'seller.orders.courier'
            )->name('orders.courier');

            Route::view(
                '/orders/confirm',
                'seller.orders.confirm'
            )->name('orders.confirm');


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