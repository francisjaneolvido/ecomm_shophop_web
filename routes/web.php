<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\HomeController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;

use App\Http\Controllers\Auth\BuyerRegistrationController;
use App\Http\Controllers\Auth\SellerRegistrationController;
use App\Http\Controllers\Auth\LoginController;

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\UserAccountController;
use App\Http\Controllers\Admin\RegistrationController as AdminRegistrationController;
use App\Http\Controllers\Admin\AccountManagementController;

use App\Http\Controllers\Buyer\DashboardController as BuyerDashboardController;

use App\Http\Controllers\Logistics\RegistrationController as LogisticsRegistrationController;
use App\Http\Controllers\Logistics\DashboardController as LogisticsDashboardController;
use App\Http\Controllers\Logistics\RiderController;
use App\Http\Controllers\Logistics\DeliveryController;
use App\Http\Controllers\Logistics\ReportController;


/*
|--------------------------------------------------------------------------
| ShopHop Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])
    ->name('home');


/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

Route::get('/register', function () {
    return view('auth.modals.account-type-modal');
})->name('register');

Route::post('/register', [BuyerRegistrationController::class, 'store'])
    ->name('register.store');

Route::post('/seller/register', [SellerRegistrationController::class, 'store'])
    ->name('seller.register.store');


Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::post('/login', [LoginController::class, 'store'])
    ->name('login.store');

Route::post('/logout', [LoginController::class, 'destroy'])
    ->name('logout');


Route::get('/create-account', function () {
    return view('auth.create-account');
})->name('create-account');


/*
|--------------------------------------------------------------------------
| Future Shop Routes
|--------------------------------------------------------------------------
*/

// Route::get('/categories', [CategoryController::class, 'index'])
//     ->name('categories.index');

// Route::get('/categories/{category}', [CategoryController::class, 'show'])
//     ->name('categories.show');

// Route::get('/products/{product}', [ProductController::class, 'show'])
//     ->name('products.show');

// Route::get('/deals', [HomeController::class, 'deals'])
//     ->name('deals');


/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])
    ->name('admin.dashboard');


Route::get('/admin/registration', [AdminRegistrationController::class, 'index'])
    ->name('admin.registrations');


Route::get('/admin/users', [UserAccountController::class, 'index'])
    ->name('admin.users');

Route::get('/admin/users/{user}', [UserAccountController::class, 'show'])
    ->name('admin.users.show');

Route::post('/admin/users/{user}/approve', [UserAccountController::class, 'approve'])
    ->name('admin.users.approve');

Route::post('/admin/users/{user}/reject', [UserAccountController::class, 'reject'])
    ->name('admin.users.reject');

Route::post('/admin/users/{user}/suspend', [UserAccountController::class, 'suspend'])
    ->name('admin.users.suspend');

Route::post('/admin/users/{user}/reactivate', [UserAccountController::class, 'reactivate'])
    ->name('admin.users.reactivate');


Route::get('/admin/seller-compliance', function () {
    return view('admin.seller-compliance');
})->name('admin.compliance');


Route::get('/admin/complaints-disputes', function () {
    return view('admin.complaints-disputes');
})->name('admin.disputes');


Route::get('/admin/commission', function () {
    return view('admin.commission');
})->name('admin.commission');


Route::get('/admin/reports', function () {
    return view('admin.reports');
})->name('admin.reports');


Route::get('/admin/chat', function () {
    return view('admin.chat-messaging');
})->name('admin.chat');


Route::get('/admin/settings', function () {
    return view('admin.platform-settings');
})->name('admin.settings');


Route::get('/admin/accounts', [AccountManagementController::class, 'index'])
    ->name('admin.accounts');

Route::post('/admin/accounts', [AccountManagementController::class, 'store'])
    ->name('admin.accounts.store');

Route::patch('/admin/accounts/{admin}/disable', [AccountManagementController::class, 'disable'])
    ->name('admin.accounts.disable');

