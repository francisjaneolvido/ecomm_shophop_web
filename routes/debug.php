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

// TEST previews render real Blade surfaces but must never become a production authentication bypass.
if (app()->environment('local')) {
    Route::prefix('__dev')
        ->name('dev.')
        ->group(function () {
            Route::view('/accounts', 'dev.accounts')->name('accounts');

            Route::prefix('preview')->name('preview.')->group(function () {
                // TEST routing: use real Blade surfaces with local representative defaults.
                Route::view('/buyer/categories', 'buyer.category.index')->name('buyer.categories');
                Route::view('/buyer/cart', 'buyer.cart.cart')->name('buyer.cart');
                Route::view('/buyer/checkout', 'buyer.checkout.cart-checkout')->name('buyer.checkout');
                Route::view('/buyer/orders', 'buyer.orders.index')->name('buyer.orders');
                Route::view('/buyer/messages', 'buyer.messages.index')->name('buyer.messages');
                Route::view('/buyer/profile', 'buyer.buyer-profile-settings')->name('buyer.profile');
                Route::view('/buyer/storefront', 'buyer.store.show')->name('buyer.storefront');

                Route::view('/seller/dashboard', 'seller.dashboard')->name('seller.dashboard');
                Route::view('/seller/inventory', 'seller.inventory')->name('seller.inventory');
                Route::view('/seller/orders', 'seller.orders.notifications')->name('seller.orders');
                Route::view('/seller/feedback', 'seller.feedback')->name('seller.feedback');
                Route::view('/seller/reports', 'seller.reports')->name('seller.reports');
                Route::view('/seller/chat', 'seller.chat')->name('seller.chat');
                Route::view('/seller/account', 'seller.account')->name('seller.account');
                Route::view('/seller/storefront', 'seller.storefront')->name('seller.storefront');

                Route::view('/admin/dashboard', 'admin.dashboard')->name('admin.dashboard');
                Route::view('/admin/registrations', 'admin.registration')->name('admin.registrations');
                Route::view('/admin/users', 'admin.user-accounts')->name('admin.users');
                Route::view('/admin/compliance', 'admin.seller-compliance')->name('admin.compliance');
                Route::view('/admin/disputes', 'admin.complaints-disputes')->name('admin.disputes');
                Route::view('/admin/commission', 'admin.commission')->name('admin.commission');
                Route::view('/admin/reports', 'admin.reports')->name('admin.reports');
                Route::view('/admin/chat', 'admin.chat-messaging')->name('admin.chat');
                Route::view('/admin/settings', 'admin.platform-settings')->name('admin.settings');
                Route::view('/admin/accounts', 'admin.account-management')->name('admin.accounts');

                Route::view('/logistics/dashboard', 'logistics.dashboard')->name('logistics.dashboard');
                Route::view('/logistics/riders', 'logistics.riders')->name('logistics.riders');
                Route::view('/logistics/deliveries', 'logistics.delivery-board')->name('logistics.deliveries');
                Route::view('/logistics/reports', 'logistics.reports')->name('logistics.reports');
            });
        });
}
