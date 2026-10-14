<?php

// routes/branch.php

use App\Http\Controllers\Branch\ParcelController;
use App\Http\Controllers\Branch\TransferController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Branch Routes
|--------------------------------------------------------------------------
| URL: logistics.<domain>/branch/...   Route names: branch.*
| Naka-register sa logistics domain (tingnan ang bootstrap/app.php).
| Branch accounts lang ang pasok (approved.role:branch).
| Hardcoded pa ang dashboard. Palitan ng DashboardController kapag may data na.
*/

Route::middleware(['auth', 'approved.role:branch'])->prefix('branch')->name('branch.')->group(function () {

    // Dashboard (hardcoded muna)
    Route::get('/', function () {
        return view('branch.dashboard.index', [
            'stats' => [
                'incoming' => 2, 'at_branch' => 34, 'to_assign' => 12, 'out_for_delivery' => 21,
                'delivered_today' => 41, 'failed' => 3, 'cod_with_riders' => 6240.00,
            ],
            'incoming' => [
                ['id' => 1, 'code' => 'TR-0012', 'parcels' => 20, 'from' => 'Main hub', 'sent_at' => '2 hrs ago'],
                ['id' => 2, 'code' => 'TR-0013', 'parcels' => 8, 'from' => 'Main hub', 'sent_at' => '30 mins ago'],
            ],
            'toAssign' => [
                ['tracking' => 'SH-100234', 'receiver' => 'Juan Dela Cruz', 'barangay' => 'Poblacion', 'cod' => 450],
                ['tracking' => 'SH-100235', 'receiver' => 'Maria Santos', 'barangay' => 'San Pablo Norte', 'cod' => null],
            ],
            'riders' => [
                ['name' => 'Pedro Santos', 'status' => 'available', 'active' => 5, 'delivered' => 8],
                ['name' => 'Ana Reyes', 'status' => 'busy', 'active' => 12, 'delivered' => 3],
            ],
            'alerts' => [
                ['level' => 'warning', 'title' => '3 parcels failed delivery', 'detail' => 'Kailangan ng retry o return.'],
            ],
        ]);
    })->name('dashboard');

    // Incoming transfers
    // Gumagamit pa ng preview() na meron ka na. Kapag nagawa na ang incoming(),
    // palitan ang 'preview' ng 'incoming' at i-uncomment ang receive route.
    Route::get('transfers', [TransferController::class, 'preview'])->name('transfers.index');
    // Route::post('transfers/{transfer}/receive', [TransferController::class, 'receive'])->name('transfers.receive');

    // Parcels (hardcoded view muna)
    Route::view('parcels', 'branch.parcels.index')->name('parcels.index');
    // Kapag nagawa na ang ParcelController, palitan ang linya sa taas ng:
    // Route::get('parcels', [ParcelController::class, 'index'])->name('parcels.index');
    // Route::post('parcels/assign', [ParcelController::class, 'assign'])->name('parcels.assign');
});