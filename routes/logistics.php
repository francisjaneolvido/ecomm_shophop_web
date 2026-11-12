<?php

use App\Http\Controllers\Branch\RiderBatchController;
use App\Http\Controllers\HybridFinanceController;
use App\Http\Controllers\Logistics\AuthController as LogisticsAuthController;
use App\Http\Controllers\Logistics\BranchStaffController;
use App\Http\Controllers\Logistics\BranchStaffWorkspaceController;
use App\Http\Controllers\Logistics\CodSettlementController;
use App\Http\Controllers\Logistics\DashboardController as LogisticsDashboardController;
use App\Http\Controllers\Logistics\DeliveryController;
use App\Http\Controllers\Logistics\DemoNetworkDirectoryController;
use App\Http\Controllers\Logistics\EarningsController;
use App\Http\Controllers\Logistics\FleetController;
use App\Http\Controllers\Logistics\GoogleAuthController;
use App\Http\Controllers\Logistics\PartnerBrandingController;
use App\Http\Controllers\Logistics\PickupCoverageController;
use App\Http\Controllers\Logistics\RegistrationController as LogisticsRegistrationController;
use App\Http\Controllers\Logistics\ReportController;
use App\Http\Controllers\Logistics\ReturnController;
use App\Http\Controllers\Logistics\RiderBatchMonitoringController;
use App\Http\Controllers\Logistics\RiderController;
use App\Http\Controllers\Logistics\RiderManagementController;
use App\Http\Controllers\Logistics\RouteNetworkController;
use App\Http\Controllers\Logistics\SortingCenterController;
use Illuminate\Support\Facades\Route;

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
            Route::get('/staff/dispatch', [\App\Http\Controllers\Branch\DispatchController::class, 'index'])->name('staff.dispatch.index');
            Route::post('/staff/dispatch/assign', [\App\Http\Controllers\Branch\DispatchController::class, 'assign'])->middleware('throttle:20,1')->name('staff.dispatch.assign');
            Route::post('/staff/dispatch/riders/{rider}/capacity', [RiderBatchController::class, 'configure'])->name('staff.dispatch.capacity');
            Route::post('/staff/dispatch/routes/{batch}/transition', [RiderBatchController::class, 'transition'])->middleware('throttle:20,1')->name('staff.dispatch.transition');
            Route::get('/staff/rider-batches', [RiderBatchController::class, 'index'])->name('staff.rider-batches.index');
            Route::post('/staff/rider-batches', [RiderBatchController::class, 'store'])->middleware('throttle:20,1')->name('staff.rider-batches.store');
            Route::post('/staff/rider-batches/riders/{rider}/capacity', [RiderBatchController::class, 'configure'])->name('staff.rider-batches.capacity');
            Route::post('/staff/rider-batches/{batch}/transition', [RiderBatchController::class, 'transition'])->middleware('throttle:20,1')->name('staff.rider-batches.transition');
            Route::get('/staff/workspace', [BranchStaffWorkspaceController::class, 'index'])->name('staff.workspace');
            Route::post('/staff/password', [BranchStaffWorkspaceController::class, 'changePassword'])
                ->middleware('throttle:6,1')->name('staff.password');
        });

        // Shared role-aware lifecycle console. The controller excludes Province Hubs and scanners.
        foreach ([
            ['middleware' => 'auth', 'url' => '/rider-management', 'name' => 'rider-management.'],
            ['middleware' => 'auth:logistics_staff', 'url' => '/staff/rider-management', 'name' => 'staff.rider-management.'],
        ] as $managementRoute) {
            Route::middleware($managementRoute['middleware'])->prefix($managementRoute['url'])->name($managementRoute['name'])->group(function () {
                Route::get('/', [RiderManagementController::class, 'index'])->name('index');
                Route::post('/riders/{rider}', [RiderManagementController::class, 'action'])->whereNumber('rider')->middleware('throttle:30,1')->name('action');
                Route::get('/riders/{rider}/documents/{slot}', [RiderManagementController::class, 'document'])->whereNumber('rider')->name('document');
                Route::post('/transfers/{transfer}', [RiderManagementController::class, 'transferDecision'])->whereNumber('transfer')->middleware('throttle:20,1')->name('transfer');
            });
        }

        Route::middleware(['auth', 'approved.role:logistics'])->group(function () {
            Route::get('/dashboard', [LogisticsDashboardController::class, 'index'])->name('dashboard');
            Route::get('/partner-profile', [PartnerBrandingController::class, 'edit'])->name('partner-profile.edit');
            Route::post('/partner-profile', [PartnerBrandingController::class, 'update'])->middleware('throttle:8,1')->name('partner-profile.update');

            Route::get('/sorting-centers', [SortingCenterController::class, 'index'])->name('sorting-centers.index');
            // Company-wide directory of city/municipal branch stations.
            Route::get('/branches', [SortingCenterController::class, 'branches'])->name('branches.index');
            Route::post('/branches/{center}/profile', [SortingCenterController::class, 'updateBranchProfile'])->name('branches.profile');
            Route::get('/sorting-centers/archived', [SortingCenterController::class, 'archived'])->name('sorting-centers.archived');
            Route::get('/sorting-centers/create', [SortingCenterController::class, 'create'])->name('sorting-centers.create');
            Route::get('/sorting-centers/{center}', [SortingCenterController::class, 'show'])->name('sorting-centers.show');
            Route::get('/sorting-centers/{center}/edit', [SortingCenterController::class, 'edit'])->name('sorting-centers.edit');
            Route::post('/sorting-centers', [SortingCenterController::class, 'store'])->name('sorting-centers.store');
            Route::post('/sorting-centers/{center}/update', [SortingCenterController::class, 'update'])->name('sorting-centers.update');
            Route::post('/sorting-centers/{center}/profile', [SortingCenterController::class, 'updateHubProfile'])->name('sorting-centers.profile');
            Route::post('/sorting-centers/{center}/toggle', [SortingCenterController::class, 'toggle'])->name('sorting-centers.toggle');
            Route::post('/sorting-centers/{center}/archive', [SortingCenterController::class, 'archive'])->name('sorting-centers.archive');
            Route::post('/sorting-centers/{center}/restore', [SortingCenterController::class, 'restore'])->name('sorting-centers.restore');
            Route::post('/sorting-centers/{center}/staff', [BranchStaffController::class, 'store'])->name('staff.store');
            Route::post('/staff/{staff}/toggle', [BranchStaffController::class, 'toggle'])->name('staff.toggle');
            Route::post('/staff/{staff}/reset-password', [BranchStaffController::class, 'resetPassword'])
                ->middleware('throttle:10,1')->name('staff.reset-password');
            Route::post('/staff/{staff}/set-password', [BranchStaffController::class, 'setPassword'])
                ->middleware('throttle:10,1')->name('staff.set-password');

            Route::get('/route-network', [RouteNetworkController::class, 'index'])->name('route-network.index');
            Route::post('/route-network', [RouteNetworkController::class, 'store'])->name('route-network.store')->middleware('throttle:30,1');
            Route::post('/route-network/{link}/verify', [RouteNetworkController::class, 'verify'])->name('route-network.verify')->middleware('throttle:10,1');
            Route::post('/route-network/{link}/suspend', [RouteNetworkController::class, 'suspend'])->name('route-network.suspend')->middleware('throttle:10,1');
            Route::get('/fleet', [FleetController::class, 'index'])->name('fleet.index');
            Route::post('/fleet/vehicles', [FleetController::class, 'storeVehicle'])->name('fleet.vehicles.store');
            Route::post('/fleet/vehicles/{vehicle}/status', [FleetController::class, 'updateVehicleStatus'])->name('fleet.vehicles.status');
            Route::post('/fleet/vehicles/{vehicle}/update', [FleetController::class, 'updateVehicle'])->name('fleet.vehicles.update');
            Route::post('/fleet/drivers', [FleetController::class, 'storeDriver'])->name('fleet.drivers.store');
            Route::post('/fleet/drivers/{driver}/toggle', [FleetController::class, 'toggleDriver'])->name('fleet.drivers.toggle');
            Route::post('/fleet/drivers/{driver}/update', [FleetController::class, 'updateDriver'])->name('fleet.drivers.update');

            Route::get('/rider-batches', [RiderBatchMonitoringController::class, 'index'])->name('rider-batches.index');
            Route::get('/riders', [RiderController::class, 'index'])->name('riders.index');
            Route::get('/demo-network', [DemoNetworkDirectoryController::class, 'index'])->name('demo-network.index');
            Route::post('/riders', [RiderController::class, 'store'])->name('riders.store');
            Route::post('/riders/{rider}/profile', [RiderController::class, 'updateProfile'])->name('riders.profile');
            Route::post('/riders/{rider}/approve', [RiderController::class, 'approve'])->name('riders.approve');
            Route::post('/riders/{rider}/reject', [RiderController::class, 'reject'])->name('riders.reject');
            Route::get('/riders/{rider}/documents/{slot}', [RiderController::class, 'document'])->name('riders.documents.show');
            Route::post('/riders/{rider}/credentials', [RiderController::class, 'provision'])->name('riders.provision');
            Route::post('/riders/{rider}/area', [RiderController::class, 'updateArea'])->name('riders.area');
            Route::post('/riders/operational-areas', [RiderController::class, 'storeOperationalArea'])->name('riders.operational-areas.store');
            Route::post('/riders/operational-areas/{area}/delete', [RiderController::class, 'removeOperationalArea'])->name('riders.operational-areas.delete');
            Route::post('/riders/{rider}/operational-areas', [RiderController::class, 'syncRiderOperationalAreas'])->name('riders.operational-areas.sync');
            Route::post('/riders/{rider}/suspend', [RiderController::class, 'suspend'])->name('riders.suspend');
            Route::post('/riders/{rider}/activate', [RiderController::class, 'activate'])->name('riders.activate');

            Route::get('/returns', [ReturnController::class, 'index'])->name('returns.index');
            Route::post('/returns/deliveries/{delivery}', [ReturnController::class, 'initiate'])->name('returns.initiate');
            Route::post('/returns/{case}/receive', [ReturnController::class, 'receive'])->name('returns.receive');
            Route::post('/returns/{case}/assign', [ReturnController::class, 'assign'])->name('returns.assign');
            Route::get('/deliveries', [DeliveryController::class, 'board'])->name('deliveries.board');
            Route::post('/deliveries/{order}/assign', [DeliveryController::class, 'assign'])->name('deliveries.assign');
            Route::post('/deliveries/{order}/enable-pickup-coverage', [PickupCoverageController::class, 'enable'])
                ->middleware('throttle:12,1')->name('deliveries.pickup-coverage.enable');
            Route::post('/deliveries/{delivery}/receive', [DeliveryController::class, 'receive'])->name('deliveries.receive');
            Route::post('/deliveries/{delivery}/sort', [DeliveryController::class, 'sort'])->name('deliveries.sort');
            Route::post('/deliveries/{delivery}/assign-delivery', [DeliveryController::class, 'assignDelivery'])->name('deliveries.assign-delivery');
            Route::post('/deliveries/{delivery}/transfer', [DeliveryController::class, 'dispatchTransfer'])->name('deliveries.transfer');
            Route::post('/transfers/{transfer}/receive', [DeliveryController::class, 'receiveTransfer'])->name('transfers.receive');
            Route::get('/earnings', [EarningsController::class, 'index'])->name('earnings');
            Route::post('/earnings/{entry}/rider-paid', [EarningsController::class, 'payRider'])->name('earnings.rider-paid');
            Route::post('/earnings/linehaul/{earning}/paid', [EarningsController::class, 'payLinehaulDriver'])->name('earnings.linehaul-paid');
            Route::get('/settlements', [CodSettlementController::class, 'index'])->name('settlements.index');
            Route::post('/settlements/{delivery}/reconcile', [CodSettlementController::class, 'reconcile'])->name('settlements.reconcile');
            Route::post('/settlements/{delivery}/remit-admin', [CodSettlementController::class, 'remitToAdmin'])->name('settlements.remit-admin');

            Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
            Route::get('/reports/export/pdf', [ReportController::class, 'exportPdf'])->name('reports.export.pdf');
            Route::get('/reports/export/csv', [ReportController::class, 'exportCsv'])->name('reports.export.csv');
            Route::get('/reports/riders/{rider}/export/pdf', [ReportController::class, 'exportRiderPdf'])->name('reports.riders.export.pdf');
        });
    });

