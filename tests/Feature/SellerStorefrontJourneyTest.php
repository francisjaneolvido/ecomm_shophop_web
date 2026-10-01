<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

// Storefront is an approved-Seller informational HTTP destination, not a separate shop-management domain.
class SellerStorefrontJourneyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Refuse destructive fixture setup if PHPUnit loses its disposable in-memory connection.
        $this->assertSame('sqlite', DB::getDefaultConnection());
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        Schema::dropAllTables();
        foreach ([
            '0001_01_01_000000_create_users_table.php',
            '2026_08_31_000001_add_account_type_and_status_to_users_table.php',
        ] as $migration) {
            (require database_path('migrations/'.$migration))->up();
        }
    }

    // Existing Seller/Product persistence must not be required or represented as a Storefront CMS.
    public function test_approved_seller_reaches_truthful_availability_without_storefront_management(): void
    {
        $response = $this->actingAs($this->user())->get(route('seller.storefront'));
        $response->assertOk()
            ->assertSee('Storefront management is not available yet.')
            ->assertSee('Shop customization and publishing are unavailable.')
            ->assertSee('Manage products in Inventory and your core profile in Account.')
            ->assertSee('Storefront availability')
            ->assertDontSee('View Storefront')
            ->assertDontSee('Manage your live storefront')
            ->assertDontSee('Customize your store')
            ->assertDontSee('Publish shop changes')
            ->assertDontSee('Followers')
            ->assertDontSee('Claimed vouchers')
            ->assertDontSee('Store analytics')
            ->assertDontSee('Merchant verification badge');

        // Shared-shell actions remain legitimate; the Storefront content itself must have no fake controls or figures.
        $document = new \DOMDocument();
        @$document->loadHTML($response->getContent());
        $xpath = new \DOMXPath($document);
        $this->assertSame(1, $xpath->query('//*[@id="sellerStorefront"]')->length);
        $this->assertSame(0, $xpath->query('//*[@id="sellerStorefront"]//*[self::form or self::button or self::input or self::select or self::textarea or self::table or self::script or self::a]')->length);
    }

    // Informational content does not weaken the normal Seller destination's guest boundary.
    public function test_guest_must_sign_in_before_opening_storefront(): void
    {
        $this->get(route('seller.storefront'))->assertRedirect(route('login'));
    }

    // Other marketplace roles cannot enter the Seller portal even though this surface has no management actions.
    public function test_other_roles_cannot_open_seller_storefront(): void
    {
        foreach (['buyer', 'admin', 'logistics'] as $role) {
            $this->actingAs($this->user(['account_type' => $role]))->get(route('seller.storefront'))
                ->assertForbidden();
        }
    }

    // Existing review denials must log the Seller out rather than expose the portal as an availability bypass.
    public function test_unapproved_sellers_remain_denied(): void
    {
        foreach (['pending', 'rejected', 'suspended'] as $status) {
            $this->actingAs($this->user(['status' => $status]))->get(route('seller.storefront'))
                ->assertRedirect(route('home'))->assertSessionHas('login_notice');
            $this->assertGuest();
        }
    }

    // Approval alone cannot bypass the Seller email-verification requirement.
    public function test_unverified_seller_is_sent_to_email_verification(): void
    {
        $this->actingAs($this->user(['email_verified_at' => null]))->get(route('seller.storefront'))
            ->assertRedirect(route('seller.verify-email.show'));
        $this->assertGuest();
    }

    // An informational GET cannot accept shop updates or report fabricated publishing success.
    public function test_storefront_does_not_accept_management_writes(): void
    {
        $this->actingAs($this->user());
        foreach (['post', 'patch', 'delete'] as $method) {
            $this->{$method}(route('seller.storefront'), ['business_name' => 'Unsupported change', 'publish' => true])
                ->assertStatus(405)->assertSessionMissing('status');
        }
    }

    // Local TEST can preview the same limitation copy; it supplies no shop data and does not grant production access.
    public function test_local_preview_is_informational_and_keeps_normal_destination_protected(): void
    {
        $this->app->detectEnvironment(fn () => 'local');
        \Illuminate\Support\Facades\Route::middleware('web')->group(base_path('routes/debug.php'));
        // Manual post-bootstrap route loading needs Laravel's normal named-route lookup refresh for the local shell.
        $this->app['router']->getRoutes()->refreshNameLookups();
        $this->get('/__dev/preview/seller/storefront')->assertOk()
            ->assertSee('Storefront management is not available yet.')
            ->assertDontSee('Publish shop changes');
        $this->get(route('seller.storefront'))->assertRedirect(route('login'));
    }

    // Users establish the real authorization boundary without inventing shop records or reseeding normal accounts.
    private function user(array $overrides = []): User
    {
        $user = new User();
        $user->forceFill(array_replace(['email' => uniqid('seller').'@storefront.test',
            'password' => 'StorefrontFixture123!', 'account_type' => 'seller',
            'status' => 'approved', 'email_verified_at' => now()], $overrides))->save();

        return $user;
    }
}
