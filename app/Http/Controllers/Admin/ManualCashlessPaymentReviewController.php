<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Buyer\Order\ManualCashlessPayment;
use App\Models\Buyer\Order\Order;
use App\Models\Logistics\Delivery;
use App\Services\CloseManualCashlessPayment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ManualCashlessPaymentReviewController extends Controller
{
    public function index(): View
    {
        // Payment review is a separate Admin queue; it does not moderate accounts or Products.
        $payments = ManualCashlessPayment::with('buyer')->where('status', ManualCashlessPayment::PENDING_REVIEW)
            ->orderBy('submitted_at')->get();
        return view('admin.payments.index', compact('payments'));
    }

    public function show(int $payment): View
    {
        $record = ManualCashlessPayment::with(['buyer', 'reviewer'])
            ->findOrFail($payment);
        // Detail lists only explicit membership with matching Buyer/group invariants.
        $record->setRelation('orders', $record->orders()->where('buyer_id', $record->buyer_id)
            ->where('checkout_group_id', $record->checkout_group_id)
            ->with('seller')->get());
        return view('admin.payments.show', ['payment' => $record]);
    }

    public function verify(Request $request, int $payment): RedirectResponse
    {
        return $this->decide($request, $payment, ManualCashlessPayment::VERIFIED, null);
    }

    public function reject(Request $request, int $payment): RedirectResponse
    {
        // Rejection corrects proof only; it never cancels Orders or restores Checkout inventory.
        if (is_string($request->input('reason'))) {
            $request->merge(['reason' => trim($request->input('reason'))]);
        }
        $data = $request->validate(['reason' => ['required', 'string', 'max:1000']]);
        return $this->decide($request, $payment, ManualCashlessPayment::REJECTED, $data['reason']);
    }

    public function cancel(Request $request, int $payment, CloseManualCashlessPayment $closure): RedirectResponse
    {
        // Admin cancellation is an audited group closure, separate from correctable proof rejection.
        if (is_string($request->input('reason'))) {
            $request->merge(['reason' => trim($request->input('reason'))]);
        }
        $data = $request->validate(['reason' => ['required', 'string', 'max:1000']]);
        $closure->close($payment, ManualCashlessPayment::CANCELLED, $request->user()->id, $data['reason']);

        return redirect()->route('admin.payments.show', $payment)
            ->with('status', 'Payment and linked Orders cancelled.');
    }

    private function decide(Request $request, int $payment, string $decision, ?string $reason): RedirectResponse
    {
        DB::transaction(function () use ($request, $payment, $decision, $reason) {
            $record = ManualCashlessPayment::whereKey($payment)->lockForUpdate()->firstOrFail();
            // Lock the complete group before one decision; partial Seller release is impossible.
            $orders = Order::where('checkout_group_id', $record->checkout_group_id)
                ->orderBy('id')->lockForUpdate()->get();
            if ($record->status !== ManualCashlessPayment::PENDING_REVIEW || ! $record->receipt_path
                || ! Storage::disk('local')->exists($record->receipt_path)
                || ! $record->normalized_reference || $orders->isEmpty()
                || $orders->contains(fn (Order $order) => (int) $order->buyer_id !== (int) $record->buyer_id
                    || (int) $order->manual_cashless_payment_id !== (int) $record->id
                    || $order->payment_method !== 'online' || $order->status !== Order::STATUS_TO_SHIP)
                || Delivery::whereIn('order_id', $orders->pluck('id'))->exists()) {
                throw ValidationException::withMessages(['payment' => 'Payment or related Orders changed. Refresh and review them.']);
            }
            $record->update([
                'status' => $decision, 'decision' => $decision,
                'reviewer_user_id' => $request->user()->id, 'reviewed_at' => now(),
                // Rejection grants a new Buyer window; verification has no future expiry.
                'rejection_reason' => $reason,
                'expires_at' => $decision === ManualCashlessPayment::REJECTED ? now()->addDay() : null,
            ]);
        });

        return redirect()->route('admin.payments.show', $payment)
            ->with('status', $decision === ManualCashlessPayment::VERIFIED
                ? 'Verified by ShopHop Admin.' : 'Payment proof rejected.');
    }
}
