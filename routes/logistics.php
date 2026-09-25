<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Logistics\RegistrationController as LogisticsRegistrationController;
use App\Http\Controllers\Logistics\DashboardController as LogisticsDashboardController;
use App\Http\Controllers\Logistics\RiderController;
use App\Http\Controllers\Logistics\DeliveryController;
use App\Http\Controllers\Logistics\ReportController;


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
| Console routes require the same approved role boundary as the other
| operational portals; applicant entry remains in the public group above.
|
*/

Route::prefix('logistics-partner')
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
        | Riders
        |--------------------------------------------------------------------------
        */

        Route::get('/riders', [
            RiderController::class,
            'index'
        ])->name('riders.index');

        // Operators create real Rider records; preview approval and warning endpoints have no application contract.
        Route::post('/riders', [RiderController::class, 'store'])->name('riders.store');

        // Legacy records require explicit provisioning by their owning partner before Rider login.
        Route::post('/riders/{rider}/credentials', [RiderController::class, 'provision'])->name('riders.provision');

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
        // After assignment, only authenticated Rider routes own pickup, transit, and evidenced completion.


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
