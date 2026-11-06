<?php
namespace Tests\Unit;

use App\Models\Seller\Manage_inventory\Voucher;
use App\Services\SellerVoucherPricing;
use App\Services\PlatformVoucherPricing;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class VoucherV2PricingTest extends TestCase
{
    public function test_merchandise_percentage_discount_obeys_maximum_cap(): void
    {
        $voucher = new Voucher(['kind' => 'merchandise', 'type' => 'percent',
            'value' => 20, 'min_order_amount' => 500, 'max_discount' => 100]);
        $result = (new SellerVoucherPricing())->discounts($voucher, 1200, 1200, 80, 10);
        $this->assertSame(100.0, (float) $result['merchandise']);
        $this->assertSame(0.0, (float) $result['shipping']);
    }

    public function test_shipping_voucher_never_exceeds_shipping_or_seller_net(): void
    {
        $voucher = new Voucher(['kind' => 'shipping', 'type' => 'percent',
            'value' => 100, 'min_order_amount' => 10]);
        $result = (new SellerVoucherPricing())->discounts($voucher, 30, 30, 200, 10);
        $this->assertSame(27.0, (float) $result['shipping']);
        $this->assertSame(0.0, (float) $result['merchandise']);
    }

    public function test_ineligible_product_spend_is_rejected(): void
    {
        $voucher = new Voucher(['kind' => 'merchandise', 'type' => 'fixed',
            'value' => 50, 'min_order_amount' => 500]);
        $this->expectException(ValidationException::class);
        (new SellerVoucherPricing())->discounts($voucher, 100, 1000, 80, 10);
    }

    public function test_one_code_per_seller_is_backwards_compatible(): void
    {
        $service = new SellerVoucherPricing();
        $this->assertSame('A10', $service->selectedCode(101, [101 => 'a10'], 'OLD'));
        $this->assertSame('OLD', $service->selectedCode(202, [], 'old'));
        $this->assertSame('', $service->selectedCode(202, [202 => ''], 'old'));
    }
}
