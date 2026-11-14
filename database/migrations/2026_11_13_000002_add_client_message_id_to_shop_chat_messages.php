<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shop_chat_messages', function (Blueprint $table) {
            $table->uuid('client_message_id')->nullable();
            $table->unique(['shop_chat_id', 'sender_id', 'client_message_id'], 'shop_chat_client_message_unique');
        });
    }

    public function down(): void
    {
        Schema::table('shop_chat_messages', function (Blueprint $table) {
            $table->dropUnique('shop_chat_client_message_unique');
            $table->dropColumn('client_message_id');
        });
    }
};
