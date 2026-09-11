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
| Buyer-specific pages.
|
| Auth muna disabled habang tine-test ang dashboard.
| Kapag ready na ang login system, pwede nating idagdag:
|
| ->middleware(['auth'])
|
*/

Route::prefix('buyer')
    ->name('buyer.')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Buyer Dashboard
        |--------------------------------------------------------------------------
        */

        Route::get('/dashboard', [
            BuyerDashboardController::class,
            'index'
        ])->name('dashboard');


        /*
        |--------------------------------------------------------------------------
        | Categories — Browse page (sidebar + filters + product grid)
        |--------------------------------------------------------------------------
        |
        | "All Categories" index (used by the breadcrumb's first link),
        | then the actual category browse page.
        |
        | Example:
        | /buyer/category/electronics-and-gadgets
        |
        | Route names:
        | buyer.category.index
        | buyer.category.show
        |
        */

        Route::get('/categories', [
            BuyerCategoryController::class,
            'index'
        ])->name('category.index');

        Route::get('/category/{category}', [
            BuyerCategoryController::class,
            'show'
        ])->name('category.show');


        /*
        |--------------------------------------------------------------------------
        | Product Details — TEMPORARY UI PREVIEW
        |--------------------------------------------------------------------------
        |
        | Temporary route ito para makita muna ang hardcoded
        | Show Product UI kahit walang existing product sa database.
        |
        | URL:
        | http://127.0.0.1:8000/buyer/product-preview
        |
        */

        Route::get('/product-preview', function () {
            return view('buyer.product.show-product-details');
        })->name('product.preview');


        /*
        |--------------------------------------------------------------------------
        | Product Details — Actual Product Route
        |--------------------------------------------------------------------------
        |
        | Example:
        | /buyer/product/1
        |
        | Route name:
        | buyer.product.show
        |
        */

        Route::get('/product/{product}', [
            BuyerProductController::class,
            'show'
        ])->name('product.show');


        /*
        |--------------------------------------------------------------------------
        | Cart
        |--------------------------------------------------------------------------
        |
        | LAHAT ng pagbili — Buy Now man o Add to Cart — ay dumadaan
        | dito muna.
        |
        | URL:
        | http://127.0.0.1:8000/buyer/cart
        |
        */

        Route::get('/cart', function () {
            return view('buyer.cart.cart');
        })->name('cart');


        /*
        |--------------------------------------------------------------------------
        | Checkout — CART
        |--------------------------------------------------------------------------
        |
        | Single checkout entry point for selected cart items.
        |
        | URL:
        | http://127.0.0.1:8000/buyer/cart/checkout
        |
        */

        Route::get('/cart/checkout', function () {
            return view('buyer.checkout.cart-checkout');
        })->name('cart.checkout');


        /*
        |--------------------------------------------------------------------------
        | Checkout — Place Order
        |--------------------------------------------------------------------------
        |
        | TEMPORARY closure lang muna.
        | Palitan ng CheckoutController kapag ready na ang backend.
        |
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
        |
        | Buyer order history / order tracking page.
        |
        | URL:
        | http://127.0.0.1:8000/buyer/orders
        |
        | Route name:
        | buyer.orders
        |
        */

        Route::get('/orders', function () {
            return view('buyer.orders.index');
        })->name('orders');


        /*
        |--------------------------------------------------------------------------
        | Order Reports — FUTURE BACKEND
        |--------------------------------------------------------------------------
        |
        | Hindi pa kailangan sa current frontend preview.
        | Kapag may real order_reports table/controller na, pwede itong
        | palitan ng BuyerOrderReportController.
        |
        */

        // Route::post('/orders/{order}/report', function ($order) {
        //     return back()->with(
        //         'status',
        //         'Report submitted (demo — no backend yet).'
        //     );
        // })->name('orders.report');


        /*
        |--------------------------------------------------------------------------
        | Messages
        |--------------------------------------------------------------------------
        |
        | Buyer conversations with sellers and ShopHop Support.
        |
        | URL:
        | http://127.0.0.1:8000/buyer/messages
        |
        | Route name:
        | buyer.messages
        |
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
        | Buyer Settings
        |--------------------------------------------------------------------------
        |
        | TEMPORARILY COMMENTED OUT.
        |
        | I-enable natin ito kapag nagawa na ang:
        |
        | app/Http/Controllers/Buyer/ProfileController.php
        |
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