Route::patch('/admin/accounts/{admin}/enable', [AccountManagementController::class, 'enable'])
    ->name('admin.accounts.enable');


Route::get('/admin/logout', function () {
    return redirect('/');
})->name('admin.logout');

Route::get('commissions/export-pdf', [CommissionController::class, 'exportPdf'])
    ->name('admin.commissions.export-pdf');

/*
|--------------------------------------------------------------------------
| Logistics Routes
|--------------------------------------------------------------------------
|
| Public routes for logistics partner registration.
|
*/

Route::prefix('logistics-partner')
    ->name('logistics.')
    ->group(function () {

        Route::get('/apply', [
            LogisticsRegistrationController::class,
            'create'
        ])->name('register');

        Route::post('/apply', [
            LogisticsRegistrationController::class,
            'store'
        ])->name('register.store');

        Route::get('/terms', [
            LogisticsRegistrationController::class,
            'terms'
        ])->name('terms');
    });


/*
|--------------------------------------------------------------------------
| Logistics Partner Console
|--------------------------------------------------------------------------
|
| Auth temporarily disabled.
| Ibalik ang middleware kapag ready na ang login/account system.
|
*/

Route::prefix('logistics-partner')
    ->name('logistics.')
    // ->middleware(['auth'])
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Dashboard
        |--------------------------------------------------------------------------
        */

        Route::get('/dashboard', [
            LogisticsDashboardController::class,
            'index'
        ])->name('dashboard');


        /*
        |--------------------------------------------------------------------------
        | Riders
        |--------------------------------------------------------------------------
        */

        Route::get('/riders', [
            RiderController::class,
            'index'
        ])->name('riders.index');

        Route::post('/riders/{rider}/approve', [
            RiderController::class,
            'approve'
        ])->name('riders.approve');

        Route::post('/riders/{rider}/disapprove', [
            RiderController::class,
            'disapprove'
        ])->name('riders.disapprove');

        Route::post('/riders/{rider}/suspend', [
            RiderController::class,
            'suspend'
        ])->name('riders.suspend');

        Route::post('/riders/{rider}/activate', [
            RiderController::class,
            'activate'
        ])->name('riders.activate');

        Route::post('/riders/{rider}/warn', [
            RiderController::class,
            'warn'
        ])->name('riders.warn');


        /*
        |--------------------------------------------------------------------------
        | Deliveries
        |--------------------------------------------------------------------------
        */

        Route::get('/deliveries', [
            DeliveryController::class,
            'board'
        ])->name('deliveries.board');

        Route::post('/deliveries/{delivery}/assign', [
            DeliveryController::class,
            'assign'
        ])->name('deliveries.assign');


        /*
        |--------------------------------------------------------------------------
        | Reports
        |--------------------------------------------------------------------------
        */

        Route::get('/reports', [
            ReportController::class,
            'index'
        ])->name('reports.index');

        Route::get('/reports/export', [
            ReportController::class,
            'export'
        ])->name('reports.export');
    });


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


/*
|--------------------------------------------------------------------------
| TEMP DEBUG ROUTES — Modal Isolation Test
|--------------------------------------------------------------------------
|
| Tanggalin kapag nahanap na yung looping modal.
|
*/

Route::get('/debug/modal/login', function () {
    return view('auth.modals.login-modal');
});


Route::get('/debug/modal/account-type', function () {
    return view('auth.modals.account-type-modal');
});


Route::get('/debug/modal/buyer-registration', function () {
    return view('auth.modals.buyer-registration-modal');
});


Route::get('/debug/modal/seller-registration', function () {
    return view('auth.modals.seller-registration-modal');
});


Route::get('/debug/modal/logistics-registration', function () {
    return view('auth.modals.logistics-registration-modal');
});


Route::view(
    '/seller-ui',
    'seller.dashboard.dashboard'
)->name('seller.dashboard');

/*
|--------------------------------------------------------------------------
| LOADING
|--------------------------------------------------------------------------
*/
Route::get('/dev/loading-preview', function () {
    return view('partials.loading-screen');
});