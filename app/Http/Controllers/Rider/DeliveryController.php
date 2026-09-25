<?php

namespace App\Http\Controllers\Rider;

use App\Http\Controllers\Controller;
use App\Models\Buyer\Order\Order;
use App\Models\Logistics\Delivery;
use App\Models\Logistics\Rider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class DeliveryController extends Controller
{
    public function index(Request $request): View
    {
        // Rider list is scoped by both assignment and partner, never by a submitted identity field.
        $rider = $request->user('rider');
        $deliveries = Delivery::with(['order.seller'])
            ->where('rider_id', $rider->id)->where('logistics_partner_id', $rider->logistics_partner_id)
            ->latest()->get();

        return view('rider.deliveries', compact('deliveries'));
    }

    public function show(Request $request, int $delivery): View
    {
        // The assigned Rider may see only this Delivery's destination and persisted events.
        $owned = $this->owned($request, $delivery)->load(['order.seller']);

        return view('rider.show', ['delivery' => $owned]);
    }

    public function pickup(Request $request, int $delivery): RedirectResponse
    {
        return $this->transition($request, $delivery, 'assigned', 'picked_up',
            Order::STATUS_READY_FOR_PICKUP, Order::STATUS_TO_RECEIVE, 'picked_up_at', 'pickup_rider_id');
    }

    public function transit(Request $request, int $delivery): RedirectResponse
    {
        return $this->transition($request, $delivery, 'picked_up', 'in_transit',
            Order::STATUS_TO_RECEIVE, Order::STATUS_TO_RECEIVE, 'in_transit_at', 'transit_rider_id');
    }

    public function complete(Request $request, int $delivery): RedirectResponse
    {
        // Foreign Deliveries are denied before upload validation; the lock below rechecks a stale assignment.
        $this->owned($request, $delivery);
        // Photo bytes are validated before any state change and stored on the private local disk.
        $request->validate(['proof' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120']]);
        $path = null;
        try {
            return DB::transaction(function () use ($request, $delivery, &$path) {
                $rider = $request->user('rider');
                $owned = $this->owned($request, $delivery, true);
                // Suspension between middleware and the locked write cannot complete an old Rider page.
                $this->assertActive($rider->id, $owned->logistics_partner_id);
                if ($owned->status !== 'in_transit' || $owned->order?->status !== Order::STATUS_TO_RECEIVE
                    || $owned->proof_path || $owned->delivered_at) {
                    return $this->stale();
                }
                $path = $request->file('proof')->store('delivery-proofs', 'local');
                if (! $path) {
                    throw ValidationException::withMessages(['proof' => 'Proof could not be stored.']);
                }
                // Order completion and immutable Rider proof commit together; failed DB writes remove the uploaded file.
                if (Order::whereKey($owned->order_id)->where('status', Order::STATUS_TO_RECEIVE)
                    ->where('payment_method', 'cod')->update(['status' => Order::STATUS_COMPLETED]) !== 1
                    || Delivery::whereKey($owned->id)->where('status', 'in_transit')->whereNull('proof_path')
                        ->where('rider_id', $rider->id)->update([
                            'status' => 'delivered', 'delivered_at' => now(),
                            'delivered_rider_id' => $rider->id, 'proof_path' => $path,
                        ]) !== 1) {
                    throw ValidationException::withMessages(['delivery' => 'Delivery changed. Refresh and review it.']);
                }

                return redirect()->route('rider.deliveries.show', $owned)->with('status', 'Delivery completed.');
            });
        } catch (Throwable $exception) {
            if ($path) {
                Storage::disk('local')->delete($path);
            }
            throw $exception;
        }
    }

    private function transition(Request $request, int $deliveryId, string $from, string $to,
        string $orderFrom, string $orderTo, string $timestamp, string $actorColumn): RedirectResponse
    {
        return DB::transaction(function () use ($request, $deliveryId, $from, $to, $orderFrom, $orderTo, $timestamp, $actorColumn) {
            $rider = $request->user('rider');
            $owned = $this->owned($request, $deliveryId, true);
            $this->assertActive($rider->id, $owned->logistics_partner_id);
            // A locked Delivery and conditional Order update reject repeats, skips, and stale assignment races.
            if ($owned->status !== $from || $owned->order?->status !== $orderFrom) {
                return $this->stale();
            }
            if (Order::whereKey($owned->order_id)->where('status', $orderFrom)->where('payment_method', 'cod')
                ->update(['status' => $orderTo]) !== 1
                || Delivery::whereKey($owned->id)->where('status', $from)->where('rider_id', $rider->id)
                    ->update(['status' => $to, $timestamp => now(), $actorColumn => $rider->id]) !== 1) {
                throw ValidationException::withMessages(['delivery' => 'Delivery changed. Refresh and review it.']);
            }

            return redirect()->route('rider.deliveries.show', $owned)->with('status', 'Delivery updated.');
        });
    }

    private function owned(Request $request, int $deliveryId, bool $lock = false): Delivery
    {
        $rider = $request->user('rider');
        $query = Delivery::with('order')->whereKey($deliveryId)->where('rider_id', $rider->id)
            ->where('logistics_partner_id', $rider->logistics_partner_id);

        return ($lock ? $query->lockForUpdate() : $query)->firstOrFail();
    }

    private function stale(): RedirectResponse
    {
        return back()->withErrors(['delivery' => 'Delivery changed. Refresh and review it.']);
    }

    private function assertActive(int $riderId, int $partnerId): void
    {
        // Lock the persisted actor row for this transition instead of trusting a session-cached model.
        $rider = Rider::whereKey($riderId)->where('logistics_partner_id', $partnerId)
            ->lockForUpdate()->first();
        abort_unless($rider && $rider->status === 'active' && $rider->email && $rider->password
            && $rider->partner?->user?->status === 'approved', 403);
    }
}
