<?php

namespace Tests\Feature;

use App\Models\Buyer\Cart\CartItem;
use App\Models\Seller\Manage_inventory\Product;
use Tests\Support\SellerWorkspaceFixtures;
use Tests\TestCase;

class MarketplaceTimedDealsTest extends TestCase
{
    use SellerWorkspaceFixtures;

    public function test_timed_deal_expires_from_price_and_home_section(): void
    {
        $seller = $this->seller();
        $active = $this->product($seller, [
            'name' => 'Active timed lamp', 'price' => 200, 'discount' => 25,
            'deal_ends_at' => now()->addHour(),
        ]);
        $expired = $this->product($seller, [
            'name' => 'Expired timed lamp', 'price' => 200, 'discount' => 25,
            'deal_ends_at' => now()->subMinute(),
        ]);

        $this->assertSame(25, $active->fresh()->discount);
        $this->assertSame(0, $expired->fresh()->discount);
        $this->assertSame(150.0, (new CartItem())->setRelation('product', $active)->unitPrice());
        $this->assertSame(200.0, (new CartItem())->setRelation('product', $expired)->unitPrice());
        $this->assertSame([$active->id], Product::query()->withActiveDiscount()->pluck('id')->all());
        $this->get('/')->assertOk()->assertSee('Next deal ends in')
            ->assertSee('data-deal-countdown', false);
    }

    public function test_seller_video_appears_in_watch_and_shop(): void
    {
        $this->product($this->seller(), [
            'name' => 'Video demo lamp', 'video_path' => 'product-videos/demo.mp4',
        ]);
        $this->get('/')->assertOk()->assertSee('Watch &amp; Shop', false)
            ->assertSee('Video demo lamp')->assertSee('data-shop-video', false);
    }
}
