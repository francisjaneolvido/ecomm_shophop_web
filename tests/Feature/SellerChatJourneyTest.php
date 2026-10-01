<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

// The Seller Chat HTTP destination must not advertise delivery without a messaging backend.
class SellerChatJourneyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Destructive fixture setup is allowed only on PHPUnit's isolated in-memory connection.
        $this->assertSame('sqlite', DB::getDefaultConnection());
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        Schema::dropAllTables();
        foreach (['0001_01_01_000000_create_users_table.php',
            '2026_08_31_000001_add_account_type_and_status_to_users_table.php'] as $migration) {
            (require database_path('migrations/'.$migration))->up();
        }
    }

    // Availability must replace delivery controls and all fabricated account activity at the protected HTTP seam.
    public function test_approved_seller_is_not_offered_unsupported_message_delivery(): void
    {
        $response = $this->actingAs($this->user())->get('/seller/chat')->assertOk();
        $document = new \DOMDocument();
        @$document->loadHTML($response->getContent());
        $xpath = new \DOMXPath($document);
        $this->assertSame(0, $xpath->query('//button[normalize-space(.)="Send"]')->length,
            'Seller Chat must not offer Send when messages cannot be delivered or stored.');
        foreach (['Live messaging is unavailable.', 'No live conversations or account message history are displayed.',
            'Messages are not sent or stored here.', 'Conversation history is not persisted here.',
            'Read/unread and archive state are unavailable.', 'Attachments cannot be uploaded or stored here.',
            'Blocking and reporting users or conversations are unavailable.',
            'Delivery/read receipts and live presence are unavailable.'] as $limit) {
            $response->assertSee($limit);
        }
        $content = $xpath->query('//*[@id="sellerChat"]');
        $this->assertSame(1, $content->length);
        $this->assertSame(0, $xpath->query('//*[@id="sellerChat"]//*[self::form or self::button or self::input or self::select or self::textarea or self::script]')->length);
        foreach (['Maricel Santos', 'Jonas Villareal', 'Ella Marasigan', 'Anna Reyes', 'ShopHop Admin',
            'ORD-10231', 'Online now', 'Active 1h ago', 'Just now', 'Conversation archived.',
            'has been blocked', 'Reported to ShopHop Admin'] as $falseFact) {
            $this->assertStringNotContainsString($falseFact, $content->item(0)->textContent);
        }
        $this->assertGreaterThan(0, $xpath->query('//a[@href="'.route('seller.chat').'"]')->length);
    }

    // The stable destination still requires normal Seller authentication.
    public function test_guest_cannot_open_seller_messages(): void
    {
        $this->get('/seller/chat')->assertRedirect(route('login'));
    }

    // Existing role, approval and email-verification semantics remain authoritative.
    public function test_wrong_roles_and_unapproved_or_unverified_sellers_are_denied(): void
    {
        foreach (['buyer', 'admin', 'logistics'] as $role) {
            $this->actingAs($this->user(['account_type' => $role]))->get('/seller/chat')->assertForbidden();
        }
        foreach (['pending', 'rejected', 'suspended'] as $status) {
            $this->actingAs($this->user(['status' => $status]))->get('/seller/chat')
                ->assertRedirect(route('home'))->assertSessionHas('login_notice');
            $this->assertGuest('web');
        }
        $this->actingAs($this->user(['email_verified_at' => null]))->get('/seller/chat')
            ->assertRedirect(route('seller.verify-email.show'))->assertSessionHas('seller_verification_user_id');
        $this->assertGuest('web');
    }

    // A separately authenticated Rider receives no marketplace Seller authorization.
    public function test_rider_guard_cannot_open_seller_messages(): void
    {
        $rider = new \App\Models\Logistics\Rider();
        $rider->forceFill(['id' => 1]);
        $this->app['auth']->guard('rider')->setUser($rider);
        $this->assertTrue($this->app['auth']->guard('rider')->check());
        $this->assertFalse($this->app['auth']->guard('web')->check());
        $this->get('/seller/chat')->assertRedirect(route('login'));
    }

    // No compatibility action may imply delivery, read-state mutation, attachment storage or conversation deletion.
    public function test_unsupported_message_operations_fail(): void
    {
        $this->actingAs($this->user());
        $before = DB::table('users')->orderBy('id')->get()->toJson();
        foreach (['post', 'patch', 'delete'] as $method) {
            $this->{$method}('/seller/chat')->assertStatus(405)->assertSessionMissing('status');
        }
        foreach ([['post', 'send'], ['post', '1/messages'], ['patch', '1/read'],
            ['post', '1/attachments'], ['patch', '1/archive'], ['post', '1/block'],
            ['post', '1/report'], ['delete', '1']] as [$method, $path]) {
            $this->{$method}('/seller/chat/'.$path)->assertNotFound()->assertSessionMissing('status');
        }
        $this->assertSame($before, DB::table('users')->orderBy('id')->get()->toJson());
    }

    // Legacy fixture filters cannot revive history, presence or apparent delivery.
    public function test_legacy_filters_remain_informational(): void
    {
        $this->actingAs($this->user());
        foreach (['all', 'unread', 'archived'] as $filter) {
            $this->get('/seller/chat?filter='.$filter.'&search=Maricel&conversation=1&send=1')
                ->assertOk()->assertSee('Live messaging is unavailable.')
                ->assertDontSee('Maricel Santos')->assertDontSee('chatSendForm');
        }
    }

    // Local TEST renders the same unavailable contract and grants no protected Seller authorization.
    public function test_local_test_is_truthful_without_granting_seller_authorization(): void
    {
        $this->get('/__dev/preview/seller/chat')->assertNotFound();
        $this->app->detectEnvironment(fn () => 'local');
        \Illuminate\Support\Facades\Route::middleware('web')->group(base_path('routes/debug.php'));
        $this->app['router']->getRoutes()->refreshNameLookups();
        $this->get('/__dev/preview/seller/chat')->assertOk()
            ->assertSee('Live messaging is unavailable.')->assertSee('Messages are not sent or stored here.')
            ->assertSee('Read/unread and archive state are unavailable.')
            ->assertSee('Blocking and reporting users or conversations are unavailable.')
            ->assertDontSee('Maricel Santos')->assertDontSee('chatSendForm');
        $this->assertGuest('web');
        $this->get('/seller/chat')->assertRedirect(route('login'));
    }

    // Authentication fixtures establish access only and do not manufacture conversations.
    private function user(array $overrides = []): User
    {
        $user = new User();
        $user->forceFill(array_replace(['email' => uniqid('seller').'@messages.test',
            'password' => 'MessagesFixture123!', 'account_type' => 'seller',
            'status' => 'approved', 'email_verified_at' => now()], $overrides))->save();

        return $user;
    }
}
