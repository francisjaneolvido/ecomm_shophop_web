<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shop_chats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('buyer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('seller_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();
            $table->unique(['buyer_id', 'seller_id']);
        });

        Schema::create('shop_chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_chat_id')->constrained('shop_chats')->cascadeOnDelete();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->text('body');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            $table->index(['shop_chat_id', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_chat_messages');
        Schema::dropIfExists('shop_chats');
    }
};