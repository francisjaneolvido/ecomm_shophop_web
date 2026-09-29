<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Buyer\Order\ManualCashlessPayment;
use App\Services\CloseManualCashlessPayment;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class ManualCashlessPaymentController extends Controller
{
    public function show(Request $request, int $payment): View
    {
        // A group UUID or payment ID alone never grants another Buyer access to receipt or Orders.
        $owned = $this->owned($request, $payment);
        // Explicitly linked Orders still require the owning Buyer and matching checkout group.
        $owned->setRelation('orders', $owned->orders()->where('buyer_id', $owned->buyer_id)
            ->where('checkout_group_id', $owned->checkout_group_id)
            ->with('seller')->get());
        return view('buyer.payments.show', ['payment' => $owned]);
    }

    public function submit(Request $request, int $payment): RedirectResponse
    {
        $this->owned($request, $payment);
        if (is_string($request->input('reference'))) {
            $request->merge(['reference' => trim($request->input('reference'))]);
        }
        // The receipt is a private image; a reference alone cannot enter Admin review.
        $data = $request->validate([
            'reference' => ['required', 'string', 'max:120'],
            'receipt' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);
        $normalized = mb_strtoupper(preg_replace('/\s+/', '', $data['reference']));
        $newPath = null;
        $oldPath = null;

        try {
            DB::transaction(function () use ($request, $payment, $data, $normalized, &$newPath, &$oldPath) {
                $owned = $this->owned($request, $payment, true);
                // Verified and pending submissions are immutable; a rejected payment may correct its own proof.
                if (! in_array($owned->status, [ManualCashlessPayment::AWAITING_PROOF, ManualCashlessPayment::REJECTED], true)
                    || ! $owned->expires_at || ! $owned->expires_at->isFuture()) {
                    throw ValidationException::withMessages(['payment' => 'This payment is no longer open for submission.']);
                }
                if (ManualCashlessPayment::where('normalized_reference', $normalized)
                    ->whereKeyNot($owned->id)->exists()) {
                    throw ValidationException::withMessages(['reference' => 'This payment reference is already used.']);
                }
                $newPath = $request->file('receipt')->store('payment-receipts', 'local');
                if (! $newPath) {
                    throw ValidationException::withMessages(['receipt' => 'Receipt could not be stored.']);
                }
                $oldPath = $owned->receipt_path;
                // A corrected proof starts a new current review; stale rejection metadata must disappear.
                $owned->update([
                    'reference' => $data['reference'], 'normalized_reference' => $normalized,
                    'receipt_path' => $newPath, 'submitted_at' => now(),
                    'status' => ManualCashlessPayment::PENDING_REVIEW,
                    'reviewer_user_id' => null, 'decision' => null,
                    // Submitted proof pauses the deadline until Admin decides it.
                    'reviewed_at' => null, 'rejection_reason' => null, 'expires_at' => null,
                ]);
            });
        } catch (Throwable $exception) {
            // A failed database write must not leave an unowned private upload behind.
            if ($newPath) {
                Storage::disk('local')->delete($newPath);
            }
            if ($exception instanceof QueryException) {
                throw ValidationException::withMessages(['reference' => 'This payment reference is already used.']);
            }
            throw $exception;
        }

        // A corrected receipt replaces only this payment's previous private file after commit.
        if ($oldPath) {
            Storage::disk('local')->delete($oldPath);
        }
        return redirect()->route('buyer.payments.show', $payment)
            ->with('status', 'Payment proof submitted for ShopHop Admin review.');
    }

    public function cancel(Request $request, int $payment, CloseManualCashlessPayment $closure): RedirectResponse
    {
        // Ownership is checked before the service locks and revalidates the whole checkout group.
        $this->owned($request, $payment);
        $closure->close($payment, ManualCashlessPayment::CANCELLED, $request->user()->id, 'Cancelled by Buyer');

        return redirect()->route('buyer.payments.show', $payment)
            ->with('status', 'All Orders in this checkout group were cancelled.');
    }

    private function owned(Request $request, int $payment, bool $lock = false): ManualCashlessPayment
    {
        $query = ManualCashlessPayment::whereKey($payment)->where('buyer_id', $request->user()->buyer?->id);
        return ($lock ? $query->lockForUpdate() : $query)->firstOrFail();
    }
}
