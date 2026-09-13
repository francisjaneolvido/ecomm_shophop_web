<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->foreignId('order_id')->after('id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->after('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_variant_id')->after('product_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('quantity')->after('product_variant_id')->default(1);
            $table->decimal('price', 10, 2)->after('quantity');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
            $table->dropForeign(['product_id']);
            $table->dropForeign(['product_variant_id']);
            $table->dropColumn(['order_id', 'product_id', 'product_variant_id', 'quantity', 'price']);
        });
    }
};