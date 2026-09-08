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

        Route::get('/reports/export/pdf', [
            ReportController::class,
            'exportPdf'
        ])->name('reports.export.pdf');

        Route::get('/reports/riders/{rider}/export/pdf', [
            ReportController::class,
            'exportRiderPdf'
        ])->name('reports.riders.export.pdf');
    });