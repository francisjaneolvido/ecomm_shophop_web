<?php

namespace App\Services;

use App\Models\Buyer\Order\Order;
use App\Models\Finance\MarketplaceRateSetting;
use App\Models\Finance\OrderFinancial;
use App\Models\Logistics\Delivery;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PlatformFinancialPolicyService
{
    public function active(): array
    {
        $rule = DB::table('platform_financial_rules')->where('effective_at', '<=', now())->orderByDesc('effective_at')->first();
        if ($rule) {
            return ['id' => $rule->id, 'product_percent' => $rule->product_percent, 'shipping_percent' => $rule->shipping_percent, 'seller_hold_days' => $rule->seller_hold_days];
        }
        $rates = MarketplaceRateSetting::current();

        return ['id' => null, 'product_percent' => $rates->commission_percent, 'shipping_percent' => $rates->shipping_commission_percent, 'seller_hold_days' => 0];
    }

    public function schedule(User $admin, array $data): void
    {
        abort_unless($admin->fresh()?->status === 'approved' && $admin->account_type === 'admin', 403);
        DB::transaction(function () use ($admin, $data) {
            MarketplaceRateSetting::whereKey(MarketplaceRateSetting::current()->id)->lockForUpdate()->firstOrFail();
            FinanceMoney::percent(100, $data['product_percent']);
            FinanceMoney::percent(100, $data['shipping_percent']);
            if (Carbon::parse($data['effective_at'])->isPast()) {
                throw ValidationException::withMessages(['effective_at' => 'Platform rules must be scheduled prospectively.']);
            }
            $id = DB::table('platform_financial_rules')->insertGetId([
                ...$data, 'created_by_user_id' => $admin->id, 'created_at' => now(),
            ]);
            FinancialAudit::record('platform_rule', $id, 'scheduled', 'user', $admin->id, $data);
        });
    }

    /** Buyer confirmation OR the explicitly snapped automatic release period, never a Rider payroll gate. */
    public function eligible(Order $order, OrderFinancial $financial): bool
    {
        if (! $order->isPaymentEligible()) {
            return false;
        }
        if ($order->status === Order::STATUS_COMPLETED) {
            return true;
        }
        if ($order->status !== Order::STATUS_TO_RECEIVE || $financial->seller_hold_days <= 0) {
            return false;
        }
        $delivery = Delivery::where('order_id', $order->id)->first();

        return $delivery && $delivery->status === Delivery::DELIVERED && $delivery->delivered_at
            && $delivery->delivered_at->copy()->addDays((int) $financial->seller_hold_days)->lte(now());
    }
}
