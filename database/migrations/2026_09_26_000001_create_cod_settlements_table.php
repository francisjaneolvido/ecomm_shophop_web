<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // One new financial record belongs to one COD Seller Order and Delivery; legacy rows stay unrecorded.
        Schema::create('cod_settlements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->unique()->constrained('orders')->restrictOnDelete();
            $table->foreignId('delivery_id')->unique()->constrained('deliveries')->restrictOnDelete();
            $table->foreignId('logistics_partner_id')->constrained()->restrictOnDelete();
            $table->decimal('expected_amount', 12, 2);
            $table->decimal('collected_amount', 12, 2);
            $table->string('status', 20);
            // Actor keys preserve cash custody history if the current assignment changes later.
            $table->foreignId('collected_by_rider_id')->constrained('riders')->restrictOnDelete();
            $table->timestamp('collected_at');
            $table->foreignId('remitted_by_rider_id')->nullable()->constrained('riders')->restrictOnDelete();
            $table->timestamp('remitted_at')->nullable();
            $table->decimal('received_amount', 12, 2)->nullable();
            $table->foreignId('reconciled_by_user_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamp('reconciled_at')->nullable();
            $table->timestamps();
            $table->index(['logistics_partner_id', 'status']);
            $table->index(['collected_by_rider_id', 'status']);
        });
    }

    public function down(): void
    {
        // Rollback removes only this milestone's records and leaves Orders and Deliveries intact.
        Schema::dropIfExists('cod_settlements');
    }
};
