<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\UserAccountController;
use App\Http\Controllers\Admin\RegistrationController as AdminRegistrationController;
use App\Http\Controllers\Admin\AccountManagementController;
use App\Http\Controllers\Admin\CommissionController;


/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

Route::prefix('admin')
    ->name('admin.')
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

        Route::get('/seller-compliance', function () {
            return view('admin.seller-compliance');
        })->name('compliance');

        Route::get('/complaints-disputes', function () {
            return view('admin.complaints-disputes');
        })->name('disputes');

        Route::get('/commission', function () {
            return view('admin.commission');
        })->name('commission');

        Route::get('/commissions/export-pdf', [CommissionController::class, 'exportPdf'])
            ->name('commissions.export-pdf');

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

        Route::get('/logout', function () {
            return redirect('/');
        })->name('logout');
    });