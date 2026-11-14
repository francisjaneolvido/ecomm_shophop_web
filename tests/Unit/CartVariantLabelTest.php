<?php

namespace Tests\Unit;

use App\Models\Buyer\Cart\CartItem;
use App\Models\Seller\Manage_inventory\ProductVariant;
use Tests\TestCase;

class CartVariantLabelTest extends TestCase
{
    public function test_variant_name_is_used_without_exposing_raw_model_columns(): void
    {
        $variant = new ProductVariant;
        $variant->forceFill(['id' => 1, 'product_id' => 2, 'name' => 'Black', 'price' => 700,
            'weight_kg' => 0.050, 'stock' => 5, 'status' => 'active', 'created_at' => '2026-11-07 23:03:58']);
        $cart = new CartItem;
        $cart->setRelation('variant', $variant);
        $this->assertSame('Black', $cart->variantLabel());
    }

    public function test_customer_choice_attributes_are_labelled_and_metadata_is_excluded(): void
    {
        $variant = new ProductVariant;
        $variant->forceFill(['name' => 'Black / Medium', 'attributes' => [
            'color' => 'Black', 'size' => 'Medium', 'id' => 12, 'price' => 700,
            'status' => 'active', 'created_at' => '2026-11-07', 'internal' => ['raw' => 'value'],
        ]]);
        $cart = new CartItem;
        $cart->setRelation('variant', $variant);
        $this->assertSame('Color: Black · Size: Medium', $cart->variantLabel());
    }

    public function test_products_without_variants_keep_the_existing_standard_fallback(): void
    {
        $cart = new CartItem;
        $cart->setRelation('variant', null);
        $this->assertSame('Standard', $cart->variantLabel());
    }
}
