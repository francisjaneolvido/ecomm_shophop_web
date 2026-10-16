<?php
namespace App\Services;

use App\Models\Finance\PlatformVoucher;
use Illuminate\Validation\ValidationException;

class PlatformVoucherPricing
{
    /**
     * $groups keyed by seller ID: [merchandise (after seller discount), shipping].
     * Both codes are optional. Never trust a client-submitted discount amount.
     * All allocations are cents-based with remainder on the final group.
     */
    public function price(array $groups, string $merchCode = '', string $shipCode = '', bool $lock = false): array
    {
        $merch = $this->find($merchCode, 'merchandise', $lock);
        $ship = $this->find($shipCode, 'shipping', $lock);
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

    private function find(string $code, string $kind, bool $lock): ?PlatformVoucher
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
        return $voucher;
    }

    private function discount(?PlatformVoucher $voucher, float $eligible, float $merchandise): float
    {
        if (! $voucher) return 0;
        if ($eligible <= 0 || $merchandise < (float) $voucher->min_spend) {
            throw ValidationException::withMessages([$voucher->kind.'_voucher_code' => 'Voucher minimum spend not met.']);
        }
        return round(min($eligible, $voucher->type === 'percent'
            ? $eligible * ((float) $voucher->value / 100) : (float) $voucher->value), 2);
    }
}
