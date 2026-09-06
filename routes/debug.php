<?php

use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| TEMP DEBUG / DEV ROUTES
|--------------------------------------------------------------------------
|
| Tanggalin kapag nahanap na yung looping modal at hindi na kailangan
| ang mga preview routes na ito.
|
*/

Route::prefix('debug')
    ->group(function () {

        Route::get('/modal/login', function () {
            return view('auth.modals.login-modal');
        });

        Route::get('/modal/account-type', function () {
            return view('auth.modals.account-type-modal');
        });

        Route::get('/modal/buyer-registration', function () {
            return view('auth.modals.buyer-registration-modal');
        });

        Route::get('/modal/seller-registration', function () {
            return view('auth.modals.seller-registration-modal');
        });

        Route::get('/modal/logistics-registration', function () {
            return view('auth.modals.logistics-registration-modal');
        });
    });

Route::prefix('dev')
    ->group(function () {

        Route::get('/loading-preview', function () {
            return view('partials.loading-screen');
        });
    });