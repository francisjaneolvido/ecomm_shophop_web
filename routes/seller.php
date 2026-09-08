<?php

use Illuminate\Support\Facades\Route;
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

        Route::get('/inventory', [InventoryController::class, 'index'])
            ->name('inventory');

        Route::post('/inventory', [InventoryController::class, 'store'])
            ->name('inventory.store');

        Route::post(
            '/inventory/{product}/stock',
            [InventoryController::class, 'updateStock']
        )->name('inventory.stock');

        Route::post(
            '/inventory/variants/{variant}/stock',
            [InventoryController::class, 'updateVariantStock']
        )->name('inventory.variant.stock');

        Route::post(
            '/inventory/{product}/archive',
            [InventoryController::class, 'archive']
        )->name('inventory.archive');


        /*
        |--------------------------------------------------------------------------
        | Vouchers
        |--------------------------------------------------------------------------
        */

        Route::post(
            '/inventory/vouchers',
            [InventoryController::class, 'storeVoucher']
        )->name('inventory.vouchers.store');

        Route::post(
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
        | Storefront
        |--------------------------------------------------------------------------
        */

        Route::view('/storefront', 'seller.storefront')
            ->name('storefront');


        /*
        |--------------------------------------------------------------------------
        | Logout
        |--------------------------------------------------------------------------
        */

        Route::get('/logout', function () {
            return redirect()->route('seller.dashboard');
        })->name('logout');

    });