<?php

namespace App\Http\Controllers;

use App\Models\Logistics\Delivery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class DeliveryProofController extends Controller
{
    public function show(Request $request, int $delivery): Response
    {
        // Proof is a private file and only parties to this Seller Order or assigned Rider may fetch it.
        $record = Delivery::with('order')->findOrFail($delivery);
        abort_unless($record->status === 'delivered' && $record->proof_path, 404);
        $user = Auth::guard('web')->user();
        $rider = Auth::guard('rider')->user()?->fresh(['partner.user']);
        $allowed = ! ($user && $rider) && (
            ($rider && $rider->status === 'active' && $rider->id === $record->rider_id
                && $rider->logistics_partner_id === $record->logistics_partner_id
                && $rider->partner?->user?->status === 'approved')
            || ($user && $user->status === 'approved' && match ($user->account_type) {
                'buyer' => $user->buyer?->id === $record->order?->buyer_id,
                'seller' => $user->seller?->id === $record->order?->seller_id,
                'logistics' => $user->logisticsPartner?->id === $record->logistics_partner_id,
                default => false,
            })
        );
        abort_unless($allowed, 403);
        abort_unless(Storage::disk('local')->exists($record->proof_path), 404);

        return Storage::disk('local')->response($record->proof_path);
    }
}
