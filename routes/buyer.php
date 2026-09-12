<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Buyer\DashboardController as BuyerDashboardController;
use App\Http\Controllers\Buyer\ShowProductDetails_Controller as BuyerProductController;
use App\Http\Controllers\Buyer\CategoryController as BuyerCategoryController;


/*
|--------------------------------------------------------------------------
| Buyer Routes
|--------------------------------------------------------------------------
|
| Requirements:
| - logged in
| - buyer account
| - email verified
| - administrator approved
|
*/

Route::prefix('buyer')
    ->name('buyer.')
    ->middleware([
        'auth',
        'approved.role:buyer',
    ])
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Buyer Dashboard
        |--------------------------------------------------------------------------
        */

        Route::get('/dashboard', [
            BuyerDashboardController::class,
            'index',
        ])->name('dashboard');


        /*
        |--------------------------------------------------------------------------
        | Categories
        |--------------------------------------------------------------------------
        */

        Route::get('/categories', [
            BuyerCategoryController::class,
            'index',
        ])->name('category.index');

        Route::get('/category/{category}', [
            BuyerCategoryController::class,
            'show',
        ])->name('category.show');


        /*
        |--------------------------------------------------------------------------
        | Product Preview
        |--------------------------------------------------------------------------
        */

        Route::get('/product-preview', function () {
            return view('buyer.product.show-product-details');
        })->name('product.preview');


        /*
        |--------------------------------------------------------------------------
        | Product Details
        |--------------------------------------------------------------------------
        */

        Route::get('/product/{product}', [
            BuyerProductController::class,
            'show',
        ])->name('product.show');


        /*
        |--------------------------------------------------------------------------
        | Cart
        |--------------------------------------------------------------------------
        */

        Route::get('/cart', function () {
            return view('buyer.cart.cart');
        })->name('cart');


        /*
        |--------------------------------------------------------------------------
        | Checkout
        |--------------------------------------------------------------------------
        */

        Route::get('/cart/checkout', function () {
            return view('buyer.checkout.cart-checkout');
        })->name('cart.checkout');


        /*
        |--------------------------------------------------------------------------
        | Place Order
        |--------------------------------------------------------------------------
        */

        Route::post('/checkout/place-order', function () {
            return back()->with(
                'status',
                'Order placed (demo — no backend yet).'
            );
        })->name('checkout.place');


        /*
        |--------------------------------------------------------------------------
        | My Orders
        |--------------------------------------------------------------------------
        */

        Route::get('/orders', function () {
            return view('buyer.orders.index');
        })->name('orders');


        /*
        |--------------------------------------------------------------------------
        | Messages
        |--------------------------------------------------------------------------
        */

        Route::get('/messages', function () {
            return view('buyer.messages.index');
        })->name('messages');


        /*
        |--------------------------------------------------------------------------
        | Buyer Profile
        |--------------------------------------------------------------------------
        */

        Route::get('/profile', function () {
            return view('buyer.buyer-profile-settings');
        })->name('profile');


        /*
        |--------------------------------------------------------------------------
        | Future Buyer Settings
        |--------------------------------------------------------------------------
        */

        // Route::patch('/settings/profile', [
        //     BuyerProfileController::class,
        //     'update'
        // ])->name('settings.profile.update');

        // Route::patch('/settings/address', [
        //     BuyerProfileController::class,
        //     'updateAddress'
        // ])->name('settings.address.update');

        // Route::patch('/settings/allergens', [
        //     BuyerProfileController::class,
        //     'updateAllergens'
        // ])->name('settings.allergens.update');

        // Route::patch('/settings/password', [
        //     BuyerProfileController::class,
        //     'updatePassword'
        // ])->name('settings.password.update');

        // Route::patch('/settings/notifications', [
        //     BuyerProfileController::class,
        //     'updateNotifications'
        // ])->name('settings.notifications.update');
    });