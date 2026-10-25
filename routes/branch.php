<?php

// routes/branch.php

use App\Http\Controllers\Branch\DashboardController;
use App\Http\Controllers\Branch\DispatchController;
use App\Http\Controllers\Branch\OutboundController;
use App\Http\Controllers\Branch\ParcelController;
use App\Http\Controllers\Branch\TransferController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'approved.role:branch'])->prefix('branch')->name('branch.')->group(function () {

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('transfers', [TransferController::class, 'index'])->name('transfers.index');
    Route::post('receive', [TransferController::class, 'scan'])->name('receive');

    Route::get('parcels', [ParcelController::class, 'index'])->name('parcels.index');
    Route::post('parcels/assign', [ParcelController::class, 'assign'])->name('parcels.assign');

    // Dispatch: pickup rider at delivery rider
    Route::get('dispatch', [DispatchController::class, 'index'])->name('dispatch.index');
    Route::post('dispatch/pickups/{pickup}/assign', [DispatchController::class, 'assignPickup'])->name('dispatch.pickups.assign');
    Route::post('dispatch/deliveries/{delivery}/assign', [DispatchController::class, 'assignDelivery'])->name('dispatch.deliveries.assign');

    // Outbound to Province Hub: sort, manifest, dispatch, print
    Route::get('outbound', [OutboundController::class, 'index'])->name('outbound.index');
    Route::post('outbound/sort', [OutboundController::class, 'sort'])->name('outbound.sort');
    Route::post('outbound/manifests', [OutboundController::class, 'store'])->name('outbound.store');
    Route::post('outbound/manifests/{manifest}/load', [OutboundController::class, 'loadManifest'])->name('outbound.load');
    Route::post('outbound/manifests/{manifest}/assign-linehaul', [OutboundController::class, 'assignLinehaul'])->name('outbound.assign-linehaul');
    Route::post('outbound/manifests/{manifest}/dispatch', [OutboundController::class, 'dispatchManifest'])->name('outbound.dispatch');
    Route::post('outbound/manifests/{manifest}/cancel', [OutboundController::class, 'cancelManifest'])->name('outbound.cancel');
    Route::get('outbound/manifests/{manifest}/print', [OutboundController::class, 'printManifest'])->name('outbound.print');
});