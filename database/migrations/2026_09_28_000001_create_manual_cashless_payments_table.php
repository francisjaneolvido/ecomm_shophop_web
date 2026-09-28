<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // One Buyer-owned payment covers all Seller Orders created under one checkout group.
        Schema::create('manual_cashless_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('buyer_id')->constrained('buyers')->cascadeOnDelete();
            $table->uuid('checkout_group_id')->unique();
            $table->string('status', 24);
            $table->string('reference', 120)->nullable();
            // A unique normalized reference closes concurrent reuse by another payment.
            $table->string('normalized_reference', 120)->nullable()->unique();
            $table->string('receipt_path')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('reviewer_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('decision', 24)->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
        });

        // Explicit Order membership prevents a copied checkout UUID from granting payment eligibility.
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('manual_cashless_payment_id')->nullable()
                ->constrained('manual_cashless_payments')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['manual_cashless_payment_id']);
            $table->dropColumn('manual_cashless_payment_id');
        });
        Schema::dropIfExists('manual_cashless_payments');
    }
};
