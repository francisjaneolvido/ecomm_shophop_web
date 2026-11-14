<?php

namespace Tests\Feature;

use App\Models\Buyer\Order\OrderItem;
use App\Models\Buyer\Order\Order;
use App\Models\LogisticsPartner;
use App\Models\Buyer\Cart\CartItem;
use App\Models\Seller\Manage_inventory\Product;
use App\Services\BuyerCheckoutService;
use App\Services\LogisticsRoutingService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\Support\SellerWorkspaceFixtures;
use Tests\TestCase;

class ProductVariationManagementTest extends TestCase
{
    use SellerWorkspaceFixtures;

    private function combination(string $color, ?string $size = null, array $extra = []): array
    {
        $attributes = ['Color' => $color];
        if ($size !== null) $attributes['Size'] = $size;

        return array_replace([
            'name' => implode(' / ', array_values($attributes)),
            'attributes' => json_encode($attributes),
            'price' => '199.00',
            'stock' => '5',
            'status' => 'active',
        ], $extra);
    }

    private function tinyImage(string $name): UploadedFile
    {
        return UploadedFile::fake()->createWithContent($name, base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/pN8AAAAASUVORK5CYII='
        ));
    }

    public function test_simple_product_creation_keeps_standard_stock(): void
    {
        $seller = $this->seller();
        $this->actingAs($seller)->post(route('seller.inventory.products.store'), $this->productInput())
            ->assertRedirect(route('seller.inventory'));

        $product = Product::query()->sole();
        $this->assertFalse($product->has_variants);
        $this->assertSame(5, $product->stock);
        $this->assertCount(0, $product->variants);
    }

    public function test_seller_editor_and_buyer_product_page_render_variation_controls(): void
    {
        $seller = $this->seller();
        $product = $this->product($seller, ['has_variants' => true]);
        $product->variants()->create([
            'name' => 'Black / Large', 'attributes' => ['Color' => 'Black', 'Size' => 'Large'],
            'price' => 219, 'stock' => 5, 'status' => 'active',
        ]);

        $this->actingAs($seller)->get(route('seller.inventory'))
            ->assertOk()->assertSee('id="variantAxes"', false)
            ->assertSee('id="legacyVariantConversion"', false)
            ->assertSee('data-variant-image-ref', false);

        $this->get(route('buyer.product.show', $product))
            ->assertOk()->assertSee('data-variant-axis="Color"', false)
            ->assertSee('data-variant-axis="Size"', false)
            ->assertSee('data-variant-image', false);
    }

    public function test_mobile_catalog_returns_the_selected_variants_own_image_and_price(): void
    {
        $seller = $this->seller();
        $buyer = $this->buyer();
        $product = $this->product($seller, ['has_variants' => true, 'stock' => 2]);
        $image = $product->images()->create([
            'image_path' => 'products/black.png', 'is_primary' => false,
        ]);
        $product->variants()->create([
            'name' => 'Black', 'attributes' => ['Color' => 'Black'],
            'image_id' => $image->id, 'price' => 219, 'stock' => 2, 'status' => 'active',
        ]);

        Sanctum::actingAs($buyer);
        $this->getJson('/api/mobile/buyer/products/'.$product->id)
            ->assertOk()
            ->assertJsonPath('data.variants.0.image', asset('storage/products/black.png'))
            ->assertJsonPath('data.variants.0.price', 219)
            ->assertJsonPath('data.variants.0.label', 'Color: Black');
    }

    public function test_one_and_two_dimensions_create_unique_combinations_with_aggregate_stock(): void
    {
        $seller = $this->seller();
        $this->actingAs($seller)->post(route('seller.inventory.products.store'), $this->productInput([
            'has_variants' => '1', 'variants' => [
                $this->combination('Black'), $this->combination('White', null, ['stock' => '2']),
            ],
        ]))->assertRedirect(route('seller.inventory'));
        $oneAxis = Product::query()->sole();
        $this->assertSame(7, $oneAxis->stock);
        $this->assertSame(['Color' => 'Black'], $oneAxis->variants()->first()->attributes);

        $this->actingAs($seller)->post(route('seller.inventory.products.store'), $this->productInput([
            'name' => 'Two-axis shirt', 'has_variants' => '1',
            'variants' => [
                $this->combination('Black', 'Small'), $this->combination('Black', 'Large'),
                $this->combination('White', 'Small'), $this->combination('White', 'Large'),
            ],
        ]))->assertRedirect(route('seller.inventory'));
        $twoAxis = Product::query()->where('name', 'Two-axis shirt')->firstOrFail();
        $this->assertSame(20, $twoAxis->stock);
        $this->assertCount(4, $twoAxis->variants);
        $this->assertSame(4, $twoAxis->variants->pluck('name')->unique()->count());
    }

    public function test_duplicate_combinations_and_foreign_variant_ids_are_rejected(): void
    {
        $seller = $this->seller();
        $product = $this->product($seller);
        $other = $this->product($seller, ['name' => 'Other lamp']);
        $foreignVariant = $other->variants()->create([
            'name' => 'Blue', 'price' => 100, 'stock' => 1, 'status' => 'active',
        ]);

        $this->actingAs($seller)->post(route('seller.inventory.products.store'), $this->productInput([
            'product_id' => $product->id, 'has_variants' => '1',
            'variants' => [$this->combination('Black'), $this->combination('black')],
        ]))->assertSessionHasErrors('variants.1.name');

        $this->actingAs($seller)->post(route('seller.inventory.products.store'), $this->productInput([
            'product_id' => $product->id, 'has_variants' => '1',
            'variants' => [$this->combination('Black', null, ['id' => $foreignVariant->id])],
        ]))->assertSessionHasErrors('variants.0.id');
    }

    public function test_uploaded_gallery_photos_can_be_shared_by_color_and_overridden_by_combination(): void
    {
        Storage::fake('public');
        $seller = $this->seller();
        $this->actingAs($seller)->post(route('seller.inventory.products.store'), $this->productInput([
            'has_variants' => '1',
            'images' => [$this->tinyImage('black.png'), $this->tinyImage('white.png')],
            'variants' => [
                $this->combination('Black', 'Small', ['image_ref' => 'new:0']),
                $this->combination('Black', 'Large', ['image_ref' => 'new:0']),
                $this->combination('White', 'Small', ['image_ref' => 'new:1']),
                $this->combination('White', 'Large', ['image_ref' => 'new:0']),
            ],
        ]))->assertRedirect(route('seller.inventory'));

        $product = Product::query()->sole();
        $variants = $product->variants()->orderBy('id')->get();
        $this->assertSame($variants[0]->image_id, $variants[1]->image_id);
        $this->assertNotSame($variants[0]->image_id, $variants[2]->image_id);
        $this->assertSame($variants[0]->image_id, $variants[3]->image_id);
        $this->assertSame(2, $product->images()->count());
        Storage::disk('public')->assertExists($variants[0]->image->image_path);
    }

    public function test_invalid_upload_and_another_products_photo_are_rejected(): void
    {
        Storage::fake('public');
        $seller = $this->seller();
        $product = $this->product($seller);
        $other = $this->product($seller, ['name' => 'Other lamp']);
        $foreignPhoto = $other->images()->create([
            'image_path' => 'products/other.png', 'is_primary' => true,
        ]);

        $this->actingAs($seller)->post(route('seller.inventory.products.store'), $this->productInput([
            'product_id' => $product->id, 'has_variants' => '1',
            'images' => [UploadedFile::fake()->create('unsafe.txt', 1, 'text/plain')],
            'variants' => [$this->combination('Black', null, ['image_ref' => 'new:0'])],
        ]))->assertSessionHasErrors('images.0');

        $this->actingAs($seller)->post(route('seller.inventory.products.store'), $this->productInput([
            'product_id' => $product->id, 'has_variants' => '1',
            'variants' => [$this->combination('Black', null, [
                'image_ref' => 'existing:'.$foreignPhoto->id,
            ])],
        ]))->assertSessionHasErrors('variants.0.image_ref');
        $this->assertSame(0, $product->variants()->count());
    }

    public function test_shared_photo_is_kept_until_all_variants_are_reassigned(): void
    {
        Storage::fake('public');
        $seller = $this->seller();
        $product = $this->product($seller, ['has_variants' => true]);
        $blackPhoto = $product->images()->create([
            'image_path' => 'products/black.png', 'is_primary' => true,
        ]);
        $whitePhoto = $product->images()->create([
            'image_path' => 'products/white.png', 'is_primary' => false,
        ]);
        Storage::disk('public')->put($blackPhoto->image_path, 'black');
        $variant = $product->variants()->create([
            'name' => 'Black', 'attributes' => ['Color' => 'Black'],
            'image_id' => $blackPhoto->id, 'price' => 199, 'stock' => 5,
            'status' => 'active',
        ]);
        $base = $this->productInput(['product_id' => $product->id, 'has_variants' => '1']);

        $this->actingAs($seller)->post(route('seller.inventory.products.store'), $base + [
            'remove_image_ids' => [$blackPhoto->id],
            'variants' => [$this->combination('Black', null, [
                'id' => $variant->id, 'image_ref' => 'existing:'.$blackPhoto->id,
            ])],
        ])->assertSessionHasErrors('variants.0.image_ref');
        Storage::disk('public')->assertExists($blackPhoto->image_path);

        $this->actingAs($seller)->post(route('seller.inventory.products.store'), $base + [
            'variants' => [$this->combination('Black', null, [
                'id' => $variant->id, 'image_ref' => 'existing:'.$whitePhoto->id,
            ])],
        ])->assertRedirect(route('seller.inventory'));
        $this->assertSame($whitePhoto->id, $variant->fresh()->image_id);

        $this->actingAs($seller)->post(route('seller.inventory.products.store'), $base + [
            'remove_image_ids' => [$blackPhoto->id],
            'variants' => [$this->combination('Black', null, [
                'id' => $variant->id, 'image_ref' => 'existing:'.$whitePhoto->id,
            ])],
        ])->assertRedirect(route('seller.inventory'));
        $this->assertDatabaseMissing('product_images', ['id' => $blackPhoto->id]);
        Storage::disk('public')->assertMissing($blackPhoto->image_path);
    }

    public function test_edit_preserves_existing_combinations_and_archives_removed_ones(): void
    {
        $seller = $this->seller();
        $buyer = $this->buyer();
        $product = $this->product($seller, ['has_variants' => true, 'stock' => 8]);
        $black = $product->variants()->create([
            'name' => 'Black / Small', 'attributes' => ['Color' => 'Black', 'Size' => 'Small'],
            'price' => 199, 'stock' => 5, 'status' => 'active',
        ]);
        $white = $product->variants()->create([
            'name' => 'White / Small', 'attributes' => ['Color' => 'White', 'Size' => 'Small'],
            'price' => 199, 'stock' => 3, 'status' => 'active',
        ]);
        $order = Order::create([
            'buyer_id' => $buyer->buyer->id, 'status' => 'pending', 'total_amount' => 199,
        ]);
        $historical = OrderItem::create([
            'order_id' => $order->id, 'product_id' => $product->id,
            'product_variant_id' => $white->id, 'quantity' => 1, 'price' => 199,
            'variant_label' => 'Color: White · Size: Small',
        ]);

        $this->actingAs($seller)->post(route('seller.inventory.products.store'), $this->productInput([
            'product_id' => $product->id, 'has_variants' => '1',
            'variants' => [$this->combination('Black', 'Small', [
                'id' => $black->id, 'price' => '219.00', 'stock' => '4',
            ])],
        ]))->assertRedirect(route('seller.inventory'));

        $this->assertSame($black->id, $black->fresh()->id);
        $this->assertSame('219.00', $black->fresh()->price);
        $this->assertSame('archived', $white->fresh()->status);
        $this->assertSame(4, $product->fresh()->stock);
        $this->assertSame($white->id, $historical->fresh()->product_variant_id);
        $this->assertSame('Color: White · Size: Small', $historical->fresh()->variantLabel());
    }

    public function test_adding_an_option_preserves_unrelated_variant_prices_stock_and_ids(): void
    {
        $seller = $this->seller();
        $product = $this->product($seller, ['has_variants' => true, 'stock' => 7]);
        $black = $product->variants()->create([
            'name' => 'Black / Small', 'attributes' => ['Color' => 'Black', 'Size' => 'Small'],
            'price' => 199, 'stock' => 5, 'status' => 'active',
        ]);
        $white = $product->variants()->create([
            'name' => 'White / Small', 'attributes' => ['Color' => 'White', 'Size' => 'Small'],
            'price' => 209, 'stock' => 2, 'status' => 'active',
        ]);

        $this->actingAs($seller)->post(route('seller.inventory.products.store'), $this->productInput([
            'product_id' => $product->id, 'has_variants' => '1',
            'variants' => [
                $this->combination('Black', 'Small', ['id' => $black->id, 'price' => 199, 'stock' => 5]),
                $this->combination('White', 'Small', ['id' => $white->id, 'price' => 209, 'stock' => 2]),
                $this->combination('Black', 'Large', ['price' => 229, 'stock' => 0]),
                $this->combination('White', 'Large', ['price' => 239, 'stock' => 0]),
            ],
        ]))->assertRedirect(route('seller.inventory'));

        $this->assertSame('199.00', $black->fresh()->price);
        $this->assertSame(5, $black->fresh()->stock);
        $this->assertSame('209.00', $white->fresh()->price);
        $this->assertSame(2, $white->fresh()->stock);
        $this->assertSame('active', $white->fresh()->status);
        $this->assertCount(4, $product->variants);
        $this->assertSame(7, $product->fresh()->stock);
    }

    public function test_buyer_cart_keeps_variants_as_distinct_lines_and_rejects_out_of_stock_choice(): void
    {
        $seller = $this->seller();
        $buyer = $this->buyer();
        $product = $this->product($seller, ['has_variants' => true, 'stock' => 5]);
        $black = $product->variants()->create([
            'name' => 'Black', 'attributes' => ['Color' => 'Black'],
            'price' => 199, 'stock' => 3, 'status' => 'active',
        ]);
        $white = $product->variants()->create([
            'name' => 'White', 'attributes' => ['Color' => 'White'],
            'price' => 219, 'stock' => 2, 'status' => 'active',
        ]);
        $soldOut = $product->variants()->create([
            'name' => 'Blue', 'attributes' => ['Color' => 'Blue'],
            'price' => 229, 'stock' => 0, 'status' => 'active',
        ]);

        foreach ([$black, $white] as $variant) {
            $this->actingAs($buyer)->postJson(route('buyer.cart.add'), [
                'product_id' => $product->id, 'variant_id' => $variant->id, 'qty' => 1,
            ])->assertOk();
        }
        $lines = CartItem::query()->where('buyer_id', $buyer->buyer->id)->orderBy('id')->get();
        $this->assertCount(2, $lines);
        $this->assertSame([$black->id, $white->id], $lines->pluck('product_variant_id')->all());
        $this->assertSame([199.0, 219.0], $lines->map(fn ($line) => $line->unitPrice())->all());

        $this->actingAs($buyer)->postJson(route('buyer.cart.add'), [
            'product_id' => $product->id, 'variant_id' => $soldOut->id, 'qty' => 1,
        ])->assertUnprocessable();
        $this->assertSame(2, CartItem::query()->count());
    }

    public function test_checkout_uses_database_variant_price_and_decrements_only_the_purchased_combination(): void
    {
        $seller = $this->seller();
        $buyer = $this->buyer();
        $product = $this->product($seller, ['has_variants' => true, 'stock' => 8]);
        $blackPhoto = $product->images()->create([
            'image_path' => 'products/checkout-black.png', 'is_primary' => false,
        ]);
        $black = $product->variants()->create([
            'name' => 'Black', 'attributes' => ['Color' => 'Black'],
            'image_id' => $blackPhoto->id, 'price' => 219, 'stock' => 5, 'status' => 'active',
        ]);
        $white = $product->variants()->create([
            'name' => 'White', 'attributes' => ['Color' => 'White'],
            'price' => 199, 'stock' => 3, 'status' => 'active',
        ]);
        $line = CartItem::create([
            'buyer_id' => $buyer->buyer->id, 'product_id' => $product->id,
            'product_variant_id' => $black->id, 'quantity' => 2,
        ]);
        $partnerUser = $this->user('logistics');
        $partner = LogisticsPartner::create([
            'user_id' => $partnerUser->id, 'agreement_rep_name' => 'Test Rep',
            'agreement_date' => '2026-11-01', 'agreement_signature_path' => 'test/signature.png',
            'company_name' => 'Test Courier', 'business_registration_no' => 'TEST-001',
            'line_of_business' => 'motorcycle_courier', 'rep_valid_id_path' => 'test/id.png',
            'rep_id_number' => 'TEST-ID', 'rep_sex' => 'male', 'rep_birthday' => '1990-01-01',
            'contact_no' => '09171234567', 'region' => 'CALABARZON', 'province' => 'Laguna',
            'municipality' => 'Calamba', 'barangay' => 'Real', 'street_no' => '1',
            'unit_no' => 'A', 'business_permit_path' => 'test/permit.png',
        ]);
        $this->mock(LogisticsRoutingService::class)
            ->shouldReceive('eligiblePartnersFor')->once()->andReturn(collect([$partner]));

        app(BuyerCheckoutService::class)->placeOrder($buyer->buyer, [
            'items' => [$line->id => ['quantity' => 2, 'price' => 1]],
            'shipping_method' => [$seller->seller->id => 'standard'],
            'payment_method' => 'cod',
        ]);

        $item = OrderItem::query()->sole();
        $this->assertSame('219.00', $item->price);
        $this->assertSame($black->id, $item->product_variant_id);
        $this->assertSame('Color: Black', $item->variantLabel());
        $this->assertSame('products/checkout-black.png', $item->variant_image_path);
        $this->assertSame(3, $black->fresh()->stock);
        $this->assertSame(3, $white->fresh()->stock);
        $this->assertSame(6, $product->fresh()->stock);
    }
}
