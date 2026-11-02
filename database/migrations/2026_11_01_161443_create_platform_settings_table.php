<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_settings', function (Blueprint $table) {
            $table->id();
            $table->string('name', 80)->default('ShopHop');
            $table->string('description', 255)->nullable();
            $table->string('support_email', 160)->nullable();
            $table->string('support_phone', 40)->nullable();
            $table->boolean('maintenance_mode')->default(false);
            $table->string('maintenance_message', 500)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_settings');
    }
};