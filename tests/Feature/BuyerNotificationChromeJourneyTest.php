<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

// Authenticated Buyer HTML is the notification contract; email verification is a separate workflow.
class BuyerNotificationChromeJourneyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Refuse destructive fixture setup unless the configured and opened database are isolated.
        $this->assertSame('sqlite', DB::getDefaultConnection());
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        Schema::dropAllTables();
        foreach (['0001_01_01_000000_create_users_table.php',
            '2026_08_31_000001_add_account_type_and_status_to_users_table.php',
            '2026_08_29_000001_create_buyers_table.php'] as $migration) {
            (require database_path('migrations/'.$migration))->up();
        }
    }

    // A nonexistent feed must not manufacture shipment, voucher or Wishlist facts for an authenticated account.
    public function test_approved_buyer_is_not_shown_fabricated_notification_records(): void
    {
        $response = $this->actingAs($this->user())->get('/buyer/dashboard')->assertOk();
        $document = new \DOMDocument();
        @$document->loadHTML($response->getContent());
        $xpath = new \DOMXPath($document);
        $this->assertSame(0, $xpath->query('//*[@data-notification-item]')->length,
            'Buyer chrome must not present fabricated business notifications as live account records.');
    }

    // Availability must replace account-looking unread state, dead destinations and fake mark-read actions across shared pages.
    public function test_shared_chrome_discloses_unavailability_and_preserves_buyer_navigation(): void
    {
        $this->actingAs($this->user());
        foreach (['/buyer/dashboard', '/buyer/messages'] as $path) {
            $response = $this->get($path)->assertOk()->assertSee('No notification feed is available yet.');
            $document = new \DOMDocument();
            @$document->loadHTML($response->getContent());
            $xpath = new \DOMXPath($document);
            $menu = $xpath->query('//*[@data-notification-menu]');
            $this->assertSame(1, $menu->length);
            $this->assertStringContainsString('Notifications unavailable', $menu->item(0)->textContent);
            $this->assertSame(0, $xpath->query('//*[@data-notification-badge or @data-mark-all-read or @data-unread-dot]')->length);
            $this->assertSame(0, $xpath->query('//*[@data-notification-menu]//a | //*[@data-notification-menu]//button[not(@data-hover-menu-toggle)]')->length);
            $this->assertSame(1, $xpath->query('//*[@data-mobile-menu-panel]//span[@data-notifications-unavailable]')->length);
            $this->assertSame(0, $xpath->query('//*[@data-mobile-menu-panel]//a[contains(., "Notifications")]')->length);
            foreach (['Order shipped', 'New voucher available', 'Price dropped', 'View all notifications', 'markAllReadButton'] as $falsePromise) {
                $response->assertDontSee($falsePromise);
            }
            foreach (['buyer.dashboard', 'buyer.cart', 'buyer.messages', 'buyer.profile'] as $route) {
                $this->assertGreaterThan(0, $xpath->query('//header//a[@href="'.route($route).'"]')->length);
            }
            $this->assertGreaterThan(0, $xpath->query('//header//form[@action="'.route('logout').'"]')->length);
        }
    }

    // The representative shared-chrome route retains normal guest, approval and Buyer email-verification gates.
    public function test_guest_and_unapproved_or_unverified_buyers_are_denied(): void
    {
        $this->get('/buyer/dashboard')->assertRedirect(route('login'));
        foreach (['pending', 'rejected', 'suspended'] as $status) {
            $this->actingAs($this->user(['status' => $status]))->get('/buyer/dashboard')
                ->assertRedirect(route('home'))->assertSessionHas('login_notice');
            $this->assertGuest('web');
        }
        $this->actingAs($this->user(['email_verified_at' => null]))->get('/buyer/dashboard')
            ->assertRedirect(route('buyer.verify-email.show'))->assertSessionHas('buyer_verification_user_id');
        $this->assertGuest('web');
    }

    // Local TEST renders the same informational chrome without granting the real Buyer identity.
    public function test_local_preview_has_the_same_limits_and_cannot_authorize_buyer_access(): void
    {
        $this->get('/__dev/preview/buyer/messages')->assertNotFound();
        $this->app->detectEnvironment(fn () => 'local');
        \Illuminate\Support\Facades\Route::middleware('web')->group(base_path('routes/debug.php'));
        $this->app['router']->getRoutes()->refreshNameLookups();
        $this->get('/__dev/preview/buyer/messages')->assertOk()
            ->assertSee('Notifications unavailable')->assertSee('No notification feed is available yet.')
            ->assertDontSee('data-notification-item')->assertDontSee('data-notification-badge');
        $this->assertGuest('web');
        $this->get('/buyer/dashboard')->assertRedirect(route('login'));
    }

    // Users establish access only; no notification, business or read-state fixtures are created.
    private function user(array $overrides = []): User
    {
        $user = new User();
        $user->forceFill(array_replace(['email' => uniqid('buyer').'@notifications.test',
            'password' => 'NotificationFixture123!', 'account_type' => 'buyer',
            'status' => 'approved', 'email_verified_at' => now()], $overrides))->save();

        return $user;
    }
}
