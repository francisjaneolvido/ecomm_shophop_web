<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ChatController extends Controller
{
    /** Account types an admin may message. */
    private const CHATTABLE_TYPES = ['buyer', 'seller', 'logistics'];

    public function index(): View
    {
        return view('admin.chat-messaging');
    }

    /**
     * Conversation list (newest activity first) with unread counts.
     */
    public function conversations(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));

        $rows = ChatConversation::with(['user.buyer', 'user.seller', 'user.logisticsPartner', 'latestMessage'])
            ->withCount([
                'messages as unread_count' => fn ($m) => $m
                    ->where('sender_role', ChatMessage::ROLE_USER)
                    ->whereNull('read_at'),
            ])
            ->when($q !== '', fn ($query) => $query->whereHas(
                'user',
                fn ($u) => $this->matchesSearch($u, $q)
            ))
            ->orderByDesc('last_message_at')
            ->limit(100)
            ->get();

        return response()->json([
            'data' => $rows->map(fn ($c) => $this->presentConversation($c))->values(),
        ]);
    }

    /**
     * Search approved buyers / sellers / logistics partners to start a new chat.
     */
    public function users(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));

        if (mb_strlen($q) < 2) {
            return response()->json(['data' => []]);
        }

        $users = User::with(['buyer', 'seller', 'logisticsPartner'])
            ->where('status', 'approved')
            ->whereIn('account_type', self::CHATTABLE_TYPES)
            ->where(fn ($w) => $this->matchesSearch($w, $q))
            ->limit(8)
            ->get();

        return response()->json([
            'data' => $users->map(fn (User $u) => [
                'id' => $u->id,
                'name' => $u->display_name,
                'email' => $u->email,
                'account_type' => $u->account_type,
                'initials' => $u->initials,
            ])->values(),
        ]);
    }

    /**
     * Open (or create) the conversation with a user.
     */
    public function start(Request $request): JsonResponse
    {
        $data = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $user = User::findOrFail($data['user_id']);

        if (! in_array($user->account_type, self::CHATTABLE_TYPES, true) || $user->status !== 'approved') {
            return response()->json(['message' => 'This account cannot be messaged.'], 422);
        }

        $conversation = ChatConversation::firstOrCreate(
            ['user_id' => $user->id],
            ['last_message_at' => now()]
        );

        $conversation->load(['user.buyer', 'user.seller', 'user.logisticsPartner']);

        return response()->json(['data' => $this->presentConversation($conversation)]);
    }

    /**
     * Messages of a conversation.
     * - after=0  -> latest 100 messages (initial load)
     * - after=N  -> only messages newer than N (polling)
     * Opening the thread marks the user's messages as read.
     */
    public function messages(Request $request, ChatConversation $conversation): JsonResponse
    {
        $after = max(0, (int) $request->query('after', 0));

        if ($after === 0) {
            $messages = $conversation->messages()
                ->orderByDesc('id')
                ->limit(100)
                ->get()
                ->reverse()
                ->values();
        } else {
            $messages = $conversation->messages()
                ->where('id', '>', $after)
                ->orderBy('id')
                ->limit(200)
                ->get();
        }

        $conversation->messages()
            ->where('sender_role', ChatMessage::ROLE_USER)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $conversation->loadMissing(['user.buyer', 'user.seller', 'user.logisticsPartner']);

        return response()->json([
            'conversation' => $this->presentConversation($conversation),
            'data' => $messages->map(fn ($m) => $this->presentMessage($m))->values(),
        ]);
    }

    /**
     * Admin sends a message. Persisted before the response, so a successful
     * response always means the message is really stored.
     */
    public function send(Request $request, ChatConversation $conversation): JsonResponse
    {
        $data = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $conversation->loadMissing('user');

        if (! $conversation->user || $conversation->user->status !== 'approved') {
            return response()->json(['message' => 'This account can no longer receive messages.'], 422);
        }

        $message = $conversation->messages()->create([
            'sender_id' => $request->user()->id,
            'sender_role' => ChatMessage::ROLE_ADMIN,
            'body' => $data['body'],
        ]);

        $conversation->forceFill(['last_message_at' => $message->created_at])->save();

        return response()->json(['data' => $this->presentMessage($message)], 201);
    }

    /* ------------------------------------------------------------------ */

    private function matchesSearch(Builder $query, string $q): void
    {
        $like = '%' . addcslashes($q, '%_\\') . '%';

        $query->where(function (Builder $w) use ($like) {
            $w->where('email', 'like', $like)
                ->orWhereHas('buyer', fn ($b) => $b
                    ->where('first_name', 'like', $like)
                    ->orWhere('last_name', 'like', $like))
                ->orWhereHas('seller', fn ($s) => $s
                    ->where('first_name', 'like', $like)
                    ->orWhere('last_name', 'like', $like)
                    ->orWhere('business_name', 'like', $like))
                ->orWhereHas('logisticsPartner', fn ($l) => $l
                    ->where('company_name', 'like', $like));
        });
    }

    private function presentConversation(ChatConversation $c): array
    {
        $user = $c->user;
        $last = $c->latestMessage;

        return [
            'id' => $c->id,
            'user_id' => $c->user_id,
            'name' => $user?->display_name ?? 'Deleted account',
            'email' => $user?->email,
            'account_type' => $user?->account_type,
            'initials' => $user?->initials ?? '??',
            'last_message' => $last ? Str::limit($last->body, 70) : null,
            'last_sender_role' => $last?->sender_role,
            'last_message_at' => optional($c->last_message_at)->toIso8601String(),
            'unread' => (int) ($c->unread_count ?? 0),
        ];
    }

    private function presentMessage(ChatMessage $m): array
    {
        return [
            'id' => $m->id,
            'body' => $m->body,
            'sender_role' => $m->sender_role,
            'created_at' => $m->created_at->toIso8601String(),
        ];
    }
}