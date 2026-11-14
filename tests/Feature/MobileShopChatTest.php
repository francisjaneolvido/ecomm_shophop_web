<?php

namespace Tests\Feature;

use App\Http\Controllers\ShopChatController;
use App\Models\ShopChat;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MobileShopChatTest extends TestCase
{
    private User $buyer;
    private User $seller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame('sqlite', DB::getDefaultConnection());
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        Schema::create('users', function (Blueprint $t) {
            $t->id(); $t->string('email'); $t->string('password');
            $t->string('account_type'); $t->string('status');
            $t->timestamp('email_verified_at')->nullable(); $t->timestamps();
        });
        (require database_path('migrations/2026_11_02_023912_create_shop_chats_tables.php'))->up();
        (require database_path('migrations/2026_11_13_000002_add_client_message_id_to_shop_chat_messages.php'))->up();
        $this->buyer = $this->user('buyer');
        $this->seller = $this->user('seller');
    }

    private function user(string $role): User
    {
        $user = User::create(['email' => uniqid().'@example.com', 'password' => 'Password1!',
            'account_type' => $role, 'status' => 'approved']);
        $user->forceFill(['email_verified_at' => now()])->save();
        return $user;
    }

    public function test_mobile_shares_web_history_and_read_status_with_participants(): void
    {
        $chat = ShopChat::create(['buyer_id' => $this->buyer->id, 'seller_id' => $this->seller->id]);
        $webMessage = $chat->messages()->create(['sender_id' => $this->seller->id, 'body' => 'Reply from web']);
        $other = $this->user('buyer');
        $otherChat = ShopChat::create(['buyer_id' => $other->id, 'seller_id' => $this->seller->id]);
        Sanctum::actingAs($this->buyer);
        $this->getJson('/api/mobile/chat/conversations')->assertOk()
            ->assertJsonCount(1, 'conversations')->assertJsonPath('conversations.0.id', $chat->id)
            ->assertJsonPath('conversations.0.unread', 1);
        $this->getJson('/api/mobile/chat/'.$chat->id.'/messages')->assertOk()
            ->assertJsonPath('messages.0.body', 'Reply from web')->assertJsonPath('messages.0.mine', false);
        $this->assertNotNull($webMessage->fresh()->read_at);
        $this->postJson('/api/mobile/chat/'.$chat->id.'/send', ['body' => 'Reply from mobile',
            'client_message_id' => '07aba20d-b118-4e66-9da3-81390bf206ba'])
            ->assertOk()->assertJsonPath('message.mine', true);
        $request = Request::create('/chat/'.$chat->id.'/messages');
        $request->setUserResolver(fn () => $this->seller);
        $response = (new ShopChatController)->messages($request, $chat)->getData(true);
        $this->assertSame('Reply from mobile', $response['messages'][1]['body']);
        $this->assertFalse($response['messages'][1]['mine']);
        $this->getJson('/api/mobile/chat/'.$otherChat->id.'/messages')->assertForbidden();
        $this->postJson('/api/mobile/chat/'.$otherChat->id.'/send', ['body' => 'Unauthorized'])->assertForbidden();
        $this->assertSame(2, $chat->messages()->count());
    }

    public function test_open_reuses_the_web_conversation_and_rejects_invalid_recipients(): void
    {
        Sanctum::actingAs($this->buyer);
        $chat = ShopChat::create(['buyer_id' => $this->buyer->id, 'seller_id' => $this->seller->id]);
        $this->postJson('/api/mobile/chat/open', ['seller_id' => $this->seller->id])
            ->assertOk()->assertJsonPath('chat_id', $chat->id);
        $this->assertSame(1, ShopChat::count());
        $this->postJson('/api/mobile/chat/open', ['seller_id' => $this->buyer->id])->assertUnprocessable();
        $suspended = $this->user('seller');
        $suspended->update(['status' => 'suspended']);
        $this->postJson('/api/mobile/chat/open', ['seller_id' => $suspended->id])->assertUnprocessable();
        $this->postJson('/api/mobile/chat/'.$chat->id.'/send', ['body' => '   '])->assertUnprocessable();
        $this->postJson('/api/mobile/chat/'.$chat->id.'/send', ['body' => str_repeat('x', 1001)])->assertUnprocessable();
        $this->assertSame(0, $chat->messages()->count());
    }

    public function test_guest_and_ineligible_accounts_cannot_use_mobile_chat(): void
    {
        $this->getJson('/api/mobile/chat/conversations')->assertUnauthorized();
        foreach (['suspended', 'pending'] as $status) {
            $actor = $this->buyer->fresh(); $actor->status = $status;
            Sanctum::actingAs($actor);
            $this->getJson('/api/mobile/chat/conversations')->assertForbidden();
        }
        Sanctum::actingAs($this->user('admin'));
        $this->getJson('/api/mobile/chat/conversations')->assertForbidden();
    }

    public function test_retry_of_committed_send_returns_the_same_message(): void
    {
        Sanctum::actingAs($this->buyer);
        $chat = ShopChat::create(['buyer_id' => $this->buyer->id, 'seller_id' => $this->seller->id]);
        $data = ['body' => 'Retry safely', 'client_message_id' => 'b8e444a2-48a8-4c06-9dce-e7d168ae4781'];
        $first = $this->postJson('/api/mobile/chat/'.$chat->id.'/send', $data)->assertOk();
        $retry = $this->postJson('/api/mobile/chat/'.$chat->id.'/send', $data)->assertOk();
        $this->assertSame($first->json('message.id'), $retry->json('message.id'));
        $this->assertSame(1, $chat->messages()->count());
    }
}
