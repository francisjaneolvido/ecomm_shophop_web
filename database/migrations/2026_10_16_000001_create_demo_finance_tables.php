<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('shophop_payment_settings', function (Blueprint $table) {
            $table->id();
            $table->string('mode', 12)->default('demo');
            $table->string('recipient_name', 160)->nullable();
            $table->string('recipient_identifier', 100)->nullable();
            $table->string('qr_path')->nullable();
            $table->text('instructions')->nullable();
            $table->timestamps();
        });
        Schema::create('platform_vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 40)->unique();
            $table->string('name', 160);
            $table->string('kind', 20); // merchandise or shipping
            $table->string('type', 12); // fixed or percent
            $table->decimal('value', 12, 2);
            $table->decimal('min_spend', 12, 2)->default(0);
            $table->unsignedInteger('usage_limit')->nullable();
            $table->unsignedInteger('used_count')->default(0);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('platform_voucher_id')->nullable()->constrained('platform_vouchers')->nullOnDelete();
            $table->foreignId('shipping_voucher_id')->nullable()->constrained('platform_vouchers')->nullOnDelete();
            $table->decimal('platform_discount', 12, 2)->default(0);
            $table->decimal('shipping_discount', 12, 2)->default(0);
        });
        // Demo-only ledger allows Admin to match a generated reference to the exact payment.
        // It is NOT a bank or GCash transaction.
        Schema::create('demo_payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manual_cashless_payment_id')->unique()
                ->constrained('manual_cashless_payments')->cascadeOnDelete();
            $table->string('reference', 50)->unique();
            $table->decimal('amount', 12, 2);
            $table->timestamp('simulated_at');
            $table->timestamps();
        });
        Schema::create('order_financials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->unique()->constrained('orders')->cascadeOnDelete();
            $table->decimal('merchandise', 12, 2);
            $table->decimal('seller_discount', 12, 2)->default(0);
            $table->decimal('platform_discount', 12, 2)->default(0);
            $table->decimal('shipping_fee', 12, 2)->default(0);
            $table->decimal('shipping_discount', 12, 2)->default(0);
            $table->decimal('cod_fee', 12, 2)->default(0);
            $table->decimal('commission', 12, 2);
            $table->decimal('seller_earnings', 12, 2);
            $table->decimal('logistics_earnings', 12, 2);
            $table->decimal('platform_net', 12, 2);
            $table->boolean('settlement_hold')->default(false);
            $table->string('hold_reason', 500)->nullable();
            $table->timestamps();
        });
        Schema::create('settlement_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->string('kind', 32); // seller, logistics, pickup_rider, delivery_rider
            $table->unsignedBigInteger('payee_id');
            $table->decimal('amount', 12, 2);
            $table->string('status', 12)->default('pending');
            $table->string('payment_reference', 120)->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('paid_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['order_id', 'kind']);
            $table->index(['kind', 'payee_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settlement_entries');
        Schema::dropIfExists('order_financials');
        Schema::dropIfExists('demo_payment_transactions');
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['platform_voucher_id']);
            $table->dropForeign(['shipping_voucher_id']);
            $table->dropColumn(['platform_voucher_id', 'shipping_voucher_id', 'platform_discount', 'shipping_discount']);
        });
        Schema::dropIfExists('platform_vouchers');
        Schema::dropIfExists('shophop_payment_settings');
    }
};
