<?php

namespace Tests\Feature;

use App\Models\Seller;
use App\Models\Buyer;
use App\Models\Buyer\Favorite\Favorite;
use App\Models\Seller\Manage_inventory\Product;
use App\Models\Seller\Manage_inventory\ProductImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublicShopTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
    }

    public function test_shop_search_and_public_page_only_show_approved_available_listings(): void
    {
        $shop = $this->shop('Adidas Finds');
        $visible = $this->product($shop, 'Running Shoes');
        $this->product($shop, 'Hidden Shoes', 'rejected');
        $this->get(route('search.index', ['search' => 'adidas']))
            ->assertOk()->assertSee('Matching shops')->assertSee('Adidas Finds')
            ->assertSee(route('shop.show', $shop), false);
        $this->get(route('search.suggestions', ['q' => 'adidas']))
            ->assertOk()->assertJsonPath('shops.0.name', 'Adidas Finds');
        $this->get(route('shop.show', $shop))->assertOk()
            ->assertSee($visible->name)->assertDontSee('Hidden Shoes')
            ->assertSee('sh-shop-cover-card')->assertSee('sh-shop-tabs')
            ->assertSee('All Products');
    }

    public function test_shop_uses_an_approved_product_photo_for_the_blurred_cover(): void
    {
        $shop = $this->shop('Cover Shop');
        $visible = $this->product($shop, 'Canvas Backpack');
        ProductImage::create(['product_id' => $visible->id, 'image_path' => 'products/cover-photo.jpg', 'is_primary' => true]);
        $hidden = $this->product($shop, 'Rejected Backpack', 'rejected');
        ProductImage::create(['product_id' => $hidden->id, 'image_path' => 'products/hidden-photo.jpg', 'is_primary' => true]);

        $this->get(route('shop.show', $shop))->assertOk()
            ->assertSee('class="sh-shop-cover-photo"', false)
            ->assertSee(asset('storage/products/cover-photo.jpg'), false)
            ->assertDontSee('hidden-photo.jpg');
    }

    public function test_pending_seller_has_no_public_shop_page(): void
    {
        $shop = $this->shop('Pending Shop');
        $this->product($shop, 'Visible stock');
        $shop->user->forceFill(['status' => 'pending'])->save();
        $this->get(route('shop.show', $shop))->assertNotFound();
        $this->get(route('search.index', ['search' => 'Pending Shop']))->assertDontSee('Matching shops');
    }

    public function test_seller_can_upload_and_remove_own_shop_logo(): void
    {
        Storage::fake('public');
        $shop = $this->shop('Logo Shop');
        $this->actingAs($shop->user)->patch(route('seller.shop-profile.logo'), [
            'logo' => UploadedFile::fake()->createWithContent('logo.png', base64_decode(
                'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jR9sAAAAASUVORK5CYII='
            )),
        ])->assertRedirect();
        $path = $shop->fresh()->logo_path;
        $this->assertNotNull($path);
        Storage::disk('public')->assertExists($path);
        $this->get(route('shop.show', $shop))->assertOk()->assertSee($shop->fresh()->logo_url, false);
        $this->patch(route('seller.shop-profile.logo'), ['remove_logo' => 1])->assertRedirect();
        $this->assertNull($shop->fresh()->logo_path);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_invalid_shop_logo_is_rejected_without_changing_profile(): void
    {
        Storage::fake('public');
        $shop = $this->shop('Safe Shop');
        $this->actingAs($shop->user)->from(route('seller.shop-profile'))
            ->patch(route('seller.shop-profile.logo'), ['logo' => UploadedFile::fake()->create('malware.txt', 3, 'text/plain')])
            ->assertRedirect(route('seller.shop-profile'))->assertSessionHasErrors('logo');
        $this->assertNull($shop->fresh()->logo_path);
    }

    public function test_mobile_buyer_shop_uses_the_web_sellers_approved_products_and_logo(): void
    {
        $shop = $this->shop('Mobile Shop');
        $shop->update(['logo_path' => 'shop-logos/mobile.png']);
        $visible = $this->product($shop, 'Mobile Shoes');
        ProductImage::create(['product_id' => $visible->id, 'image_path' => 'products/mobile-cover.jpg', 'is_primary' => true]);
        $this->product($shop, 'Rejected Shoes', 'rejected');
        $buyerUser = User::forceCreate(['email' => uniqid().'@mobile-buyer.test', 'password' => bcrypt('Buyer123!'),
            'account_type' => 'buyer', 'status' => 'approved', 'email_verified_at' => now()]);
        Buyer::create(['user_id' => $buyerUser->id, 'first_name' => 'Mobile', 'last_name' => 'Buyer',
            'sex' => 'Female', 'contact_no' => '09123456789', 'birthday' => '1990-01-01',
            'province_code' => '01', 'province_name' => 'Laguna', 'municipality_code' => '01',
            'municipality_name' => 'Calamba', 'barangay_code' => '01', 'barangay_name' => 'Crossing',
            'street_address' => '1 Test Street', 'valid_id_path' => 'buyer.jpg']);

        $this->actingAs($buyerUser)->getJson('/api/mobile/buyer/shops/'.$shop->id)
            ->assertOk()->assertJsonPath('data.business_name', 'Mobile Shop')
            ->assertJsonPath('data.logo_url', $shop->logo_url)
            ->assertJsonPath('data.cover_url', asset('storage/products/mobile-cover.jpg'))
            ->assertJsonPath('data.products.0.id', $visible->id)
            ->assertJsonMissing(['name' => 'Rejected Shoes']);
    }

    public function test_mobile_seller_can_upload_the_same_logo_shown_on_the_web_shop(): void
    {
        Storage::fake('public');
        $shop = $this->shop('Mobile Logo Shop');
        $response = $this->actingAs($shop->user)->post('/api/mobile/seller/profile/logo', [
            'logo' => UploadedFile::fake()->createWithContent('mobile-logo.png', base64_decode(
                'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jR9sAAAAASUVORK5CYII='
            )),
        ], ['Accept' => 'application/json']);
        $response->assertOk()->assertJsonPath('data.logo_url', $shop->fresh()->logo_url);
        $this->assertNotNull($shop->fresh()->logo_path);
        $path = $shop->fresh()->logo_path;
        Storage::disk('public')->assertExists($path);
        $this->post('/api/mobile/seller/profile/logo', [
            'logo' => UploadedFile::fake()->create('invalid.txt', 1, 'text/plain'),
        ], ['Accept' => 'application/json'])->assertUnprocessable();
        $this->assertSame($path, $shop->fresh()->logo_path);
        $this->post('/api/mobile/seller/profile/logo', ['remove_logo' => 1],
            ['Accept' => 'application/json'])->assertOk()->assertJsonPath('data.logo_url', null);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_mobile_product_search_also_finds_approved_shops_by_business_name(): void
    {
        $shop = $this->shop('Adidas Finds');
        $shop->update(['logo_path' => 'shop-logos/adidas.png']);
        $this->product($shop, 'Running Shoes');
        $hidden = $this->shop('Adidas Hidden');
        $hidden->user->forceFill(['status' => 'pending'])->save();

        $this->actingAs($this->mobileBuyer())->getJson('/api/mobile/buyer/products?q=Adidas')
            ->assertOk()->assertJsonPath('data.shops.0.id', $shop->id)
            ->assertJsonPath('data.shops.0.logo_url', $shop->logo_url)
            ->assertJsonMissing(['business_name' => 'Adidas Hidden']);
        $this->getJson('/api/mobile/buyer/products?q=%20%20')
            ->assertOk()->assertJsonCount(0, 'data.shops');
    }

    public function test_buyer_product_likes_and_cart_link_back_to_the_real_shop(): void
    {
        $shop = $this->shop('Buyer Shop');
        $product = $this->product($shop, 'Canvas Shoes');
        $user = User::forceCreate(['email' => uniqid().'@buyer.test', 'password' => bcrypt('Buyer123!'),
            'account_type' => 'buyer', 'status' => 'approved', 'email_verified_at' => now()]);
        $buyer = Buyer::create(['user_id' => $user->id, 'first_name' => 'Test', 'last_name' => 'Buyer',
            'sex' => 'Female', 'contact_no' => '09123456789', 'birthday' => '1990-01-01',
            'province_code' => '01', 'province_name' => 'Laguna', 'municipality_code' => '01',
            'municipality_name' => 'Calamba', 'barangay_code' => '01', 'barangay_name' => 'Crossing',
            'street_address' => '1 Test Street', 'valid_id_path' => 'buyer.jpg']);
        Favorite::create(['buyer_id' => $buyer->id, 'product_id' => $product->id]);
        $this->actingAs($user)->get(route('buyer.product.show', $product))
            ->assertOk()->assertSee('data-favorite-toggle', false)
            ->assertSee(route('shop.show', $shop), false);
        $this->get(route('buyer.likes'))->assertOk()->assertSee(route('shop.show', $shop), false);
        $this->postJson(route('buyer.cart.add'), ['product_id' => $product->id, 'qty' => 1])->assertOk();
        $this->get(route('buyer.cart'))->assertOk()->assertSee(route('shop.show', $shop), false)
            ->assertSee('cartRemoveDialog');
    }

    private function shop(string $name): Seller
    {
        $user = User::forceCreate(['email' => uniqid().'@shops.test', 'password' => bcrypt('Shop123!'),
            'account_type' => 'seller', 'status' => 'approved', 'email_verified_at' => now()]);
        return Seller::create(['user_id' => $user->id, 'first_name' => 'Shop', 'last_name' => 'Owner',
            'sex' => 'Male', 'contact_no' => '09123456789', 'birthday' => '1990-01-01',
            'province_code' => '01', 'province_name' => 'Laguna', 'municipality_code' => '01',
            'municipality_name' => 'Calamba', 'barangay_code' => '01', 'barangay_name' => 'Crossing',
            'street_address' => '1 Test Street', 'business_name' => $name, 'business_category' => 'Fashion',
            'valid_id_path' => 'test.jpg', 'business_permit_path' => 'permit.jpg']);
    }

    private function mobileBuyer(): User
    {
        $user = User::forceCreate(['email' => uniqid().'@shop-search.test', 'password' => bcrypt('Buyer123!'),
            'account_type' => 'buyer', 'status' => 'approved', 'email_verified_at' => now()]);
        Buyer::create(['user_id' => $user->id, 'first_name' => 'Search', 'last_name' => 'Buyer',
            'sex' => 'Female', 'contact_no' => '09123456789', 'birthday' => '1990-01-01',
            'province_code' => '01', 'province_name' => 'Laguna', 'municipality_code' => '01',
            'municipality_name' => 'Calamba', 'barangay_code' => '01', 'barangay_name' => 'Crossing',
            'street_address' => '1 Test Street', 'valid_id_path' => 'buyer.jpg']);
        return $user;
    }

    private function product(Seller $shop, string $name, string $compliance = 'approved'): Product
    {
        return Product::create(['seller_id' => $shop->user_id, 'name' => $name,
            'category' => 'Fashion', 'price' => 399, 'stock' => 5,
            'status' => 'active', 'compliance_status' => $compliance]);
    }
}
