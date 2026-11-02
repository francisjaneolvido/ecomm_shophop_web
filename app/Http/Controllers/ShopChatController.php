<?php

namespace App\Http\Controllers;

use App\Models\ShopChat;
use App\Models\ShopChatMessage;
use App\Models\User;
use Illuminate\Http\Request;

class ShopChatController extends Controller
{
    private function displayName(?User $u): string
    {
        if (! $u) return 'User';
        $name = $u->name ?? trim(($u->first_name ?? '') . ' ' . ($u->last_name ?? ''));
        return $name !== '' ? $name : ($u->email ?? 'User');
    }

    private function authorizeChat(Request $request, ShopChat $chat): void
    {
        abort_unless($chat->hasParticipant((int) $request->user()->id), 403);
    }

    public function page()
    {
        return view('chat.inbox');
    }

    public function open(Request $request)
    {
        $data = $request->validate([
            'seller_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $me = $request->user();
        abort_if((int) $data['seller_id'] === (int) $me->id, 422, 'You cannot message yourself.');

        $chat = ShopChat::firstOrCreate([
            'buyer_id'  => $me->id,
            'seller_id' => $data['seller_id'],
        ]);

        return response()->json(['ok' => true, 'chat_id' => $chat->id]);
    }

    public function conversations(Request $request)
    {
        $me = $request->user();

        $rows = ShopChat::query()
            ->where(fn ($q) => $q->where('buyer_id', $me->id)->orWhere('seller_id', $me->id))
            ->with(['buyer', 'seller'])
            ->orderByDesc('last_message_at')->orderByDesc('id')
            ->get()
            ->map(function (ShopChat $c) use ($me) {
                $other = (int) $c->buyer_id === (int) $me->id ? $c->seller : $c->buyer;
                $last  = $c->messages()->latest('id')->first();
                return [
                    'id'     => $c->id,
                    'name'   => $this->displayName($other),
                    'last'   => $last?->body,
                    'time'   => $c->last_message_at?->diffForHumans(),
                    'unread' => $c->messages()->where('sender_id', '!=', $me->id)->whereNull('read_at')->count(),
                ];
            });

        return response()->json(['ok' => true, 'conversations' => $rows]);
    }

    public function messages(Request $request, ShopChat $chat)
    {
        $this->authorizeChat($request, $chat);
        $me    = $request->user();
        $after = (int) $request->query('after', 0);

        $chat->messages()
            ->where('sender_id', '!=', $me->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $messages = $chat->messages()
            ->where('id', '>', $after)
            ->orderBy('id')
            ->limit(100)
            ->get()
            ->map(fn (ShopChatMessage $m) => [
                'id'   => $m->id,
                'mine' => (int) $m->sender_id === (int) $me->id,
                'body' => $m->body,
                'time' => $m->created_at->format('g:i A'),
            ]);

        return response()->json(['ok' => true, 'messages' => $messages]);
    }

    public function send(Request $request, ShopChat $chat)
    {
        $this->authorizeChat($request, $chat);

        $data = $request->validate([
            'body' => ['required', 'string', 'max:1000'],
        ]);

        $message = $chat->messages()->create([
            'sender_id' => $request->user()->id,
            'body'      => trim($data['body']),
        ]);

        $chat->update(['last_message_at' => now()]);

        return response()->json([
            'ok'      => true,
            'message' => [
                'id'   => $message->id,
                'mine' => true,
                'body' => $message->body,
                'time' => $message->created_at->format('g:i A'),
            ],
        ]);
    }
}