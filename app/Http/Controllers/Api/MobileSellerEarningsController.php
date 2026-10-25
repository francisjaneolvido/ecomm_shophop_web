<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Finance\SettlementEntry;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MobileSellerEarningsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $actor = $request->user();
        abort_unless($actor instanceof User && $actor->account_type === 'seller', 403, 'Seller login required.');

        $seller = $actor->seller;
        abort_unless($seller && $actor->status === 'approved' && $actor->email_verified_at, 403, 'Approved Seller account required.');

        $query = SettlementEntry::with('order')
            ->where('kind', 'seller')
            ->where('payee_id', $seller->id);

        $entries = (clone $query)->latest('id')->limit(100)->get();

        return response()->json([
            'success' => true,
            'status' => 'ok',
            'message' => 'Seller earnings loaded.',
            'data' => [
                'seller_name' => $seller->business_name ?: $actor->name,
                'pending' => round((float) $entries->where('status', 'pending')->sum('amount'), 2),
                'available' => round((float) $entries->where('status', 'available')->sum('amount'), 2),
                'paid' => round((float) $entries->where('status', 'paid')->sum('amount'), 2),
                'entries' => $entries->map(fn (SettlementEntry $entry) => [
                    'id' => $entry->id,
                    'order_id' => $entry->order_id,
                    'amount' => (float) $entry->amount,
                    'status' => $entry->status,
                    'distribution_reference' => $entry->distribution_reference,
                    'payment_reference' => $entry->payment_reference,
                    'distributed_at' => optional($entry->distributed_at)?->toIso8601String(),
                    'paid_at' => optional($entry->paid_at)?->toIso8601String(),
                ])->values(),
            ],
        ]);
    }
}
