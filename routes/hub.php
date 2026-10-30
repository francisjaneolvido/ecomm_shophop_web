<?php

// routes/hub.php  —  Province Hub portal

use App\Http\Controllers\hub\HubPortalController;
use App\Http\Middleware\EnsureHubManager;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MobileLogisticsScannerController;
use App\Http\Controllers\Logistics\WebScannerController;

/*
| Province hub portal. URL: <LOGISTICS_DOMAIN>/hub/...   Route names: logistics.hub.*
| Branch-staff guard (logistics_staff) + manager role only.
*/
Route::domain(config('app.logistics_domain'))
    ->name('logistics.')
    ->group(function () {

        Route::middleware(['auth:logistics_staff', EnsureHubManager::class])
            ->prefix('hub')->name('hub.')->group(function () {

                // Role-locked Web scanner using existing Mobile scanner policies.
                Route::get('/scanner', [WebScannerController::class, 'hub'])->name('scanner');
                Route::prefix('scanner/api')->name('scanner.api.')->group(function () {
                    Route::get('branches', [MobileLogisticsScannerController::class, 'branches'])->name('branches');
                    Route::get('inbound-preview', [MobileLogisticsScannerController::class, 'inboundManifestPreview'])->name('inbound-preview');
                    Route::post('arrival', [MobileLogisticsScannerController::class, 'acknowledgeManifestArrival'])->middleware('throttle:20,1')->name('arrival');
                    Route::post('receive-all', [MobileLogisticsScannerController::class, 'receiveManifestAll'])->middleware('throttle:20,1')->name('receive-all');
                    Route::get('outbound-manifests', [MobileLogisticsScannerController::class, 'manifests'])->name('outbound-manifests');
                    Route::get('outbound-preview', [MobileLogisticsScannerController::class, 'loadPreview'])->name('outbound-preview');
                    Route::post('outbound-load', [MobileLogisticsScannerController::class, 'loadParcel'])->middleware('throttle:30,1')->name('outbound-load');
                    Route::get('parcel-preview', [MobileLogisticsScannerController::class, 'preview'])->name('parcel-preview');
                    Route::post('parcel-receive', [MobileLogisticsScannerController::class, 'receive'])->middleware('throttle:30,1')->name('parcel-receive');
                });

                // Overview
                Route::get('/', [HubPortalController::class, 'dashboard'])->name('dashboard');

                // Network: station hubs
                Route::get('/station-hubs', [HubPortalController::class, 'stationHubs'])->name('station-hubs');
                Route::post('/station-hubs', [HubPortalController::class, 'storeStationHub'])->name('station-hubs.store');
                Route::post('/station-hubs/{stationHub}/password', [HubPortalController::class, 'setStationPassword'])
                    ->middleware('throttle:10,1')->name('station-hubs.set-password');
                Route::post('/station-hubs/{stationHub}/toggle', [HubPortalController::class, 'toggleStationHub'])->name('station-hubs.toggle');

                // Operations: receive -> sort -> outbound manifest -> dispatch
                Route::get('/parcels', [HubPortalController::class, 'parcels'])->name('parcels');
                Route::post('/parcels/receive', [HubPortalController::class, 'receiveParcel'])->name('parcels.receive');
                Route::post('/parcels/sort', [HubPortalController::class, 'sortParcels'])->name('parcels.sort');
                Route::post('/parcels/manifests', [HubPortalController::class, 'storeManifest'])->name('parcels.manifests.store');
                Route::post('/parcels/manifests/{manifest}/load', [HubPortalController::class, 'loadManifest'])->whereNumber('manifest')->name('parcels.manifests.load');
                Route::post('/parcels/manifests/{manifest}/assign-linehaul', [HubPortalController::class, 'assignLinehaul'])->whereNumber('manifest')->name('parcels.manifests.assign-linehaul');
                Route::post('/parcels/manifests/{manifest}/dispatch', [HubPortalController::class, 'dispatchManifest'])
                    ->whereNumber('manifest')->name('parcels.manifests.dispatch');
                Route::post('/parcels/manifests/{manifest}/cancel', [HubPortalController::class, 'cancelManifest'])
                    ->whereNumber('manifest')->name('parcels.manifests.cancel');
                Route::get('/parcels/manifests/{manifest}/print', [HubPortalController::class, 'printManifest'])
                    ->whereNumber('manifest')->name('parcels.manifests.print');
            });
    });