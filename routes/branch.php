<?php

// routes/branch.php

use App\Http\Controllers\Branch\DashboardController;
use App\Http\Controllers\Branch\ParcelController;
use App\Http\Controllers\Branch\TransferController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'approved.role:branch'])->prefix('branch')->name('branch.')->group(function () {

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('transfers', [TransferController::class, 'preview'])->name('transfers.index');
    Route::post('receive', [TransferController::class, 'scan'])->name('receive');

    Route::get('parcels', [ParcelController::class, 'index'])->name('parcels.index');
    Route::post('parcels/assign', [ParcelController::class, 'assign'])->name('parcels.assign');
});