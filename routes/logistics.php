<?php

use App\Http\Controllers\Logistics\DashboardController;
use App\Http\Controllers\Logistics\DeliveryController;
use App\Http\Controllers\Logistics\RegistrationController;
use App\Http\Controllers\Logistics\ReportController;
use App\Http\Controllers\Logistics\RiderController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Logistics Partner Routes
|--------------------------------------------------------------------------
|
| Drop this file in routes/logistics.php, then require it from
| routes/web.php:
|
|     require __DIR__.'/logistics.php';
|
| Or paste the two groups below directly into web.php.
|
*/

// Public — anyone can apply to become a Logistics Partner.
Route::prefix('logistics-partner')->name('logistics.')->group(function () {
    Route::get('/apply', [RegistrationController::class, 'create'])->name('register');
    Route::post('/apply', [RegistrationController::class, 'store'])->name('register.store');
});

// Authenticated partner console.
// Swap 'auth' for your logistics-partner guard/middleware once it exists
// (e.g. 'auth:logistics_partner') so buyers/sellers can't land here.
Route::prefix('logistics-partner')
    ->name('logistics.')
    ->middleware(['auth'])
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('/riders', [RiderController::class, 'index'])->name('riders.index');
        Route::post('/riders/{rider}/approve', [RiderController::class, 'approve'])->name('riders.approve');
        Route::post('/riders/{rider}/disapprove', [RiderController::class, 'disapprove'])->name('riders.disapprove');
        Route::post('/riders/{rider}/suspend', [RiderController::class, 'suspend'])->name('riders.suspend');
        Route::post('/riders/{rider}/activate', [RiderController::class, 'activate'])->name('riders.activate');

        Route::get('/deliveries', [DeliveryController::class, 'board'])->name('deliveries.board');
        Route::post('/deliveries/{delivery}/assign', [DeliveryController::class, 'assign'])->name('deliveries.assign');

        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');
    });
