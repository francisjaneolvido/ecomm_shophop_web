<?php
namespace Tests\Unit;

use App\Services\ShopHopFinance;
use Tests\TestCase;

class ShopHopFinanceMathTest extends TestCase
{
    public function test_seller_voucher_reduces_commission_base_but_platform_vouchers_do_not(): void
    {
        $money = ShopHopFinance::breakdown(1000, 100, 50, 80, 30, 0);
        $this->assertEquals(90.00, $money['commission']);
        $this->assertEquals(810.00, $money['seller_earnings']);
        $this->assertEquals(80.00, $money['logistics_earnings']);
        $this->assertEquals(10.00, $money['platform_net']);
        $this->assertEquals(900.00, array_sum([
            $money['seller_earnings'], $money['logistics_earnings'], $money['platform_net'],
        ]));
    }

    public function test_shipping_is_not_part_of_product_commission(): void
    {
        $a = ShopHopFinance::breakdown(500, 0, 0, 58, 0, 0);
        $b = ShopHopFinance::breakdown(500, 0, 0, 128, 0, 0);
        $this->assertEquals(50.00, $a['commission']);
        $this->assertEquals($a['commission'], $b['commission']);
        $this->assertEquals(450.00, $b['seller_earnings']);
        $this->assertEquals(128.00, $b['logistics_earnings']);
    }

    public function test_shop_hop_can_make_negative_net_after_promotions_without_charging_the_seller(): void
    {
        $money = ShopHopFinance::breakdown(100, 0, 40, 58, 58, 0);
        $this->assertEquals(90.00, $money['seller_earnings']);
        $this->assertEquals(58.00, $money['logistics_earnings']);
        $this->assertEquals(-88.00, $money['platform_net']);
    }
}
