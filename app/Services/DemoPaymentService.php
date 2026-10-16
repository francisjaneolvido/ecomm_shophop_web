<?php
namespace App\Services;

use App\Models\Buyer\Order\ManualCashlessPayment;
use App\Models\Finance\DemoTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class DemoPaymentService
{
    /** Generates an on-platform practice transaction; DOES NOT transfer any real funds. */
    public function simulate(int $paymentId, int $buyerId): DemoTransaction
    {
        return DB::transaction(function () use ($paymentId, $buyerId) {
            $payment = ManualCashlessPayment::whereKey($paymentId)->where('buyer_id', $buyerId)
                ->lockForUpdate()->firstOrFail();
            if (! in_array($payment->status, [ManualCashlessPayment::AWAITING_PROOF,
                ManualCashlessPayment::REJECTED], true) || ! $payment->expires_at?->isFuture()) {
                throw ValidationException::withMessages(['payment' => 'Demo payment is no longer open.']);
            }
            $existing = DemoTransaction::where('manual_cashless_payment_id', $payment->id)->first();
            if ($existing) return $existing;
            return DemoTransaction::create([
                'manual_cashless_payment_id' => $payment->id,
                'reference' => 'DEMO-'.Str::upper(Str::random(16)),
                'amount' => $payment->expectedAmount(),
                'simulated_at' => now(),
            ]);
        });
    }

    public function validatedForReview(ManualCashlessPayment $payment): bool
    {
        $demo = DemoTransaction::where('manual_cashless_payment_id', $payment->id)->first();
        return $demo && hash_equals($demo->reference, (string) $payment->normalized_reference)
            && abs((float) $demo->amount - (float) $payment->expectedAmount()) < 0.001;
    }
}
