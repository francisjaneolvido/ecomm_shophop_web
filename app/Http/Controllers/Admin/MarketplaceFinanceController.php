<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Buyer\Order\Order;
use App\Models\Finance\OrderFinancial;
use App\Models\Finance\PaymentSetting;
use App\Models\Finance\PlatformVoucher;
use App\Models\Finance\MarketplaceRateSetting;
use App\Models\Finance\SettlementEntry;
use App\Services\ShopHopFinance;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class MarketplaceFinanceController extends Controller
{
    public function index(): View
    {
        return view('admin.commission', [
            'setting' => PaymentSetting::current(),
            'rates' => MarketplaceRateSetting::current(),
            'vouchers' => PlatformVoucher::orderByDesc('id')->limit(60)->get(),
            'finances' => OrderFinancial::orderByDesc('id')->paginate(20, ['*'], 'finance_page'),
            'settlements' => SettlementEntry::with('order')->orderByDesc('id')->paginate(30, ['*'], 'payout_page'),
            'eligibleOrders' => Order::where('status', Order::STATUS_COMPLETED)
                ->where(fn ($q) => $q->where('payment_method', '!=', 'cod')
                    ->orWhereHas('codSettlement', fn ($cash) => $cash->whereNotNull('admin_received_at')
                        ->whereColumn('admin_received_amount', 'expected_amount')))
                ->where(function ($query) {
                    $query->whereDoesntHave('financial')
                        ->orWhereHas('financial', fn ($financial) => $financial->where('settlement_hold', false));
                })
                ->whereDoesntHave('settlements')
                ->with('financial')
                ->latest('id')->limit(40)->get(),
            'commissionTotal' => (float) OrderFinancial::whereHas('order', fn ($q) => $q->where('status', Order::STATUS_COMPLETED))->sum('commission'),
            'distributionOrders' => Order::with(['financial', 'settlements', 'codSettlement'])
                ->whereHas('settlements', fn ($q) => $q->whereIn('kind', ['seller', 'logistics'])->where('status', 'pending'))
                ->latest('id')->limit(40)->get(),
            'availablePayouts' => SettlementEntry::with('order')
                ->whereIn('kind', ['seller', 'logistics'])->where('status', 'available')
                ->orderByDesc('distributed_at')->limit(80)->get(),
            'pendingDistributionTotal' => (float) SettlementEntry::whereIn('kind', ['seller', 'logistics'])->where('status', 'pending')->sum('amount'),
            'availablePayoutTotal' => (float) SettlementEntry::whereIn('kind', ['seller', 'logistics'])->where('status', 'available')->sum('amount'),
            'paidPayoutTotal' => (float) SettlementEntry::whereIn('kind', ['seller', 'logistics'])->where('status', 'paid')->sum('amount'),
        ]);
    }


    public function updateRates(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'commission_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'base_shipping_fee' => ['required', 'numeric', 'min:0', 'max:100000'],
            'local_distance_fee' => ['required', 'numeric', 'min:0', 'max:100000'],
            'same_province_distance_fee' => ['required', 'numeric', 'min:0', 'max:100000'],
            'interprovince_distance_fee' => ['required', 'numeric', 'min:0', 'max:100000'],
            'same_province_linehaul_fee' => ['required', 'numeric', 'min:0', 'max:100000'],
            'interprovince_linehaul_fee' => ['required', 'numeric', 'min:0', 'max:100000'],
            'included_weight_kg' => ['required', 'numeric', 'min:0.05', 'max:10000'],
            'extra_weight_fee_per_kg' => ['required', 'numeric', 'min:0', 'max:100000'],
            'express_surcharge_percent' => ['required', 'numeric', 'min:0', 'max:500'],
            'pickup_rider_fee' => ['required', 'numeric', 'min:0', 'max:100000'],
            'delivery_rider_fee' => ['required', 'numeric', 'min:0', 'max:100000'],
            'origin_branch_fee' => ['required', 'numeric', 'min:0', 'max:100000'],
            'destination_branch_fee' => ['required', 'numeric', 'min:0', 'max:100000'],
            'province_hub_fee' => ['required', 'numeric', 'min:0', 'max:100000'],
            'linehaul_branch_hub_driver_fee' => ['required', 'numeric', 'min:0', 'max:100000'],
            'linehaul_hub_hub_driver_fee' => ['required', 'numeric', 'min:0', 'max:100000'],
        ]);

        // The confirmed ShopHop business rule is 10%; keep Admin from silently drifting it.
        $data['commission_percent'] = 10;
        MarketplaceRateSetting::current()->update($data);

        return back()->with('status', 'Shipping, handling and payout rates updated. ShopHop commission remains 10%.');
    }

    public function paymentSetting(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'recipient_name' => ['required', 'string', 'max:160'],
            'recipient_identifier' => ['nullable', 'string', 'max:100'],
            'instructions' => ['nullable', 'string', 'max:1000'],
            'qr_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);
        $newPath = $request->hasFile('qr_image')
            ? $request->file('qr_image')->store('demo-payment-qr', 'public') : null;
        try {
            $previous = null;
            DB::transaction(function () use ($data, $newPath, &$previous) {
                $setting = PaymentSetting::query()->lockForUpdate()->first();
                if (! $setting) {
                    $setting = new PaymentSetting();
                    $setting->setAttribute('id', 1);
                }
                $previous = $setting->qr_path;
                $setting->fill([
                    'mode' => 'demo', 'recipient_name' => $data['recipient_name'],
                    'recipient_identifier' => $data['recipient_identifier'] ?? null,
                    'instructions' => $data['instructions'] ?? null,
                    'qr_path' => $newPath ?: $previous,
                ]);
                $setting->save();
            });
            if ($newPath && $previous && $previous !== $newPath) Storage::disk('public')->delete($previous);
        } catch (\Throwable $e) {
            if ($newPath) Storage::disk('public')->delete($newPath);
            throw $e;
        }
        return back()->with('status', 'Demo payment QR configuration saved.');
    }

    public function createVoucher(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'regex:/^[A-Z0-9_-]{3,40}$/i', 'unique:platform_vouchers,code'],
            'name' => ['required', 'string', 'max:160'],
            'kind' => ['required', Rule::in(['merchandise', 'shipping'])],
            'type' => ['required', Rule::in(['fixed', 'percent'])],
            'value' => ['required', 'numeric', 'gt:0', 'max:1000000'],
            'min_spend' => ['required', 'numeric', 'min:0', 'max:1000000'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'ends_at' => ['nullable', 'date', 'after:now'],
        ]);
        if ($data['type'] === 'percent' && $data['value'] > 100) {
            throw ValidationException::withMessages(['value' => 'Percentage cannot exceed 100%.']);
        }
        PlatformVoucher::create([
            ...$data, 'code' => strtoupper($data['code']), 'used_count' => 0, 'active' => true,
        ]);
        return back()->with('status', 'ShopHop-funded voucher created.');
    }

    public function toggleVoucher(PlatformVoucher $voucher): RedirectResponse
    {
        $voucher->update(['active' => ! $voucher->active]);
        return back()->with('status', 'Voucher availability updated. Existing orders are unchanged.');
    }

    public function hold(Request $request, int $order): RedirectResponse
    {
        $data = $request->validate(['hold' => ['required', 'boolean'], 'reason' => ['nullable', 'string', 'max:500']]);
        DB::transaction(function () use ($order, $data) {
            $financial = OrderFinancial::where('order_id', $order)->lockForUpdate()->firstOrFail();
            if (SettlementEntry::where('order_id', $order)->where('status', 'paid')->exists()) {
                throw ValidationException::withMessages(['hold' => 'At least one payout was already paid. Manual dispute handling required.']);
            }
            if (! (bool) $data['hold'] && \App\Models\Logistics\ReturnCase::where('order_id',$order)->exists()) {
                throw ValidationException::withMessages(['hold'=>'Return cases cannot have settlement holds manually removed.']);
            }
            $financial->update(['settlement_hold' => (bool) $data['hold'],
                'hold_reason' => $data['hold'] ? ($data['reason'] ?: 'Under admin review') : null]);
        });
        return back()->with('status', 'Order settlement hold updated.');
    }

    public function prepare(int $order, ShopHopFinance $finance): RedirectResponse
    {
        $legacyBackfilled = $finance->prepareForOrder($order);

        $message = $legacyBackfilled
            ? 'Historical Order financial snapshot reconstructed from its frozen checkout amounts. Seller, Logistics and Rider earnings are now prepared and pending distribution.'
            : 'Eligible Seller, Logistics and Rider earnings prepared and pending distribution.';

        return back()->with('status', $message);
    }

    public function distribute(Request $request, int $order, ShopHopFinance $finance): RedirectResponse
    {
        $reference = $finance->distributeOrder($order, (int) $request->user()->id);
        return back()->with('status', 'Settlement distributed to beneficiary ledgers. Reference '.$reference.'. No external transfer was performed.');
    }

    public function paid(Request $request, int $entry, ShopHopFinance $finance): RedirectResponse
    {
        $data = $request->validate(['reference' => ['required', 'string', 'max:120']]);
        $finance->markAdminPaid($entry, (int) $request->user()->id, trim($data['reference']));
        return back()->with('status', 'Manual payout recorded. No money was transferred by ShopHop.');
    }
}
