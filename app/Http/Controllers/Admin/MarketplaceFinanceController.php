<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Buyer\Order\Order;
use App\Models\Finance\MarketplaceRateSetting;
use App\Models\Finance\OrderFinancial;
use App\Models\Finance\PlatformVoucher;
use App\Models\Finance\SettlementEntry;
use App\Models\Logistics\ReturnCase;
use App\Services\FinancialAudit;
use App\Services\ShopHopFinance;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class MarketplaceFinanceController extends Controller
{
    public function index(): View
    {
        $blockedOrders = DB::table('cod_discrepancy_cases as cases')->join('cod_settlements as cash', 'cash.id', '=', 'cases.cod_settlement_id')
            ->where('cases.status', '!=', 'resolved')->select('cash.order_id');

        return view('admin.commission', [
            'rates' => MarketplaceRateSetting::current(),
            'vouchers' => PlatformVoucher::orderByDesc('id')->limit(60)->get(),
            'promotionOrders' => Order::with(['voucher', 'platformVoucher', 'shippingVoucher', 'financial'])
                ->where(fn ($q) => $q->whereNotNull('voucher_id')
                    ->orWhereNotNull('platform_voucher_id')->orWhereNotNull('shipping_voucher_id'))
                ->latest('id')->paginate(20, ['*'], 'promo_page'),
            'finances' => OrderFinancial::orderByDesc('id')->paginate(20, ['*'], 'finance_page'),
            'settlements' => SettlementEntry::with('order')->orderByDesc('id')->paginate(30, ['*'], 'payout_page'),
            'eligibleOrders' => Order::whereNotIn('id', clone $blockedOrders)->where(fn ($q) => $q->where('status', Order::STATUS_COMPLETED)->orWhere(fn ($a) => $a->where('status', Order::STATUS_TO_RECEIVE)->whereHas('financial', fn ($f) => $f->where('seller_hold_days', '>', 0))))
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
            'platformPromoSpent' => (float) PlatformVoucher::sum('spent_amount'),
            'platformPromoBudget' => (float) PlatformVoucher::whereNotNull('budget_amount')->sum('budget_amount'),
            'commissionTotal' => (float) (OrderFinancial::where('settlement_hold', false)
                ->whereHas('order.settlements', fn ($q) => $q->whereIn('kind', ['seller', 'logistics'])->whereIn('status', ['available', 'paid']))
                ->whereHas('order', fn ($q) => $q->whereNotIn('status', [Order::STATUS_CANCELLED, Order::STATUS_RETURNED]))
                ->whereNotExists(fn ($q) => $q->selectRaw('1')->from('disputes')->whereColumn('disputes.order_id', 'order_financials.order_id')->whereNotIn('disputes.status', ['resolved', 'rejected']))
                ->selectRaw('COALESCE(SUM(commission + shipping_commission), 0) as total')->value('total') ?? 0),
            'distributionOrders' => Order::with(['financial', 'settlements', 'codSettlement'])->whereNotIn('id', clone $blockedOrders)
                ->whereHas('settlements', fn ($q) => $q->whereIn('kind', ['seller', 'logistics'])->where('status', 'pending'))
                ->latest('id')->limit(40)->get(),
            'availablePayouts' => SettlementEntry::with('order')->whereNotIn('order_id', clone $blockedOrders)
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
            'commission_percent' => ['required', 'numeric', 'min:0', 'max:100', 'decimal:0,2'],
            'shipping_commission_percent' => ['sometimes', 'numeric', 'min:0', 'max:100', 'decimal:0,2'],
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

        DB::transaction(function () use ($data, $request) {
            $rates = MarketplaceRateSetting::whereKey(MarketplaceRateSetting::current()->id)->lockForUpdate()->firstOrFail();
            $before = $rates->toArray();
            $rates->update($data);
            FinancialAudit::record('marketplace_rates', $rates->id, 'updated', 'user', $request->user()->id, ['before' => $before, 'after' => $rates->fresh()->toArray()]);
        });

        return back()->with('status', 'Financial settings updated. Existing order snapshots are unchanged.');
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
            'per_buyer_limit' => ['nullable', 'integer', 'min:1'],
            'max_discount' => ['nullable', 'numeric', 'gt:0', 'max:1000000'],
            'budget_amount' => ['nullable', 'numeric', 'gt:0', 'max:100000000'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after:now'],
        ]);
        if ($data['type'] === 'percent' && $data['value'] > 100) {
            throw ValidationException::withMessages(['value' => 'Percentage cannot exceed 100%.']);
        }
        if (! empty($data['starts_at']) && ! empty($data['ends_at'])
            && strtotime($data['ends_at']) <= strtotime($data['starts_at'])) {
            throw ValidationException::withMessages(['ends_at' => 'End date must be after the start date.']);
        }
        PlatformVoucher::create([
            ...$data, 'code' => strtoupper($data['code']), 'used_count' => 0, 'spent_amount' => 0, 'active' => true,
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
        DB::transaction(function () use ($order, $data, $request) {
            $financial = OrderFinancial::where('order_id', $order)->lockForUpdate()->firstOrFail();
            if (SettlementEntry::where('order_id', $order)->where('status', 'paid')->exists()) {
                throw ValidationException::withMessages(['hold' => 'At least one payout was already paid. Manual dispute handling required.']);
            }
            if (! (bool) $data['hold'] && ReturnCase::where('order_id', $order)->exists()) {
                throw ValidationException::withMessages(['hold' => 'Return cases cannot have settlement holds manually removed.']);
            }
            $financial->update(['settlement_hold' => (bool) $data['hold'],
                'hold_reason' => $data['hold'] ? ($data['reason'] ?: 'Under admin review') : null]);
            FinancialAudit::record('order_financial', $financial->id, 'hold_changed', 'user', $request->user()->id, $data);
        });

        return back()->with('status', 'Order settlement hold updated.');
    }

    public function prepare(int $order, ShopHopFinance $finance): RedirectResponse
    {
        $legacyBackfilled = $finance->prepareForOrder($order);

        $message = $legacyBackfilled
            ? 'Historical Order financial snapshot reconstructed from its frozen checkout amounts. Seller and Logistics allocations are now prepared and pending distribution.'
            : 'Eligible Seller and Logistics allocations prepared and pending distribution.';

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
