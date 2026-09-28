<?php

namespace App\Http\Controllers;

use App\Models\Buyer\Order\ManualCashlessPayment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class PaymentReceiptController extends Controller
{
    public function show(int $payment): Response
    {
        // Receipt bytes are restricted to the owning Buyer and approved Admin, never delivery roles.
        $record = ManualCashlessPayment::findOrFail($payment);
        $user = Auth::guard('web')->user();
        abort_unless($user && ! Auth::guard('rider')->check() && $user->status === 'approved'
            && ($user->account_type === 'admin'
                || ($user->account_type === 'buyer' && $user->buyer?->id === $record->buyer_id)), 403);
        abort_unless($record->receipt_path && Storage::disk('local')->exists($record->receipt_path), 404);
        return Storage::disk('local')->response($record->receipt_path);
    }
}
