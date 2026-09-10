<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Buyer\DashboardController as BuyerDashboardController;
use App\Http\Controllers\Buyer\ShowProductDetails_Controller as BuyerProductController;


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
        | Ito ang actual product details route.
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
        | dito muna. Wala nang direct/"quick" checkout path.
        |
        | Flow:
        | 1. Product page → "Add to Cart" o "Buy Now" → parehong
        |    nagta-trigger ng add-to-cart, tapos redirect papunta dito.
        | 2. Kapag galing sa "Buy Now", yung kakabili lang na item ang
        |    naka-preselect (checked) sa cart, para hindi na kailangan
        |    pang i-check pa ulit ng buyer.
        | 3. Buyer pipili (checkbox) kung anong item(s) talaga ang
        |    ic-checkout, tapos pindutin ang "Proceed to Checkout".
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
        | Checkout — CART (single entry point na ngayon)
        |--------------------------------------------------------------------------
        |
        | Ito na ang TANGING checkout page, dating tinatawag na
        | "cart/multi-seller checkout" lang. Ngayon, kahit isang item
        | lang (galing Buy Now), dito pa rin dumadaan — basta't
        | naka-group-by-shop ang layout nito.
        |
        | Binabasa nito (sa production) ang mga naka-select (checked)
        | na item mula sa cart page, karaniwan bilang array ng
        | cart item IDs (hal. ?items[]=1&items[]=3), bago i-render.
        |
        | NOTE: Yung dati nating place-order.blade.php (single-item
        | "Buy Now direct checkout" page) ay hindi na ginagamit sa
        | flow na ito. Panatilihin na lang ito sa codebase bilang
        | reference/backup, pero wala nang route na tumuturo dito.
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
        | Checkout — Place Order (form submission)
        |--------------------------------------------------------------------------
        |
        | TEMPORARY closure lang muna. Ito yung tatamaan ng submit
        | button sa cart-checkout.blade.php (parehong Buy Now at
        | normal na cart checkout, dahil iisa na lang ang checkout
        | page).
        |
        | I-palitan natin ‘to ng tunay na controller
        | (app/Http/Controllers/Buyer/CheckoutController.php)
        | kapag ready na ang order/payment logic.
        |
        */

        Route::post('/checkout/place-order', function () {
            return back()->with('status', 'Order placed (demo — no backend yet).');
        })->name('checkout.place');


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