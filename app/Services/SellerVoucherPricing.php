<?php
namespace App\Services;

use App\Models\Buyer\Order\Order;
use App\Models\Seller\Manage_inventory\Voucher;
use Illuminate\Validation\ValidationException;

/** Seller-funded vouchers are scoped to exactly one shop and selected by shop ID. */
class SellerVoucherPricing
{
    public function selectedCode(int $shopId, array $codes, string $legacyCode = ''): string
    {
        // Older Web/Mobile clients send one seller code; it still applies only to its own Seller.
        return strtoupper(trim((string) ($codes[$shopId] ?? $codes[(string) $shopId] ?? $legacyCode)));
    }

    public function find(string $code, int $sellerUserId, int $buyerId, bool $lock = false): ?Voucher
    {
        if ($code === '') return null;
        $query = Voucher::query()->where('code', $code)->where('seller_id', $sellerUserId);
        $voucher = ($lock ? $query->lockForUpdate() : $query)->first();
        if (! $voucher || $voucher->status !== 'active'
            || ($voucher->starts_at && $voucher->starts_at->isFuture())
            || ($voucher->ends_at && $voucher->ends_at->isPast())
            || ($voucher->usage_limit !== null && $voucher->used_count >= $voucher->usage_limit)
            || (float) $voucher->value <= 0 || ! in_array($voucher->type, ['fixed','percent'], true)
            || ($voucher->type === 'percent' && (float) $voucher->value > 100)) {
            throw ValidationException::withMessages(['seller_vouchers' => 'This Seller voucher is invalid or unavailable for the selected shop.']);
        }
        if ($buyerId > 0 && $voucher->per_buyer_limit !== null
            && Order::where('buyer_id', $buyerId)->where('voucher_id', $voucher->id)
                ->count() >= $voucher->per_buyer_limit) {
            throw ValidationException::withMessages(['seller_vouchers' => 'You have reached the use limit for this Seller voucher.']);
        }
        return $voucher;
    }

    /** $eligible is the subtotal of selected eligible products only. */
    public function discounts(?Voucher $voucher, float $eligible, float $subtotal, float $shipping, float $commissionRate): array
    {
        if (! $voucher) return ['merchandise' => 0.0, 'shipping' => 0.0];
        if ($eligible <= 0 || $eligible < (float) $voucher->min_order_amount) {
            throw ValidationException::withMessages(['seller_vouchers' => 'Seller voucher minimum spend or product eligibility not met.']);
        }
        $shippingKind = ($voucher->kind ?? 'merchandise') === 'shipping';
        $base = $shippingKind ? $shipping : $eligible;
        $discount = min($base, round($voucher->type === 'percent'
            ? $base * (float) $voucher->value / 100 : (float) $voucher->value, 2));
        if ($voucher->max_discount !== null) $discount = min($discount, (float) $voucher->max_discount);
        if ($shippingKind) {
            $discount = min($discount, max(0, round($subtotal * (1 - $commissionRate / 100), 2)));
        }
        return ['merchandise' => $shippingKind ? 0.0 : $discount,
            'shipping' => $shippingKind ? $discount : 0.0];
    }
}
