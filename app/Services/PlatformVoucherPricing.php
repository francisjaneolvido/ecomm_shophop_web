<?php
namespace App\Services;

use App\Models\Finance\PlatformVoucher;
use Illuminate\Validation\ValidationException;
use App\Models\Buyer\Order\Order;

class PlatformVoucherPricing
{
    /**
     * $groups keyed by seller ID: [merchandise (after seller discount), shipping].
     * Both codes are optional. Never trust a client-submitted discount amount.
     * All allocations are cents-based with remainder on the final group.
     */
    public function price(array $groups, string $merchCode = '', string $shipCode = '', bool $lock = false, int $buyerId = 0): array
    {
        $merch = $this->find($merchCode, 'merchandise', $lock, $buyerId);
        $ship = $this->find($shipCode, 'shipping', $lock, $buyerId);
        $base = round(array_sum(array_column($groups, 'merchandise')), 2);
        $shipping = round(array_sum(array_column($groups, 'shipping')), 2);
        $merchDiscount = $this->discount($merch, $base, $base);
        $shippingDiscount = $this->discount($ship, $shipping, $base);
        $allocations = [];
        $leftMerch = (int) round($merchDiscount * 100);
        $leftShip = (int) round($shippingDiscount * 100);
        $keys = array_keys($groups);
        foreach ($keys as $idx => $key) {
            $last = $idx === count($keys) - 1;
            $m = $last ? $leftMerch : (int) floor(($merchDiscount * 100) * ($base > 0 ? $groups[$key]['merchandise'] / $base : 0));
            $s = $last ? $leftShip : (int) floor(($shippingDiscount * 100) * ($shipping > 0 ? $groups[$key]['shipping'] / $shipping : 0));
            $m = min($m, $leftMerch); $s = min($s, $leftShip);
            $allocations[$key] = ['platform_discount' => $m / 100, 'shipping_discount' => $s / 100];
            $leftMerch -= $m; $leftShip -= $s;
        }
        return ['allocations' => $allocations, 'merchandise_voucher' => $merch,
            'shipping_voucher' => $ship, 'merchandise_discount' => $merchDiscount,
            'shipping_discount' => $shippingDiscount];
    }

    private function find(string $code, string $kind, bool $lock, int $buyerId): ?PlatformVoucher
    {
        $code = strtoupper(trim($code));
        if ($code === '') return null;
        $query = PlatformVoucher::available()->where('code', $code)->where('kind', $kind);
        $voucher = ($lock ? $query->lockForUpdate() : $query)->first();
        if (! $voucher || (float) $voucher->value <= 0
            || ! in_array($voucher->type, ['fixed', 'percent'], true)
            || ($voucher->type === 'percent' && (float) $voucher->value > 100)) {
            throw ValidationException::withMessages([$kind.'_voucher_code' => 'Invalid or unavailable ShopHop '.$kind.' voucher.']);
        }
        if ($buyerId > 0 && $voucher->per_buyer_limit !== null) {
            $column = $kind === 'shipping' ? 'shipping_voucher_id' : 'platform_voucher_id';
            $alreadyUsed = Order::query()->where('buyer_id', $buyerId)->where($column, $voucher->id)
                ->distinct('checkout_group_id')->count('checkout_group_id');
            if ($alreadyUsed >= $voucher->per_buyer_limit) {
                throw ValidationException::withMessages([$kind.'_voucher_code' => 'You have reached the ShopHop voucher use limit.']);
            }
        }
        return $voucher;
    }

    private function discount(?PlatformVoucher $voucher, float $eligible, float $merchandise): float
    {
        if (! $voucher) return 0;
        if ($eligible <= 0 || $merchandise < (float) $voucher->min_spend) {
            throw ValidationException::withMessages([$voucher->kind.'_voucher_code' => 'Voucher minimum spend not met.']);
        }
        $discount = round(min($eligible, $voucher->type === 'percent'
            ? $eligible * ((float) $voucher->value / 100) : (float) $voucher->value), 2);
        if ($voucher->max_discount !== null) $discount = min($discount, (float) $voucher->max_discount);
        if ($voucher->budget_amount !== null && round((float) $voucher->spent_amount + $discount, 2) > (float) $voucher->budget_amount) {
            throw ValidationException::withMessages([$voucher->kind.'_voucher_code' => 'ShopHop voucher budget is insufficient for this checkout.']);
        }
        return $discount;
    }
}
