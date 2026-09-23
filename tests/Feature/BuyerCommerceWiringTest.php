<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class BuyerCommerceWiringTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Keep cart coverage disposable while the unrelated full migration chain still duplicates order_items.order_id.
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('account_type');
            $table->string('status');
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamps();
        });

        Schema::create('buyers', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('seller_id');
            $table->string('name');
            $table->string('category');
            $table->decimal('price', 10, 2);
            $table->unsignedTinyInteger('discount')->default(0);
            $table->unsignedInteger('stock')->default(0);
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });
    }

    public function test_authenticated_buyer_dashboard_exposes_the_session_cart_contract_truthfully(): void
    {
        $buyer = new User();
        $buyer->forceFill([
            'name' => 'Buyer',
            'email' => 'buyer-commerce@example.test',
            'password' => 'not-used-by-this-test',
            'account_type' => 'buyer',
            'status' => 'approved',
            'email_verified_at' => now(),
        ])->save();

        $productId = \Illuminate\Support\Facades\DB::table('products')->insertGetId([
            'seller_id' => 42,
            'name' => 'Session Cart Product',
            'category' => 'Electronics and Gadgets',
            'price' => 799,
            'discount' => 0,
            'stock' => 4,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $dashboard = $this->actingAs($buyer)
            ->withSession(['shophop_cart' => []])
            ->get(route('buyer.dashboard'))
            ->assertOk()
            ->assertSee('data-dashboard-add-to-cart', false)
            ->assertSee('data-buyer-cart-link', false)
            ->assertSee('data-wishlist-unavailable', false);

        $this->assertCartCountInMarkup($dashboard->getContent(), 0);

        $this->postJson(route('buyer.cart.add'), [
            'product_id' => $productId,
            'name' => 'Session Cart Product',
            'image' => 'images/placeholder-product.jpg',
            'price' => 799,
            'stock' => 4,
            'qty' => 1,
            'shop_id' => 42,
        ])
            ->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('cart_count', 1);

        $cart = $this->app['session']->get('shophop_cart');
        $this->assertIsArray($cart);
        $this->assertCount(1, $cart);
        $this->assertSame('Session Cart Product', reset($cart)['name']);
        $this->assertSame(1, reset($cart)['qty']);

        $dashboard = $this->get(route('buyer.dashboard'))
            ->assertOk();

        $this->assertCartCountInMarkup($dashboard->getContent(), 1);

        $this->get(route('buyer.cart'))
            ->assertOk()
            ->assertSee('Session Cart Product');
    }

    private function assertCartCountInMarkup(string $html, int $expectedCount): void
    {
        $document = new \DOMDocument();
        @$document->loadHTML($html);

        $counts = (new \DOMXPath($document))->query('//*[@data-cart-count]');

        $this->assertNotFalse($counts);
        $this->assertGreaterThan(0, $counts->length);

        foreach ($counts as $count) {
            $this->assertSame((string) $expectedCount, trim($count->textContent));
        }
    }
}
