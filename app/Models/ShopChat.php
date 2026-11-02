<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopChat extends Model
{
    protected $fillable = ['buyer_id', 'seller_id', 'last_message_at'];

    protected $casts = ['last_message_at' => 'datetime'];

    public function buyer()    { return $this->belongsTo(User::class, 'buyer_id'); }
    public function seller()   { return $this->belongsTo(User::class, 'seller_id'); }
    public function messages() { return $this->hasMany(ShopChatMessage::class); }

    public function hasParticipant(int $userId): bool
    {
        return (int) $this->buyer_id === $userId || (int) $this->seller_id === $userId;
    }
}