<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Earlier installs may already have some columns; add only missing ones without changing existing purchase data.
        $missing = array_filter(['order_id', 'product_id', 'product_variant_id', 'quantity', 'price'],
            fn (string $column) => ! Schema::hasColumn('order_items', $column));

        Schema::table('order_items', function (Blueprint $table) use ($missing) {
            if (in_array('order_id', $missing, true)) {
                $table->foreignId('order_id')->after('id')->constrained()->cascadeOnDelete();
            }
            if (in_array('product_id', $missing, true)) {
                $table->foreignId('product_id')->after('order_id')->constrained()->cascadeOnDelete();
            }
            if (in_array('product_variant_id', $missing, true)) {
                $table->foreignId('product_variant_id')->after('product_id')->nullable()->constrained()->nullOnDelete();
            }
            if (in_array('quantity', $missing, true)) {
                $table->unsignedInteger('quantity')->after('product_variant_id')->default(1);
            }
            if (in_array('price', $missing, true)) {
                $table->decimal('price', 10, 2)->after('quantity');
            }
        });
    }

    public function down(): void
    {
        // The canonical create migration owns these columns; rollback cannot remove preexisting purchase fields.
    }
};
