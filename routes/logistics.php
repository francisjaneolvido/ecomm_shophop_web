<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Logistics\ReturnController;

use App\Http\Controllers\Logistics\AuthController as LogisticsAuthController;
use App\Http\Controllers\Logistics\GoogleAuthController;
use App\Http\Controllers\Logistics\RegistrationController as LogisticsRegistrationController;
use App\Http\Controllers\Logistics\DashboardController as LogisticsDashboardController;
use App\Http\Controllers\Logistics\RiderController;
use App\Http\Controllers\Logistics\DeliveryController;
use App\Http\Controllers\Logistics\ReportController;
use App\Http\Controllers\Logistics\CodSettlementController;
use App\Http\Controllers\Logistics\EarningsController;
use App\Http\Controllers\Logistics\SortingCenterController;
use App\Http\Controllers\Logistics\BranchStaffController;
use App\Http\Controllers\Logistics\BranchStaffWorkspaceController;

/*
| All Logistics public pages and private console live under LOGISTICS_DOMAIN.
| Main marketplace routes are host-scoped in bootstrap/app.php.
| Keep route names unchanged to preserve existing controller/view redirects.
*/
Route::domain(config('app.logistics_domain'))
    ->name('logistics.')
    ->group(function () {
        Route::view('/', 'logistics.landing.index')->name('home');

        Route::get('/apply', [LogisticsRegistrationController::class, 'create'])->name('register');
        Route::post('/apply', [LogisticsRegistrationController::class, 'store'])->name('register.store');
        Route::post('/verification/send', [LogisticsRegistrationController::class, 'sendVerificationCode'])
            ->middleware('throttle:8,1')->name('verification.send');
        Route::post('/verification/verify', [LogisticsRegistrationController::class, 'verifyVerificationCode'])
            ->middleware('throttle:20,1')->name('verification.verify');
        Route::get('/terms', [LogisticsRegistrationController::class, 'terms'])->name('terms');

        Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])
            ->middleware('throttle:10,1')->name('google.redirect');
        Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])
            ->middleware('throttle:20,1')->name('google.callback');

        Route::get('/sign-in', [LogisticsAuthController::class, 'showLogin'])->name('login');
        Route::post('/sign-in', [LogisticsAuthController::class, 'login'])
            ->middleware('throttle:10,1')->name('login.submit');
        Route::post('/sign-out', [LogisticsAuthController::class, 'logout'])->name('logout');

        // Branch operators use a separate session guard: no access to company management routes.
        Route::middleware('auth:logistics_staff')->group(function () {
            Route::get('/staff/workspace', [BranchStaffWorkspaceController::class, 'index'])->name('staff.workspace');
            Route::post('/staff/password', [BranchStaffWorkspaceController::class, 'changePassword'])
                ->middleware('throttle:6,1')->name('staff.password');
        });

        Route::middleware(['auth', 'approved.role:logistics'])->group(function () {
            Route::get('/dashboard', [LogisticsDashboardController::class, 'index'])->name('dashboard');

            Route::get('/sorting-centers', [SortingCenterController::class, 'index'])->name('sorting-centers.index');
            Route::get('/sorting-centers/archived', [SortingCenterController::class, 'archived'])->name('sorting-centers.archived');
            Route::get('/sorting-centers/create', [SortingCenterController::class, 'create'])->name('sorting-centers.create');
            Route::get('/sorting-centers/{center}', [SortingCenterController::class, 'show'])->name('sorting-centers.show');
            Route::get('/sorting-centers/{center}/edit', [SortingCenterController::class, 'edit'])->name('sorting-centers.edit');
            Route::post('/sorting-centers', [SortingCenterController::class, 'store'])->name('sorting-centers.store');
            Route::post('/sorting-centers/{center}/update', [SortingCenterController::class, 'update'])->name('sorting-centers.update');
            Route::post('/sorting-centers/{center}/toggle', [SortingCenterController::class, 'toggle'])->name('sorting-centers.toggle');
            Route::post('/sorting-centers/{center}/archive', [SortingCenterController::class, 'archive'])->name('sorting-centers.archive');
            Route::post('/sorting-centers/{center}/restore', [SortingCenterController::class, 'restore'])->name('sorting-centers.restore');
            Route::post('/sorting-centers/{center}/staff', [BranchStaffController::class, 'store'])->name('staff.store');
            Route::post('/staff/{staff}/toggle', [BranchStaffController::class, 'toggle'])->name('staff.toggle');
            Route::post('/staff/{staff}/reset-password', [BranchStaffController::class, 'resetPassword'])
                ->middleware('throttle:10,1')->name('staff.reset-password');
            Route::post('/staff/{staff}/set-password', [BranchStaffController::class, 'setPassword'])
                ->middleware('throttle:10,1')->name('staff.set-password');

            Route::get('/riders', [RiderController::class, 'index'])->name('riders.index');
            Route::post('/riders', [RiderController::class, 'store'])->name('riders.store');
            Route::post('/riders/{rider}/approve', [RiderController::class, 'approve'])->name('riders.approve');
            Route::post('/riders/{rider}/reject', [RiderController::class, 'reject'])->name('riders.reject');
            Route::get('/riders/{rider}/documents/{slot}', [RiderController::class, 'document'])->name('riders.documents.show');
            Route::post('/riders/{rider}/credentials', [RiderController::class, 'provision'])->name('riders.provision');
            Route::post('/riders/{rider}/area', [RiderController::class, 'updateArea'])->name('riders.area');
            Route::post('/riders/{rider}/suspend', [RiderController::class, 'suspend'])->name('riders.suspend');
            Route::post('/riders/{rider}/activate', [RiderController::class, 'activate'])->name('riders.activate');

            Route::get('/returns', [ReturnController::class, 'index'])->name('returns.index');
            Route::post('/returns/deliveries/{delivery}', [ReturnController::class, 'initiate'])->name('returns.initiate');
            Route::post('/returns/{case}/receive', [ReturnController::class, 'receive'])->name('returns.receive');
            Route::post('/returns/{case}/assign', [ReturnController::class, 'assign'])->name('returns.assign');
            Route::get('/deliveries', [DeliveryController::class, 'board'])->name('deliveries.board');
            Route::post('/deliveries/{order}/assign', [DeliveryController::class, 'assign'])->name('deliveries.assign');
            Route::post('/deliveries/{delivery}/receive', [DeliveryController::class, 'receive'])->name('deliveries.receive');
            Route::post('/deliveries/{delivery}/sort', [DeliveryController::class, 'sort'])->name('deliveries.sort');
            Route::post('/deliveries/{delivery}/assign-delivery', [DeliveryController::class, 'assignDelivery'])->name('deliveries.assign-delivery');
            Route::post('/deliveries/{delivery}/transfer', [DeliveryController::class, 'dispatchTransfer'])->name('deliveries.transfer');
            Route::post('/transfers/{transfer}/receive', [DeliveryController::class, 'receiveTransfer'])->name('transfers.receive');
            Route::get('/earnings', [EarningsController::class, 'index'])->name('earnings');
            Route::post('/earnings/{entry}/rider-paid', [EarningsController::class, 'payRider'])->name('earnings.rider-paid');
            Route::get('/settlements', [CodSettlementController::class, 'index'])->name('settlements.index');
            Route::post('/settlements/{delivery}/reconcile', [CodSettlementController::class, 'reconcile'])->name('settlements.reconcile');
            Route::post('/settlements/{delivery}/remit-admin', [CodSettlementController::class, 'remitToAdmin'])->name('settlements.remit-admin');

            Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
            Route::get('/reports/export/pdf', [ReportController::class, 'exportPdf'])->name('reports.export.pdf');
            Route::get('/reports/riders/{rider}/export/pdf', [ReportController::class, 'exportRiderPdf'])->name('reports.riders.export.pdf');
        });
    });

// Province Hub portal lives in its own file.
require __DIR__ . '/hub.php';