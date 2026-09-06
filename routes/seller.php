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

        Route::view('/dashboard', 'seller.dashboard')
            ->name('dashboard');

        Route::get('/inventory', [InventoryController::class, 'index'])
            ->name('inventory');

        Route::post('/inventory', [InventoryController::class, 'store'])
            ->name('inventory.store');

        Route::post('/inventory/{product}/archive', [InventoryController::class, 'archive'])
            ->name('inventory.archive');

        Route::view('/orders/notifications', 'seller.orders.notifications')
            ->name('orders.notifications');

        Route::view('/orders/prepare', 'seller.orders.prepare')
            ->name('orders.prepare');

        Route::view('/orders/courier', 'seller.orders.courier')
            ->name('orders.courier');

        Route::view('/orders/confirm', 'seller.orders.confirm')
            ->name('orders.confirm');

        Route::view('/feedback', 'seller.feedback')
            ->name('feedback');

        Route::view('/reports', 'seller.reports')
            ->name('reports');

        Route::view('/chat', 'seller.chat')
            ->name('chat');

        Route::view('/account', 'seller.account')
            ->name('account');

        Route::view('/storefront', 'seller.storefront')
            ->name('storefront');

        Route::get('/logout', function () {
            return redirect()->route('seller.dashboard');
        })->name('logout');
    });