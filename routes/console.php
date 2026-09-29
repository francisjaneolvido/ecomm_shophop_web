<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Models\Buyer\Order\ManualCashlessPayment;
use App\Services\CloseManualCashlessPayment;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Scheduler or operator may run this repeatedly; each due row is rechecked under the payment lock.
Artisan::command('payments:expire', function (CloseManualCashlessPayment $closure) {
    $closed = 0;
    $failed = 0;
    ManualCashlessPayment::whereIn('status', [ManualCashlessPayment::AWAITING_PROOF, ManualCashlessPayment::REJECTED])
        ->whereNotNull('expires_at')->where('expires_at', '<=', now())
        ->orderBy('id')->pluck('id')->each(function ($id) use ($closure, &$closed, &$failed) {
            try {
                if ($closure->close($id, ManualCashlessPayment::EXPIRED, null, 'Payment proof deadline passed')) {
                    $closed++;
                }
            } catch (ValidationException $exception) {
                // An inconsistent group remains untouched and visible for operator investigation.
                $this->error("Payment {$id}: ".$exception->getMessage());
                $failed++;
            }
        });
    $this->info("Expired {$closed} payment(s); {$failed} failed.");
    return $failed ? 1 : 0;
})->purpose('Expire due unresolved online payments and restore their checkout effects');

// The host's normal Laravel scheduler runs the repeatable due-payment command without a Buyer request.
Schedule::command('payments:expire')->everyMinute()->withoutOverlapping();
