<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\UserAccountController;
use App\Http\Controllers\Admin\RegistrationController as AdminRegistrationController;
use App\Http\Controllers\Admin\AccountManagementController;
use App\Http\Controllers\Admin\ManualCashlessPaymentReviewController;
// Product review uses the same current Admin identity and middleware boundary as payment review.
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

        // Compliance reads and mutations require an authenticated approved Admin, never a route prefix alone.
        Route::get('/product-compliance', [ProductComplianceController::class, 'index'])->name('product-compliance.index');
        Route::get('/product-compliance/{product}', [ProductComplianceController::class, 'show'])->name('product-compliance.show');
        Route::patch('/product-compliance/{product}/approve', [ProductComplianceController::class, 'approve'])->name('product-compliance.approve');
        Route::patch('/product-compliance/{product}/reject', [ProductComplianceController::class, 'reject'])->name('product-compliance.reject');

        // Cashless proof decisions have their own Admin queue and locked group review.
        Route::get('/payments', [ManualCashlessPaymentReviewController::class, 'index'])->name('payments.index');
        Route::get('/payments/{payment}', [ManualCashlessPaymentReviewController::class, 'show'])->name('payments.show');
        Route::post('/payments/{payment}/verify', [ManualCashlessPaymentReviewController::class, 'verify'])->name('payments.verify');
        Route::post('/payments/{payment}/reject', [ManualCashlessPaymentReviewController::class, 'reject'])->name('payments.reject');
        // Cancellation restores Checkout effects and is distinct from correctable proof rejection.
        Route::post('/payments/{payment}/cancel', [ManualCashlessPaymentReviewController::class, 'cancel'])->name('payments.cancel');

        Route::get('/registration', [AdminRegistrationController::class, 'index'])
            ->name('registrations');

        Route::get('/registration/{user}', [AdminRegistrationController::class, 'show'])
            ->name('registrations.show');

        // Sensitive files reuse the approved Admin boundary and accept a record/slot, never a filesystem path.
        Route::get('/registration/{user}/documents/{document}', [AdminRegistrationController::class, 'document'])
            ->name('registrations.documents.show');

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
