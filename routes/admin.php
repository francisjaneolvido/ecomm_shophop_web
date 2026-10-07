<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\UserAccountController;
use App\Http\Controllers\Admin\RegistrationController as AdminRegistrationController;
use App\Http\Controllers\Admin\AccountManagementController;
use App\Http\Controllers\Admin\ProductComplianceController;
// use App\Http\Controllers\Admin\CommissionController;


/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

Route::prefix('admin')
    ->name('admin.')
    // A route prefix is only a URL namespace; every admin action needs the same session and role boundary.
    ->middleware(['auth', 'approved.role:admin'])
    ->group(function () {

        Route::get('/dashboard', [AdminDashboardController::class, 'index'])
            ->name('dashboard');

        Route::get('/registration', [AdminRegistrationController::class, 'index'])
            ->name('registrations');

        Route::get('/registration/{user}', [AdminRegistrationController::class, 'show'])
            ->name('registrations.show');

        Route::get('/users', [UserAccountController::class, 'index'])
            ->name('users');

        Route::get('/users/{user}', [UserAccountController::class, 'show'])
            ->name('users.show');

        Route::post('/users/{user}/approve', [UserAccountController::class, 'approve'])
            ->name('users.approve');

        Route::post('/users/{user}/reject', [UserAccountController::class, 'reject'])
            ->name('users.reject');

        Route::post('/users/{user}/suspend', [UserAccountController::class, 'suspend'])
            ->name('users.suspend');

        Route::post('/users/{user}/reactivate', [UserAccountController::class, 'reactivate'])
            ->name('users.reactivate');

        // Seller Compliance = admin review of products submitted by verified sellers.
        // (Seller document verification happens on the Account Registrations page.)
        Route::get('/seller-compliance', [ProductComplianceController::class, 'index'])
            ->name('compliance');

        Route::post('/seller-compliance/products/{product}/approve', [ProductComplianceController::class, 'approve'])
            ->name('compliance.products.approve');

        Route::post('/seller-compliance/products/{product}/reject', [ProductComplianceController::class, 'reject'])
            ->name('compliance.products.reject');

        Route::get('/complaints-disputes', function () {
            return view('admin.complaints-disputes');
        })->name('disputes');

        Route::get('/commission', function () {
            return view('admin.commission');
        })->name('commission');

        // Route::get('/commissions/export-pdf', [CommissionController::class, 'exportPdf'])
//     ->name('commissions.export-pdf');

        Route::get('/reports', function () {
            return view('admin.reports');
        })->name('reports');

        Route::get('/chat', function () {
            return view('admin.chat-messaging');
        })->name('chat');

        Route::get('/settings', function () {
            return view('admin.platform-settings');
        })->name('settings');

        Route::get('/accounts', [AccountManagementController::class, 'index'])
            ->name('accounts');

        Route::post('/accounts', [AccountManagementController::class, 'store'])
            ->name('accounts.store');

        Route::patch('/accounts/{admin}/disable', [AccountManagementController::class, 'disable'])
            ->name('accounts.disable');

        Route::patch('/accounts/{admin}/enable', [AccountManagementController::class, 'enable'])
            ->name('accounts.enable');

});