<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ShopChatController;
use App\Models\ShopChat;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/** Mobile and web intentionally share the same chat storage and JSON contract. */
class MobileShopChatController extends ShopChatController
{
    private function marketplaceAccount(Request $request): void
    {
        $user = $request->user();
        abort_unless($user instanceof User && in_array($user->account_type, ['buyer', 'seller'], true), 403);
        abort_unless($user->status === 'approved' && $user->email_verified_at, 403, 'Approved and verified account required.');
    }

    public function open(Request $request)
    {
        $this->marketplaceAccount($request);
        abort_unless($request->user()->account_type === 'buyer', 403, 'Only buyers can start a shop conversation.');
        $request->validate(['seller_id' => ['required', 'integer',
            Rule::exists('users', 'id')->where(fn ($query) => $query
                ->where('account_type', 'seller')->where('status', 'approved')->whereNotNull('email_verified_at')),
        ]]);

        return parent::open($request);
    }

    public function conversations(Request $request)
    {
        $this->marketplaceAccount($request);
        return parent::conversations($request);
    }

    public function messages(Request $request, ShopChat $chat)
    {
        $this->marketplaceAccount($request);
        $request->validate(['after' => ['sometimes', 'integer', 'min:0']]);
        return parent::messages($request, $chat);
    }

    public function send(Request $request, ShopChat $chat)
    {
        $this->marketplaceAccount($request);
        abort_unless($chat->hasParticipant((int) $request->user()->id), 403);
        $data = $request->validate([
            'body' => ['required', 'string', 'max:1000'],
            'client_message_id' => ['required', 'uuid'],
        ]);
        $message = DB::transaction(function () use ($request, $chat, $data) {
            $locked = ShopChat::whereKey($chat->id)->lockForUpdate()->firstOrFail();
            $existing = $locked->messages()->where('sender_id', $request->user()->id)
                ->where('client_message_id', $data['client_message_id'])->first();
            if ($existing) {
                abort_unless($existing->body === trim($data['body']), 422, 'This message ID was already used.');
                return $existing;
            }
            $message = $locked->messages()->make([
                'sender_id' => $request->user()->id, 'body' => trim($data['body']),
            ]);
            $message->client_message_id = $data['client_message_id'];
            $message->save();
            $locked->update(['last_message_at' => now()]);
            return $message;
        });

        return response()->json(['ok' => true, 'message' => [
            'id' => $message->id, 'mine' => true, 'body' => $message->body,
            'time' => $message->created_at->format('g:i A'),
        ]]);
    }
}
