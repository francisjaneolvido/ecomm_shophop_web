<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Logistics\AuthController as LogisticsAuthController;
use App\Http\Controllers\Logistics\RegistrationController as LogisticsRegistrationController;
use App\Http\Controllers\Logistics\DashboardController as LogisticsDashboardController;
use App\Http\Controllers\Logistics\RiderController;
use App\Http\Controllers\Logistics\DeliveryController;
use App\Http\Controllers\Logistics\ReportController;
use App\Http\Controllers\Logistics\CodSettlementController;
use App\Http\Controllers\Logistics\SortingCenterController;


/*
|--------------------------------------------------------------------------
| Logistics Routes
|--------------------------------------------------------------------------
|
| Public routes for logistics partner registration (main site).
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

        Route::post('/verification/send', [
            LogisticsRegistrationController::class,
            'sendVerificationCode'
        ])->middleware('throttle:8,1')->name('verification.send');

        Route::post('/verification/verify', [
            LogisticsRegistrationController::class,
            'verifyVerificationCode'
        ])->middleware('throttle:20,1')->name('verification.verify');

        Route::get('/terms', [
            LogisticsRegistrationController::class,
            'terms'
        ])->name('terms');
    });


/*
|--------------------------------------------------------------------------
| Logistics Subdomain: Sign In / Sign Out
|--------------------------------------------------------------------------
|
| Nasa subdomain mismo ang login para dito mase-save ang session cookie.
|
*/

Route::domain(config('app.logistics_domain'))
    ->name('logistics.')
    ->group(function () {

        Route::get('/sign-in', [LogisticsAuthController::class, 'showLogin'])
            ->name('login');

        Route::post('/sign-in', [LogisticsAuthController::class, 'login'])
            ->middleware('throttle:10,1')
            ->name('login.submit');

        Route::post('/sign-out', [LogisticsAuthController::class, 'logout'])
            ->name('logout');
    });


/*
|--------------------------------------------------------------------------
| Logistics Partner Console (Subdomain)
|--------------------------------------------------------------------------
|
| Console routes require the same approved role boundary as the other
| operational portals; applicant entry remains in the public group above.
|
*/

Route::domain(config('app.logistics_domain'))
    ->name('logistics.')
    ->middleware([
        'auth',
        'approved.role:logistics',
    ])
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
        | Sorting Center Network
        |--------------------------------------------------------------------------
        */

        Route::get('/sorting-centers', [SortingCenterController::class, 'index'])
            ->name('sorting-centers.index');
        Route::post('/sorting-centers', [SortingCenterController::class, 'store'])
            ->name('sorting-centers.store');
        Route::post('/sorting-centers/{center}/update', [SortingCenterController::class, 'update'])
            ->name('sorting-centers.update');
        Route::post('/sorting-centers/{center}/toggle', [SortingCenterController::class, 'toggle'])
            ->name('sorting-centers.toggle');


        /*
        |--------------------------------------------------------------------------
        | Riders
        |--------------------------------------------------------------------------
        */

        Route::get('/riders', [
            RiderController::class,
            'index'
        ])->name('riders.index');

        // Operators create real Rider records; preview approval and warning endpoints have no application contract.
        Route::post('/riders', [RiderController::class, 'store'])->name('riders.store');

        // Self-registered Riders remain pending until this owning Logistics / Sorting Center reviews them.
        Route::post('/riders/{rider}/approve', [RiderController::class, 'approve'])->name('riders.approve');
        Route::post('/riders/{rider}/reject', [RiderController::class, 'reject'])->name('riders.reject');
        Route::get('/riders/{rider}/documents/{slot}', [RiderController::class, 'document'])->name('riders.documents.show');

        // Legacy records require explicit provisioning by their owning partner before Rider login.
        Route::post('/riders/{rider}/credentials', [RiderController::class, 'provision'])->name('riders.provision');
        Route::post('/riders/{rider}/area', [RiderController::class, 'updateArea'])->name('riders.area');

        Route::post('/riders/{rider}/suspend', [
            RiderController::class,
            'suspend'
        ])->name('riders.suspend');

        Route::post('/riders/{rider}/activate', [
            RiderController::class,
            'activate'
        ])->name('riders.activate');


        /*
        |--------------------------------------------------------------------------
        | Deliveries
        |--------------------------------------------------------------------------
        */

        Route::get('/deliveries', [
            DeliveryController::class,
            'board'
        ])->name('deliveries.board');

        // Every transition is a server-owned POST for one Seller Order, never a DOM-only board move.
        Route::post('/deliveries/{order}/assign', [DeliveryController::class, 'assign'])->name('deliveries.assign');
        Route::post('/deliveries/{delivery}/receive', [DeliveryController::class, 'receive'])->name('deliveries.receive');
        Route::post('/deliveries/{delivery}/sort', [DeliveryController::class, 'sort'])->name('deliveries.sort');
        Route::post('/deliveries/{delivery}/assign-delivery', [DeliveryController::class, 'assignDelivery'])->name('deliveries.assign-delivery');
        Route::post('/deliveries/{delivery}/transfer', [DeliveryController::class, 'dispatchTransfer'])->name('deliveries.transfer');
        Route::post('/transfers/{transfer}/receive', [DeliveryController::class, 'receiveTransfer'])->name('transfers.receive');
        // Rider routes own pickup acceptance, collection, final dispatch, proof, and failure reporting.

        // Only the owning approved partner confirms receipt of recorded Rider-remitted COD cash.
        Route::get('/settlements', [CodSettlementController::class, 'index'])->name('settlements.index');
        Route::post('/settlements/{delivery}/reconcile', [CodSettlementController::class, 'reconcile'])->name('settlements.reconcile');


        /*
        |--------------------------------------------------------------------------
        | Reports
        |--------------------------------------------------------------------------
        */

        Route::get('/reports', [
            ReportController::class,
            'index'
        ])->name('reports.index');

        Route::get('/reports/export/pdf', [
            ReportController::class,
            'exportPdf'
        ])->name('reports.export.pdf');

        Route::get('/reports/riders/{rider}/export/pdf', [
            ReportController::class,
            'exportRiderPdf'
        ])->name('reports.riders.export.pdf');
    });