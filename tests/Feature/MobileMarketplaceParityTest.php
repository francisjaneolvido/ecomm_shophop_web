<?php

namespace Tests\Feature;

use App\Models\Buyer\Order\Order;
use App\Models\Buyer\Order\OrderItem;
use Laravel\Sanctum\Sanctum;
use Tests\Support\SellerWorkspaceFixtures;
use Tests\TestCase;

class MobileMarketplaceParityTest extends TestCase
{
    use SellerWorkspaceFixtures;

    public function test_web_and_mobile_share_home_products_ordering_and_banners(): void
    {
        $seller = $this->seller();
        $this->product($seller, ['name'=>'First lamp', 'price'=>199.99, 'discount'=>15]);
        $this->product($seller, ['name'=>'Better discount', 'discount'=>30]);
        $this->product($seller, ['name'=>'Latest lamp']);
        foreach ([['compliance_status'=>'pending_review'], ['compliance_status'=>'rejected'], ['stock'=>0], ['status'=>'archived']] as $change) {
            $this->product($seller, ['name'=>'Hidden product', ...$change]);
        }
        $web = $this->get('/')->assertOk();
        Sanctum::actingAs($this->buyer());
        $mobile = $this->getJson('/api/mobile/buyer/home')->assertOk();
        foreach (['deal_products'=>'flashDealProducts', 'top_products'=>'topProducts', 'discover_products'=>'discoverProducts'] as $key=>$variable) {
            $this->assertSame($web->viewData($variable)->pluck('id')->all(), array_column($mobile->json('data.'.$key), 'id'));
        }
        $mobile->assertJsonCount(3, 'data.discover_products')->assertJsonPath('data.has_completed_sales', false);
        foreach (config('shophop_marketplace.banners') as $index=>$banner) {
            $mobile->assertJsonPath('data.banners.'.$index.'.heading', $banner['heading']);
            $web->assertSee($banner['heading'])->assertSee($banner['description']);
        }
        $this->assertSame(array_column(config('shophop_categories'), 'name'), array_column($mobile->json('data.categories'), 'name'));
        $category = collect($mobile->json('data.categories'))->firstWhere('slug', 'home-and-garden');
        $this->assertSame(3, $category['product_count']);
        $this->assertSame(config('shophop_categories.5.subcategories'), $category['subcategories']);
    }

    public function test_top_products_and_sold_counts_include_completed_orders_only(): void
    {
        $seller = $this->seller(); $buyer = $this->buyer();
        $completedProduct = $this->product($seller, ['name'=>'Completed sales']);
        $cancelledProduct = $this->product($seller, ['name'=>'Cancelled sales']);
        foreach ([['completed', $completedProduct, 2], ['cancelled', $cancelledProduct, 100], ['pending', $cancelledProduct, 50]] as [$status, $product, $quantity]) {
            $order = Order::create(['buyer_id'=>$buyer->buyer->id, 'seller_id'=>$seller->seller->id, 'status'=>$status, 'total_amount'=>100]);
            OrderItem::create(['order_id'=>$order->id, 'product_id'=>$product->id, 'quantity'=>$quantity, 'price'=>100]);
        }
        Sanctum::actingAs($buyer);
        $response = $this->getJson('/api/mobile/buyer/home')->assertOk()
            ->assertJsonPath('data.has_completed_sales', true)->assertJsonPath('data.top_products.0.id', $completedProduct->id)
            ->assertJsonPath('data.top_products.0.sold', 2)->assertJsonPath('data.top_products.1.sold', 0);
        $this->get('/')->assertOk()->assertViewHas('topProducts', fn ($products) => $products->first()->id === $completedProduct->id);
    }

    public function test_prices_match_web_and_cart_rounding_and_sort_by_displayed_price(): void
    {
        $seller = $this->seller();
        $discounted = $this->product($seller, ['name'=>'Centavo lamp', 'price'=>199.99, 'discount'=>15]);
        $this->product($seller, ['name'=>'Lower base price', 'price'=>180]);
        Sanctum::actingAs($this->buyer());
        $this->getJson('/api/mobile/buyer/products?sort=price_low')->assertOk()
            ->assertJsonPath('data.items.0.id', $discounted->id)->assertJsonPath('data.items.0.price', 169.99);
        $this->get('/')->assertOk()->assertSee('169.99');
        $this->get('/buyer/product/'.$discounted->id)->assertOk()->assertViewHas('product', fn ($product) => $product['price'] === 169.99);
        $this->get('/buyer/category/home-and-garden')->assertOk()->assertViewHas('products', fn ($products) => collect($products)->firstWhere('id', $discounted->id)['price'] === 169.99);
    }

    public function test_collection_and_subcategory_filters_are_real_and_validated(): void
    {
        $seller = $this->seller();
        $discounted = $this->product($seller, ['name'=>'Desk light', 'discount'=>10]);
        $this->product($seller, ['name'=>'Plain garden tool', 'subcategory'=>'Gardening Tools']);
        Sanctum::actingAs($this->buyer());
        $this->getJson('/api/mobile/buyer/products?collection=deals')->assertOk()->assertJsonCount(1, 'data.items')->assertJsonPath('data.items.0.id', $discounted->id);
        $query = http_build_query(['category'=>'home-and-garden', 'subcategory'=>'Furniture & Decor']);
        $this->getJson('/api/mobile/buyer/products?'.$query)->assertOk()->assertJsonCount(1, 'data.items')->assertJsonPath('data.items.0.id', $discounted->id);
        $this->getJson('/api/mobile/buyer/products?q=furniture')->assertOk()->assertJsonCount(1, 'data.items');
        $this->getJson('/api/mobile/buyer/products?category=home-and-garden&subcategory=forged')->assertUnprocessable();
        $this->getJson('/api/mobile/buyer/products?collection=forged')->assertUnprocessable();
    }

    public function test_home_requires_an_approved_verified_buyer_and_keeps_legacy_payload_keys(): void
    {
        $this->getJson('/api/mobile/buyer/home')->assertUnauthorized();
        Sanctum::actingAs($this->seller());
        $this->getJson('/api/mobile/buyer/home')->assertForbidden();
        Sanctum::actingAs($this->buyer());
        $this->getJson('/api/mobile/buyer/home')->assertOk()->assertJsonStructure(['data'=>[
            'categories', 'banners', 'top_products', 'discover_products', 'deal_products',
            'trending_products', 'recommended_products', 'new_arrivals', 'cart_count',
        ]]);
    }
}
