<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->uuid('checkout_group_id')->nullable()->after('id')->index();

            $table->foreignId('seller_id')
                ->nullable()
                ->after('buyer_id')
                ->constrained('sellers')
                ->nullOnDelete();

            $table->string('shipping_method')->nullable()->after('total_amount');
            $table->decimal('shipping_fee', 10, 2)->default(0)->after('shipping_method');

            $table->string('payment_method')->nullable()->after('shipping_fee');
            $table->decimal('cod_fee', 10, 2)->default(0)->after('payment_method');

            $table->foreignId('voucher_id')
                ->nullable()
                ->after('cod_fee')
                ->constrained('vouchers')
                ->nullOnDelete();

            $table->decimal('voucher_discount', 10, 2)->default(0)->after('voucher_id');

            $table->text('note')->nullable()->after('voucher_discount');

            $table->string('delivery_name')->nullable()->after('note');
            $table->string('delivery_phone')->nullable()->after('delivery_name');
            $table->text('delivery_address')->nullable()->after('delivery_phone');

            $table->string('gcash_reference')->nullable()->after('delivery_address');
            $table->string('gcash_proof_path')->nullable()->after('gcash_reference');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['seller_id']);
            $table->dropForeign(['voucher_id']);

            $table->dropColumn([
                'checkout_group_id',
                'seller_id',
                'shipping_method',
                'shipping_fee',
                'payment_method',
                'cod_fee',
                'voucher_id',
                'voucher_discount',
                'note',
                'delivery_name',
                'delivery_phone',
                'delivery_address',
                'gcash_reference',
                'gcash_proof_path',
            ]);
        });
    }
};