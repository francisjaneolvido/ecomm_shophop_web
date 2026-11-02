<?php

namespace Tests\Feature;

use App\Models\ShopChat;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class BuyerMessagesJourneyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->assertSame('sqlite', DB::getDefaultConnection());
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        Schema::dropAllTables();
        foreach (['0001_01_01_000000_create_users_table.php',
            '2026_08_31_000001_add_account_type_and_status_to_users_table.php',
            '2026_11_02_023912_create_shop_chats_tables.php'] as $migration) {
            (require database_path('migrations/'.$migration))->up();
        }
    }

    public function test_approved_buyer_sees_live_messages_inbox(): void
    {
        $response = $this->actingAs($this->user())->get('/buyer/messages')->assertOk();
        $response->assertSee('buyerConvList', false)
            ->assertSee('buyerChatForm', false)
            ->assertDontSee('Live messaging is unavailable.');

        foreach (['ShopHop Tech Store', 'StepUp Footwear PH', 'ShopHop Support', 'HomeTech Essentials',
            'SHP-2026-00125', 'Online now', 'Sent a photo'] as $falseFact) {
            $response->assertDontSee($falseFact);
        }
    }

    public function test_guest_cannot_open_buyer_messages(): void
    {
        $this->get('/buyer/messages')->assertRedirect(route('login'));
    }

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

    public function test_rider_guard_cannot_open_buyer_messages(): void
    {
        $rider = new \App\Models\Logistics\Rider();
        $rider->forceFill(['id' => 1]);
        $this->app['auth']->guard('rider')->setUser($rider);
        $this->assertTrue($this->app['auth']->guard('rider')->check());
        $this->assertFalse($this->app['auth']->guard('web')->check());
        $this->get('/buyer/messages')->assertRedirect(route('login'));
    }

    public function test_unsupported_buyer_messages_routes_fail(): void
    {
        $this->actingAs($this->user());
        foreach (['post', 'patch', 'delete'] as $method) {
            $this->{$method}('/buyer/messages')->assertStatus(405);
        }
        foreach ([['post', 'send'], ['post', '1/messages'], ['patch', '1/read'],
            ['post', 'mark-all-read'], ['post', '1/attachments'], ['delete', '1']] as [$method, $path]) {
            $this->{$method}('/buyer/messages/'.$path)->assertNotFound();
        }
    }

    // End-to-end: buyer opens a chat with a seller, sends, then sees the seller's reply.
    public function test_buyer_can_open_chat_send_and_read_reply(): void
    {
        $buyer  = $this->user();
        $seller = $this->user(['account_type' => 'seller']);

        $this->actingAs($buyer);
        $chatId = $this->postJson('/chat/open', ['seller_id' => $seller->id])
            ->assertOk()->json('chat_id');

        $this->postJson('/chat/'.$chatId.'/send', ['body' => 'Hello, is this available?'])
            ->assertOk()->assertJsonPath('message.mine', true);

        ShopChat::find($chatId)->messages()->create(['sender_id' => $seller->id, 'body' => 'Yes!']);

        $this->getJson('/chat/conversations')->assertOk()
            ->assertJsonPath('conversations.0.id', $chatId)
            ->assertJsonPath('conversations.0.unread', 1);

        $this->getJson('/chat/'.$chatId.'/messages')->assertOk()
            ->assertJsonPath('messages.1.body', 'Yes!')
            ->assertJsonPath('messages.1.mine', false);
    }

    public function test_buyer_cannot_message_themselves(): void
    {
        $buyer = $this->user();
        $this->actingAs($buyer)->postJson('/chat/open', ['seller_id' => $buyer->id])->assertStatus(422);
    }

    private function user(array $overrides = []): User
    {
        $user = new User();
        $user->forceFill(array_replace(['email' => uniqid('buyer').'@messages.test',
            'password' => 'MessagesFixture123!', 'account_type' => 'buyer',
            'status' => 'approved', 'email_verified_at' => now()], $overrides))->save();

        return $user;
    }
}