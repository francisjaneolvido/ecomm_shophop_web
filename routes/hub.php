<?php

// routes/hub.php  —  Province Hub portal

use App\Http\Controllers\hub\HubPortalController;
use App\Http\Middleware\EnsureHubManager;
use Illuminate\Support\Facades\Route;

/*
| Province hub portal. URL: <LOGISTICS_DOMAIN>/hub/...   Route names: logistics.hub.*
| Branch-staff guard (logistics_staff) + manager role only.
| Naka-require sa dulo ng routes/logistics.php, kaya pareho ang web middleware at domain.
*/
Route::domain(config('app.logistics_domain'))
    ->name('logistics.')
    ->group(function () {

        Route::middleware(['auth:logistics_staff', EnsureHubManager::class])
            ->prefix('hub')->name('hub.')->group(function () {

                // Overview
                Route::view('/', 'hub.dashboard.dashboard')->name('dashboard');

                // Network: station hubs (province manager creates the station accounts)
                Route::get('/station-hubs', [HubPortalController::class, 'stationHubs'])->name('station-hubs');
                Route::post('/station-hubs', [HubPortalController::class, 'storeStationHub'])->name('station-hubs.store');
                Route::post('/station-hubs/{stationHub}/password', [HubPortalController::class, 'setStationPassword'])
                    ->middleware('throttle:10,1')->name('station-hubs.set-password');
                Route::post('/station-hubs/{stationHub}/toggle', [HubPortalController::class, 'toggleStationHub'])->name('station-hubs.toggle');

                // Operations
                Route::view('/parcels', 'hub.parcel.parcels')->name('parcels');
            });
    });