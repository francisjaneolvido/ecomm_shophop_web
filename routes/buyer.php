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