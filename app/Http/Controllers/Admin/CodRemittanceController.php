<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Logistics\CodSettlement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CodRemittanceController extends Controller
{
    public function index(): View
    {
        return view('admin.finance.cod-remittances', [
            'settlements' => CodSettlement::with(['order', 'partner', 'remitter', 'reconciler'])
                ->where('status', CodSettlement::RECONCILED)->orderByDesc('id')->paginate(30),
        ]);
    }

    /** Distinct Admin confirmation. Screenshot/reference alone is not proof of receipt. */
    public function confirm(Request $request, int $settlement): RedirectResponse
    {
        $data = $request->validate(['reference' => ['required','string','max:120'],
            'received_amount' => ['required','numeric','min:0']]);
        DB::transaction(function () use ($request, $settlement, $data) {
            $cash = CodSettlement::whereKey($settlement)->lockForUpdate()->firstOrFail();
            if ($cash->status !== CodSettlement::RECONCILED || ! $cash->admin_remitted_at
                || ! $cash->admin_remittance_reference || $cash->admin_received_at
                || $cash->admin_remittance_reference !== trim($data['reference'])
                || abs((float) $cash->expected_amount - (float) $cash->received_amount) > .001
                || abs((float) $data['received_amount'] - (float) $cash->expected_amount) > .001) {
                throw ValidationException::withMessages([
                    'settlement' => 'Remittance reference, amount or state differs. Check the actual received funds first.']);
            }
            $cash->update(['admin_received_amount' => $cash->expected_amount,
                'admin_received_at' => now(), 'admin_received_by_user_id' => $request->user()->id]);
        });
        return back()->with('status', 'ShopHop Admin confirmed COD receipt. Seller/Logistics payouts are still manual.');
    }
}
