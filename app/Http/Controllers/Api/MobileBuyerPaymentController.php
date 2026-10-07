<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Buyer\Order\ManualCashlessPayment;
use App\Services\CloseManualCashlessPayment;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

class MobileBuyerPaymentController extends Controller
{
    public function show(Request $request, int $payment): JsonResponse
    {
        $owned = $this->owned($request, $payment);
        $orders = $owned->orders()
            ->where('buyer_id', $owned->buyer_id)
            ->where('checkout_group_id', $owned->checkout_group_id)
            ->with('seller')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Payment loaded.',
            'data' => [
                'id' => (int) $owned->id,
                'status' => $owned->status,
                'reference' => $owned->reference,
                'expected_amount' => (float) $owned->expectedAmount(),
                'expires_at' => $owned->expires_at?->toIso8601String(),
                'submitted_at' => $owned->submitted_at?->toIso8601String(),
                'reviewed_at' => $owned->reviewed_at?->toIso8601String(),
                'rejection_reason' => $owned->rejection_reason,
                'closure_reason' => $owned->closure_reason,
                'can_submit' => in_array($owned->status, [
                    ManualCashlessPayment::AWAITING_PROOF,
                    ManualCashlessPayment::REJECTED,
                ], true) && $owned->expires_at?->isFuture(),
                'can_cancel' => in_array($owned->status, [
                    ManualCashlessPayment::AWAITING_PROOF,
                    ManualCashlessPayment::PENDING_REVIEW,
                    ManualCashlessPayment::REJECTED,
                ], true),
                'orders' => $orders->map(fn ($order) => [
                    'id' => (int) $order->id,
                    'seller_name' => $order->seller?->business_name ?? 'ShopHop Seller',
                    'total_amount' => (float) $order->total_amount,
                    'status' => $order->status,
                    'status_label' => $order->statusLabel(),
                ])->values()->all(),
            ],
        ]);
    }

    public function submit(Request $request, int $payment): JsonResponse
    {
        $this->owned($request, $payment);

        if (is_string($request->input('reference'))) {
            $request->merge(['reference' => trim($request->input('reference'))]);
        }

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

                if (! in_array($owned->status, [
                    ManualCashlessPayment::AWAITING_PROOF,
                    ManualCashlessPayment::REJECTED,
                ], true)
                    || ! $owned->expires_at
                    || ! $owned->expires_at->isFuture()) {
                    throw ValidationException::withMessages([
                        'payment' => 'This payment is no longer open for submission.',
                    ]);
                }

                if (ManualCashlessPayment::where('normalized_reference', $normalized)
                    ->whereKeyNot($owned->id)
                    ->exists()) {
                    throw ValidationException::withMessages([
                        'reference' => 'This payment reference is already used.',
                    ]);
                }

                $newPath = $request->file('receipt')->store('payment-receipts', 'local');
                if (! $newPath) {
                    throw ValidationException::withMessages([
                        'receipt' => 'Receipt could not be stored.',
                    ]);
                }

                $oldPath = $owned->receipt_path;
                $owned->update([
                    'reference' => $data['reference'],
                    'normalized_reference' => $normalized,
                    'receipt_path' => $newPath,
                    'submitted_at' => now(),
                    'status' => ManualCashlessPayment::PENDING_REVIEW,
                    'reviewer_user_id' => null,
                    'decision' => null,
                    'reviewed_at' => null,
                    'rejection_reason' => null,
                    'expires_at' => null,
                ]);
            });
        } catch (Throwable $exception) {
            if ($newPath) {
                Storage::disk('local')->delete($newPath);
            }

            if ($exception instanceof QueryException) {
                throw ValidationException::withMessages([
                    'reference' => 'This payment reference is already used.',
                ]);
            }

            throw $exception;
        }

        if ($oldPath) {
            Storage::disk('local')->delete($oldPath);
        }

        return response()->json([
            'success' => true,
            'message' => 'Payment proof submitted for ShopHop Admin review.',
            'data' => [
                'payment_id' => $payment,
                'status' => ManualCashlessPayment::PENDING_REVIEW,
            ],
        ]);
    }

    public function cancel(
        Request $request,
        int $payment,
        CloseManualCashlessPayment $closure,
    ): JsonResponse {
        $this->owned($request, $payment);
        $closure->close(
            $payment,
            ManualCashlessPayment::CANCELLED,
            $request->user()->id,
            'Cancelled by Buyer',
        );

        return response()->json([
            'success' => true,
            'message' => 'All Orders in this checkout group were cancelled.',
            'data' => [
                'payment_id' => $payment,
                'status' => ManualCashlessPayment::CANCELLED,
            ],
        ]);
    }

    private function owned(Request $request, int $payment, bool $lock = false): ManualCashlessPayment
    {
        $user = $request->user();

        abort_unless($user, 401, 'Unauthenticated.');
        abort_unless($user->account_type === 'buyer', 403, 'Buyer account required.');
        abort_unless($user->email_verified_at, 403, 'Email verification required.');
        abort_unless($user->status === 'approved', 403, 'Approved buyer account required.');
        abort_unless($user->buyer, 403, 'Buyer profile not found.');

        $query = ManualCashlessPayment::whereKey($payment)
            ->where('buyer_id', $user->buyer->id);

        return ($lock ? $query->lockForUpdate() : $query)->firstOrFail();
    }
}
