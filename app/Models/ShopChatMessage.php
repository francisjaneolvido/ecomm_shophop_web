<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopChatMessage extends Model
{
    protected $fillable = ['shop_chat_id', 'sender_id', 'body', 'read_at'];

    protected $casts = ['read_at' => 'datetime'];

    public function chat() { return $this->belongsTo(ShopChat::class, 'shop_chat_id'); }
}