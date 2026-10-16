<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('return_cases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->unique()->constrained('orders')->restrictOnDelete();
            $table->foreignId('delivery_id')->unique()->constrained('deliveries')->restrictOnDelete();
            $table->foreignId('logistics_partner_id')->constrained('logistics_partners')->restrictOnDelete();
            $table->string('status', 32)->default('requested');
            $table->text('reason');
            $table->foreignId('return_rider_id')->nullable()->constrained('riders')->restrictOnDelete();
            $table->foreignId('return_branch_id')->nullable()->constrained('sorting_centers')->restrictOnDelete();
            $table->unsignedBigInteger('requested_by_user_id')->nullable();
            $table->unsignedBigInteger('received_by_user_id')->nullable();
            $table->timestamp('branch_received_at')->nullable();
            $table->timestamp('rider_collected_at')->nullable();
            $table->string('handover_proof_path')->nullable();
            $table->timestamp('handover_submitted_at')->nullable();
            $table->timestamp('seller_received_at')->nullable();
            $table->string('refund_status', 24)->default('not_applicable');
            $table->decimal('refund_amount', 12, 2)->default(0);
            $table->string('refund_reference', 120)->nullable();
            $table->unsignedBigInteger('refunded_by_user_id')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->timestamps();
            $table->index(['logistics_partner_id','status']);
            $table->index(['return_rider_id','status']);
        });
    }
    public function down(): void { Schema::dropIfExists('return_cases'); }
};
