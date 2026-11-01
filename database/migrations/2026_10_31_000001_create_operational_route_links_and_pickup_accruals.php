<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('logistics_route_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('logistics_partner_id')->constrained('logistics_partners')->cascadeOnDelete();
            $table->foreignId('from_center_id')->constrained('sorting_centers')->cascadeOnDelete();
            $table->foreignId('to_center_id')->constrained('sorting_centers')->cascadeOnDelete();
            $table->string('mode', 20); // road, sea, air
            $table->string('status', 20)->default('proposed'); // proposed, verified, suspended
            $table->string('notes', 500)->nullable();
            $table->foreignId('verified_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
            $table->unique(['logistics_partner_id','from_center_id','to_center_id'], 'sh_route_direction_unique');
        });
        Schema::create('pickup_rider_accruals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->unique()->constrained('orders')->cascadeOnDelete();
            $table->foreignId('delivery_id')->unique()->constrained('deliveries')->cascadeOnDelete();
            $table->foreignId('logistics_partner_id')->constrained('logistics_partners')->cascadeOnDelete();
            $table->foreignId('rider_id')->constrained('riders')->cascadeOnDelete();
            $table->foreignId('received_center_id')->constrained('sorting_centers')->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('status', 24)->default('pending_release');
            $table->timestamp('earned_at');
            $table->timestamps();
            $table->index(['rider_id','status']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('pickup_rider_accruals');
        Schema::dropIfExists('logistics_route_links');
    }
};
