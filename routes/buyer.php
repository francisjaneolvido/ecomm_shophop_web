<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Buyer\DashboardController as BuyerDashboardController;
use App\Http\Controllers\Buyer\ShowProductDetails_Controller as BuyerProductController;
use App\Http\Controllers\Buyer\CategoryController as BuyerCategoryController;
use App\Http\Controllers\Buyer\CartController as BuyerCartController;
use App\Http\Controllers\Buyer\CheckoutController as BuyerCheckoutController;
use App\Http\Controllers\Buyer\OrderController as BuyerOrderController;
use App\Http\Controllers\Buyer\BuyerProfileController;


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
        | Product Details
        |--------------------------------------------------------------------------
        */

        // Product detail needs the controller's persisted Product and review data; a bare view route cannot supply them.
        Route::get('/product/{product}', [
            BuyerProductController::class,
            'show',
        ])->name('product.show');


        /*
        |--------------------------------------------------------------------------
        | Cart
        |--------------------------------------------------------------------------
        | Wired to CartController so the buyer's cart_items (add/update/remove)
        | persist in the database and render on /buyer/cart.
        */

        Route::get('/cart', [
            BuyerCartController::class,
            'index',
        ])->name('cart');

        Route::post('/cart/add', [
            BuyerCartController::class,
            'add',
        ])->name('cart.add');

        Route::patch('/cart/{lineKey}', [
            BuyerCartController::class,
            'update',
        ])->name('cart.update');

        Route::delete('/cart/{lineKey}', [
            BuyerCartController::class,
            'remove',
        ])->name('cart.remove');

        Route::post('/cart/remove-many', [
            BuyerCartController::class,
            'removeMany',
        ])->name('cart.removeMany');

        Route::get('/cart/count', [
            BuyerCartController::class,
            'count',
        ])->name('cart.count');


        /*
        |--------------------------------------------------------------------------
        | Checkout
        |--------------------------------------------------------------------------
        | Wired to CheckoutController so the page reads real cart_items,
        | buyer address, and seller-scoped vouchers from the database.
        */

        Route::get('/cart/checkout', [
            BuyerCheckoutController::class,
            'index',
        ])->name('cart.checkout');


        /*
        |--------------------------------------------------------------------------
        | Place Order
        |--------------------------------------------------------------------------
        */

        Route::post('/checkout/place-order', [
            BuyerCheckoutController::class,
            'placeOrder',
        ])->name('checkout.place');


        /*
        |--------------------------------------------------------------------------
        | My Orders
        |--------------------------------------------------------------------------
        | Wired to OrderController so the page reads real Order/OrderItem rows
        | for the logged-in buyer instead of hardcoded demo data.
        */

        Route::get('/orders', [
            BuyerOrderController::class,
            'index',
        ])->name('orders');


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

        Route::patch('/settings/profile', [
            BuyerProfileController::class,
            'update',
        ])->name('settings.profile.update');


        /*
        |--------------------------------------------------------------------------
        | Future Buyer Settings
        |--------------------------------------------------------------------------
        */

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