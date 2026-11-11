<?php

namespace Tests\Feature;

use App\Models\Seller;
use App\Models\Seller\Manage_inventory\Product;
use App\Models\Seller\Manage_inventory\Voucher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Run against a dedicated sqlite :memory: test DB, never a real store DB. */
class SellerPromotionsWorkspaceRegressionTest extends TestCase
{
    use RefreshDatabase;

    public function test_promotion_directory_is_paginated_seller_scoped_and_filterable(): void
    {
        $owner = $this->seller('owner@promo.test');
        $other = $this->seller('other@promo.test');
        foreach (range(1, 28) as $n) {
            $this->voucher($owner, sprintf('Own Voucher %02d', $n), sprintf('OWN%02d', $n));
        }
        $this->voucher($other, 'Foreign Secret Voucher', 'OTHER');
        $first = $this->actingAs($owner)->get(route('seller.promotions'));
        $first->assertOk()->assertSee('Own Voucher 28')->assertDontSee('Foreign Secret Voucher');
        $this->assertSame(25, substr_count($first->getContent(), 'data-voucher-open data-voucher='));
        $second = $this->get(route('seller.promotions', ['page' => 2]));
        $second->assertOk()->assertSee('Own Voucher 01')->assertDontSee('Foreign Secret Voucher');
        $search = $this->get(route('seller.promotions', ['q' => 'OWN07']));
        $search->assertOk()->assertSee('Own Voucher 07')->assertDontSee('Own Voucher 05');
    }

    public function test_product_lookup_and_voucher_mutations_respect_seller_ownership(): void
    {
        $owner = $this->seller('owner2@promo.test');
        $other = $this->seller('other2@promo.test');
        $this->product($owner, 'My Headphones');
        $foreign = $this->product($other, 'Other Seller Headphones');
        $lookup = $this->actingAs($owner)->getJson(route('seller.promotions.products', ['q' => 'Headphones']));
        $lookup->assertOk()->assertSee('My Headphones')->assertDontSee('Other Seller Headphones');
        $this->post(route('seller.inventory.vouchers.store'), [
            'name' => 'New offer', 'code' => 'HELLO10', 'kind' => 'merchandise',
            'type' => 'percent', 'value' => 10, 'product_ids' => [$foreign->id], 'return_to' => 'promotions',
        ])->assertForbidden();
        $this->post(route('seller.inventory.vouchers.store'), [
            'name' => 'New offer', 'code' => 'HELLO10', 'kind' => 'merchandise',
            'type' => 'percent', 'value' => 200, 'return_to' => 'promotions',
        ])->assertSessionHasErrors('value');
        $this->post(route('seller.inventory.vouchers.store'), [
            'name' => 'New offer', 'code' => 'HELLO10', 'kind' => 'merchandise',
            'type' => 'percent', 'value' => 10, 'return_to' => 'promotions',
        ])->assertRedirect(route('seller.promotions'));
        $this->assertDatabaseHas('vouchers', ['code' => 'HELLO10', 'seller_id' => $owner->id]);
    }

    public function test_shop_profile_is_read_only_business_information_for_owner(): void
    {
        $owner = $this->seller('shopowner@promo.test');
        Seller::create([
            'user_id' => $owner->id, 'first_name' => 'Seller', 'last_name' => 'Owner', 'sex' => 'Female',
            'contact_no' => '09123456789', 'birthday' => '2000-01-01',
            'province_code' => '0434', 'province_name' => 'Laguna', 'municipality_code' => '043404',
            'municipality_name' => 'Pila', 'barangay_code' => '043404001', 'barangay_name' => 'San Antonio',
            'street_address' => 'Main Street', 'business_name' => 'Owner Test Shop',
            'business_category' => 'Home & Living', 'valid_id_path' => 'testing/id.jpg', 'business_permit_path' => 'testing/permit.jpg',
        ]);
        $this->actingAs($owner)->get(route('seller.shop-profile'))->assertOk()
            ->assertSee('Owner Test Shop')->assertSee('San Antonio')
            ->assertSee('Admin-supported approval process');
    }

    private function seller(string $email): User
    {
        $user = new User;
        $user->forceFill(['email' => $email, 'password' => 'test-only', 'account_type' => 'seller',
            'status' => 'approved', 'email_verified_at' => now()])->save();

        return $user;
    }

    private function voucher(User $user, string $name, string $code): Voucher
    {
        return Voucher::create(['seller_id' => $user->id, 'name' => $name, 'code' => $code, 'kind' => 'merchandise',
            'type' => 'percent', 'value' => 10, 'status' => 'active', 'min_order_amount' => 0]);
    }

    private function product(User $user, string $name): Product
    {
        return Product::create(['seller_id' => $user->id, 'name' => $name, 'sku' => 'P'.$user->id.str_replace(' ', '', $name),
            'category' => 'Home & Living', 'subcategory' => 'Home Decor', 'price' => 500, 'stock' => 5,
            'status' => 'active', 'compliance_status' => 'approved']);
    }
}