// Province Hub portal lives in its own file.
require __DIR__.'/hub.php';
Route::domain(config('app.logistics_domain'))->middleware('auth')->prefix('financial-workspace')->group(function () {
    Route::get('/discrepancies', [\App\Http\Controllers\CodDiscrepancyController::class, 'index'])->name('logistics.hybrid.discrepancies.index');
    Route::post('/discrepancies/{case}/notes', [\App\Http\Controllers\CodDiscrepancyController::class, 'note'])->whereNumber('case')->name('logistics.hybrid.discrepancies.note');
    Route::post('/discrepancies/{case}/proposals', [\App\Http\Controllers\CodDiscrepancyController::class, 'propose'])->whereNumber('case')->name('logistics.hybrid.discrepancies.propose');
    Route::post('/discrepancies/{case}/resolutions/{resolution}/review', [\App\Http\Controllers\CodDiscrepancyController::class, 'review'])->whereNumber('case')->whereNumber('resolution')->name('logistics.hybrid.discrepancies.review');
    Route::post('/discrepancies/{case}/resolutions/{resolution}/return', [\App\Http\Controllers\CodDiscrepancyController::class, 'recordReturn'])->whereNumber('case')->whereNumber('resolution')->name('logistics.hybrid.discrepancies.return');
    Route::post('/policies/{policy}/end', [HybridFinanceController::class, 'closePolicy'])->whereNumber('policy')->name('logistics.hybrid.policy.end');
    Route::post('/legacy-earnings/{earning}/approve', [HybridFinanceController::class, 'approveLegacy'])->whereNumber('earning')->name('logistics.hybrid.legacy.approve');
    Route::post('/expenses', [HybridFinanceController::class, 'expense'])->name('logistics.hybrid.expense');
    Route::get('/', [HybridFinanceController::class, 'index'])->name('logistics.hybrid');
    Route::post('/batches/{batch}/verify', [HybridFinanceController::class, 'verify'])->whereNumber('batch')->name('logistics.hybrid.verify');
    Route::post('/policies', [HybridFinanceController::class, 'policy'])->name('logistics.hybrid.policy');
    Route::post('/payroll', [HybridFinanceController::class, 'payroll'])->name('logistics.hybrid.payroll');
    Route::post('/earnings/{earning}/approve', [HybridFinanceController::class, 'approve'])->whereNumber('earning')->name('logistics.hybrid.approve');
    Route::post('/earnings/{earning}/paid', [HybridFinanceController::class, 'paid'])->whereNumber('earning')->name('logistics.hybrid.paid');
});
