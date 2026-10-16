<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\UserAccountController;
use App\Http\Controllers\Admin\RegistrationController as AdminRegistrationController;
use App\Http\Controllers\Admin\AccountManagementController;
use App\Http\Controllers\Admin\ManualCashlessPaymentReviewController;
use App\Http\Controllers\Admin\MarketplaceFinanceController;
use App\Http\Controllers\Admin\ReturnRefundController;
use App\Http\Controllers\Admin\CodRemittanceController;
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

        // Seller Compliance: review ng products bago makita ng buyers.
        Route::get('/seller-compliance', [ProductComplianceController::class, 'index'])->name('compliance');
        Route::get('/seller-compliance/{product}', [ProductComplianceController::class, 'show'])->name('compliance.show');
        Route::patch('/seller-compliance/{product}/approve', [ProductComplianceController::class, 'approve'])->name('compliance.approve');
        Route::patch('/seller-compliance/{product}/reject', [ProductComplianceController::class, 'reject'])->name('compliance.reject');

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

        Route::get('/complaints-disputes', function () {
            return view('admin.complaints-disputes');
        })->name('disputes');

        Route::get('/cod-remittances', [CodRemittanceController::class, 'index'])->name('finance.cod.index');
        Route::post('/cod-remittances/{settlement}/confirm', [CodRemittanceController::class, 'confirm'])->name('finance.cod.confirm');
        Route::get('/returns-refunds', [ReturnRefundController::class, 'index'])->name('returns.index');
        Route::post('/returns-refunds/{case}/paid', [ReturnRefundController::class, 'paid'])->name('returns.refund-paid');
        Route::get('/commission', [MarketplaceFinanceController::class, 'index'])->name('commission');
        Route::post('/commission/qr', [MarketplaceFinanceController::class, 'paymentSetting'])->name('finance.qr');
        Route::post('/commission/vouchers', [MarketplaceFinanceController::class, 'createVoucher'])->name('finance.vouchers.create');
        Route::post('/commission/vouchers/{voucher}/toggle', [MarketplaceFinanceController::class, 'toggleVoucher'])->name('finance.vouchers.toggle');
        Route::post('/commission/orders/{order}/prepare', [MarketplaceFinanceController::class, 'prepare'])->name('finance.prepare');
        Route::post('/commission/orders/{order}/hold', [MarketplaceFinanceController::class, 'hold'])->name('finance.hold');
        Route::post('/commission/payouts/{entry}/paid', [MarketplaceFinanceController::class, 'paid'])->name('finance.paid');

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