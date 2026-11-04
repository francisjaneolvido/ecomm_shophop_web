<?php

namespace Tests\Feature;

use App\Models\Buyer;
use App\Models\Buyer\Order\Order;
use App\Models\Buyer\Order\OrderItem;
use App\Models\Seller;
use App\Models\Seller\Manage_inventory\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SellerOrderDirectoryRegressionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // The fixture is destructive: never run it against a non-disposable database.
        $this->assertSame('sqlite', DB::getDefaultConnection());
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        Schema::dropAllTables();
        foreach ([
            '0001_01_01_000000_create_users_table.php',
            '2026_08_31_000001_add_account_type_and_status_to_users_table.php',
            '2026_08_29_000001_create_buyers_table.php',
            '2026_08_29_000002_create_sellers_table.php',
            '2026_08_29_000003_create_logistics_partners_table.php',
            '0001_01_01_000003_create_products_table.php',
            'Seller/Manage_inventory/2025_01_15_000001_add_inventory_fields_to_products_table.php',
            'Seller/Manage_inventory/2026_09_30_000001_add_product_compliance.php',
            'Seller/Manage_inventory/2025_01_15_000003_create_product_variants_table.php',
            'Seller/Manage_inventory/2025_01_15_000004_create_vouchers_table.php',
            'Seller/Manage_inventory/2025_01_15_000005_create_voucher_product_table.php',
            'Buyer/2026_09_12_102227_create_orders_table.php',
            'Buyer/2026_09_12_102228_create_order_items_table.php',
            'Buyer/2026_09_23_065258_add_checkout_fields_to_orders_table.php',
            'Buyer/2026_09_23_065410_create_cart_items_table.php',
            '2026_09_24_000001_create_logistics_operations_tables.php',
            '2026_09_26_000001_create_cod_settlements_table.php',
            '2026_09_28_000001_create_manual_cashless_payments_table.php',
        ] as $migration) {
            (require database_path('migrations/' . $migration))->up();
        }
    }

    public function test_seller_order_directory_is_paginated_searchable_and_scoped(): void
    {
        $owner = $this->seller('owner-orders@example.test');
        $other = $this->seller('other-orders@example.test');
        $buyer = $this->buyer();
        for ($n = 1; $n <= 27; $n++) {
            $this->order($owner, $buyer, "Owned Product {$n}");
        }
        $this->order($other, $buyer, 'Hidden Foreign Product');

        $page = $this->actingAs($owner->user)->get(route('seller.orders.notifications'));
        $page->assertOk()->assertSee('27 matching')->assertDontSee('Hidden Foreign Product');
        $this->assertSame(25, substr_count($page->getContent(), 'data-so-preview='));

        $next = $this->get(route('seller.orders.notifications', ['page' => 2]));
        $next->assertOk()->assertSee('Owned Product');
        $this->assertSame(2, substr_count($next->getContent(), 'data-so-preview='));

        $filtered = $this->get(route('seller.orders.notifications', ['q' => 'SHP-000001']));
        $filtered->assertOk()->assertSee('1 matching')->assertSee('Owned Product 1')
            ->assertDontSee('Owned Product 27');
    }

    public function test_prepare_and_courier_queues_show_only_eligible_statuses(): void
    {
        $seller = $this->seller('status-orders@example.test');
        $buyer = $this->buyer();
        $placed = $this->order($seller, $buyer, 'Placed Product');
        $confirmed = $this->order($seller, $buyer, 'Confirmed Product', Order::STATUS_CONFIRMED);
        $preparing = $this->order($seller, $buyer, 'Preparing Product', Order::STATUS_PREPARING);
        $ready = $this->order($seller, $buyer, 'Ready Product', Order::STATUS_READY_FOR_PICKUP);
        $shipping = $this->order($seller, $buyer, 'Shipping Product', Order::STATUS_TO_RECEIVE);

        $this->actingAs($seller->user)->get(route('seller.orders.prepare'))
            ->assertOk()->assertSee('Confirmed Product')->assertSee('Preparing Product')
            ->assertDontSee('Placed Product')->assertDontSee('Ready Product');
        $this->get(route('seller.orders.prepare', ['status' => 'confirmed']))
            ->assertOk()->assertSee('Confirmed Product')->assertDontSee('Preparing Product');
        $this->get(route('seller.orders.courier'))
            ->assertOk()->assertSee('Ready Product')->assertSee('Shipping Product')
            ->assertDontSee('Placed Product');
        $this->get(route('seller.orders.courier', ['status' => 'awaiting']))
            ->assertOk()->assertSee('Ready Product')->assertDontSee('Shipping Product');
        $this->get(route('seller.orders.confirm'))->assertOk()
            ->assertSee('Delivery confirmation is unavailable')->assertDontSee('Mark Delivered');
    }

    private function seller(string $email): Seller
    {
        $user = $this->user($email, 'seller');
        return Seller::create($this->profile($user->id) + [
            'business_name' => $email, 'business_category' => 'Home',
            'valid_id_path' => 'id.jpg', 'business_permit_path' => 'permit.jpg',
        ]);
    }

    private function buyer(): Buyer
    {
        $user = $this->user('buyer-orders@example.test', 'buyer');
        return Buyer::create($this->profile($user->id) + ['valid_id_path' => 'buyer-id.jpg']);
    }

    private function user(string $email, string $role): User
    {
        $user = new User();
        $user->forceFill(['email' => $email, 'password' => 'unused', 'account_type' => $role,
            'status' => 'approved', 'email_verified_at' => now()])->save();
        return $user;
    }

    private function profile(int $userId): array
    {
        return ['user_id' => $userId, 'first_name' => 'Test', 'last_name' => 'Person',
            'sex' => 'Male', 'contact_no' => '09123456789', 'birthday' => '1990-01-01',
            'province_code' => '01', 'province_name' => 'Province',
            'municipality_code' => '01', 'municipality_name' => 'City',
            'barangay_code' => '01', 'barangay_name' => 'Barangay', 'street_address' => '1 Street'];
    }

    private function order(Seller $seller, Buyer $buyer, string $productName, string $status = Order::STATUS_TO_SHIP): Order
    {
        $product = Product::create(['seller_id' => $seller->user_id, 'name' => $productName,
            'category' => 'Home', 'price' => '100.00', 'stock' => 5,
            'description' => 'Test product', 'status' => 'active']);
        $order = Order::create([
            'seller_id' => $seller->id, 'buyer_id' => $buyer->id,
            'checkout_group_id' => '00000000-0000-4000-8000-000000000001',
            'status' => $status, 'total_amount' => '147.25',
            'shipping_method' => 'standard', 'shipping_fee' => '58.00',
            'payment_method' => 'cod', 'cod_fee' => '0.00',
            'delivery_name' => 'Test Person', 'delivery_address' => '1 Street',
        ]);
        OrderItem::create(['order_id' => $order->id, 'product_id' => $product->id,
            'quantity' => 1, 'price' => '89.25']);
        return $order;
    }
}
