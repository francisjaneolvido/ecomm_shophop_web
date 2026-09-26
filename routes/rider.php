<?php

use App\Http\Controllers\Rider\AuthController;
use App\Http\Controllers\Rider\DeliveryController;
use App\Http\Controllers\Rider\CodSettlementController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DeliveryProofController;

// Rider sessions and mutations are separate from approved Logistics operator sessions.
Route::prefix('rider')->name('rider.')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->middleware('throttle:6,1')->name('login.store');
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');

    // Authenticated collectors may return already held cash even after a narrow Rider suspension.
    Route::middleware('auth:rider')->group(function () {
        Route::get('/settlements', [CodSettlementController::class, 'index'])->name('settlements.index');
        Route::post('/settlements/{delivery}/remit', [CodSettlementController::class, 'remit'])->name('settlements.remit');
    });

    Route::middleware('active.rider')->group(function () {
        Route::get('/deliveries', [DeliveryController::class, 'index'])->name('deliveries.index');
        Route::get('/deliveries/{delivery}', [DeliveryController::class, 'show'])->name('deliveries.show');
        Route::post('/deliveries/{delivery}/pickup', [DeliveryController::class, 'pickup'])->name('deliveries.pickup');
        Route::post('/deliveries/{delivery}/transit', [DeliveryController::class, 'transit'])->name('deliveries.transit');
        Route::post('/deliveries/{delivery}/complete', [DeliveryController::class, 'complete'])->name('deliveries.complete');
    });
});

// Proof access performs record-level authorization for Buyer, Seller, Logistics, and the assigned Rider.
Route::get('/delivery-proofs/{delivery}', [DeliveryProofController::class, 'show'])->name('delivery.proof');
