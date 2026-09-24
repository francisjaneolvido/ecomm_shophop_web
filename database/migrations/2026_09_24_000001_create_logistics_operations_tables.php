<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Rider identity belongs to one partner; only active riders may receive new assignments.
        Schema::create('riders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('logistics_partner_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('vehicle_type', 80);
            $table->string('status', 20)->default('active');
            $table->timestamps();
        });

        // A unique Order key makes mixed-Seller Orders independent and prevents duplicate assignments.
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('logistics_partner_id')->constrained()->restrictOnDelete();
            $table->foreignId('rider_id')->constrained()->restrictOnDelete();
            $table->string('status', 20);
            $table->timestamp('assigned_at');
            $table->timestamp('picked_up_at')->nullable();
            $table->timestamp('in_transit_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        // Delivery facts must be removed before their Rider identities on an explicit rollback.
        Schema::dropIfExists('deliveries');
        Schema::dropIfExists('riders');
    }
};
