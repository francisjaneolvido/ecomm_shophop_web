<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

// The Buyer Messages HTTP destination must not advertise delivery without a messaging backend.
class BuyerMessagesJourneyTest extends TestCase
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

    // A Buyer must not be offered successful sending when the page cannot deliver or store a message.
    public function test_approved_buyer_sees_truthful_messaging_availability(): void
    {
        $response = $this->actingAs($this->user())->get('/buyer/messages')->assertOk();
        $document = new \DOMDocument();
        @$document->loadHTML($response->getContent());
        $xpath = new \DOMXPath($document);
        $this->assertSame(0, $xpath->query('//button[@aria-label="Send message"]')->length,
            'Buyer Messages must not offer delivery when sending is unavailable.');
        $response->assertSee('Live messaging is unavailable.')
            ->assertSee('No live conversations or account message history are displayed.')
            ->assertSee('Messages are not sent or stored here.')
            ->assertSee('Conversation history is not persisted here.')
            ->assertSee('Read/unread state, delivery/read receipts and live presence are unavailable.')
            ->assertSee('Attachments cannot be uploaded or sent here.');

        // Unsupported actions and fabricated account history cannot survive inside the informational destination.
        $content = $xpath->query('//*[@id="buyerMessages"]');
        $this->assertSame(1, $content->length);
        $this->assertSame(0, $xpath->query('//*[@id="buyerMessages"]//*[self::form or self::button or self::input or self::select or self::textarea or self::script or self::a]')->length);
        foreach (['ShopHop Tech Store', 'StepUp Footwear PH', 'ShopHop Support', 'HomeTech Essentials',
            'SHP-2026-00125', 'SHP-2026-00097', 'SHP-2026-00088', 'Conversation preview',
            'Online now', 'Just now', 'Sent a photo', 'Mark all read', 'All read',
            'All conversations marked as read.', 'Search seller or message...'] as $falseFact) {
            $this->assertStringNotContainsString($falseFact, $content->item(0)->textContent);
        }
        foreach (['message-composer', 'message-attachment-input', 'messagesToast',
            'markMessagesReadBtn', 'conversationSearch', 'data-conversation-id',
            'data-conversation-unread', 'data-conversation-panel'] as $oldAffordance) {
            $response->assertDontSee($oldAffordance);
        }
        $this->assertGreaterThan(0, $xpath->query('//a[@href="'.route('buyer.messages').'"]')->length);
        $this->assertGreaterThan(0, $xpath->query('//a[@href="'.route('buyer.dashboard').'"]')->length);
    }

    // The stable destination still requires normal Buyer authentication.
    public function test_guest_cannot_open_buyer_messages(): void
    {
        $this->get('/buyer/messages')->assertRedirect(route('login'));
    }

    // Existing role, approval and email-verification semantics remain authoritative.
    public function test_wrong_roles_and_unapproved_or_unverified_buyers_are_denied(): void
    {
        foreach (['seller', 'admin', 'logistics'] as $role) {
            $this->actingAs($this->user(['account_type' => $role]))->get('/buyer/messages')->assertForbidden();
        }
        foreach (['pending', 'rejected', 'suspended'] as $status) {
            $this->actingAs($this->user(['status' => $status]))->get('/buyer/messages')
                ->assertRedirect(route('home'))->assertSessionHas('login_notice');
            $this->assertGuest('web');
        }
        $this->actingAs($this->user(['email_verified_at' => null]))->get('/buyer/messages')
            ->assertRedirect(route('buyer.verify-email.show'))->assertSessionHas('buyer_verification_user_id');
        $this->assertGuest('web');
    }

    // A separately authenticated Rider receives no marketplace Buyer authorization.
    public function test_rider_guard_cannot_open_buyer_messages(): void
    {
        $rider = new \App\Models\Logistics\Rider();
        $rider->forceFill(['id' => 1]);
        $this->app['auth']->guard('rider')->setUser($rider);
        $this->assertTrue($this->app['auth']->guard('rider')->check());
        $this->assertFalse($this->app['auth']->guard('web')->check());
        $this->get('/buyer/messages')->assertRedirect(route('login'));
    }

    // No compatibility action may imply delivery, read-state mutation, attachment storage or conversation deletion.
    public function test_unsupported_message_operations_fail(): void
    {
        $this->actingAs($this->user());
        $before = DB::table('users')->orderBy('id')->get()->toJson();
        foreach (['post', 'patch', 'delete'] as $method) {
            $this->{$method}('/buyer/messages')->assertStatus(405)->assertSessionMissing('status');
        }
        foreach ([['post', 'send'], ['post', '1/messages'], ['patch', '1/read'],
            ['post', 'mark-all-read'], ['post', '1/attachments'], ['delete', '1']] as [$method, $path]) {
            $this->{$method}('/buyer/messages/'.$path)->assertNotFound()->assertSessionMissing('status');
        }
        $this->assertSame($before, DB::table('users')->orderBy('id')->get()->toJson());
    }

    // Old fixture-selection and search parameters cannot revive account-looking history or actions.
    public function test_legacy_filters_and_selection_remain_informational(): void
    {
        $this->actingAs($this->user());
        foreach (['all', 'unread', 'support'] as $filter) {
            $this->get('/buyer/messages?filter='.$filter.'&search=StepUp&conversation=tech-store&send=1')
                ->assertOk()->assertSee('Live messaging is unavailable.')
                ->assertDontSee('StepUp Footwear PH')->assertDontSee('message-composer')
                ->assertDontSee('data-conversation-id');
        }
    }

    // Local TEST shares the same limits and grants no production Buyer access; DEMO remains normal authentication.
    public function test_local_test_is_truthful_without_granting_buyer_authorization(): void
    {
        $this->get('/__dev/preview/buyer/messages')->assertNotFound();
        $this->app->detectEnvironment(fn () => 'local');
        \Illuminate\Support\Facades\Route::middleware('web')->group(base_path('routes/debug.php'));
        $this->app['router']->getRoutes()->refreshNameLookups();
        $this->get('/__dev/preview/buyer/messages')->assertOk()
            ->assertSee('Live messaging is unavailable.')->assertSee('Messages are not sent or stored here.')
            ->assertSee('Conversation history is not persisted here.')
            ->assertSee('Read/unread state, delivery/read receipts and live presence are unavailable.')
            ->assertSee('Attachments cannot be uploaded or sent here.')
            ->assertDontSee('data-conversation-id')->assertDontSee('message-composer')
            ->assertDontSee('message-attachment-input')->assertDontSee('markMessagesReadBtn');
        $this->assertGuest('web');
        $this->get('/buyer/messages')->assertRedirect(route('login'));
    }

    // Authentication fixtures establish access only and do not manufacture conversations.
    private function user(array $overrides = []): User
    {
        $user = new User();
        $user->forceFill(array_replace(['email' => uniqid('buyer').'@messages.test',
            'password' => 'MessagesFixture123!', 'account_type' => 'buyer',
            'status' => 'approved', 'email_verified_at' => now()], $overrides))->save();

        return $user;
    }
}
