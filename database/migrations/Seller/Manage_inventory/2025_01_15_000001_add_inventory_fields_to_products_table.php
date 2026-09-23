<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Sparse preview databases can omit optional inventory tables.
        if (! Schema::hasTable('products')) {
            return;
        }

        Schema::table('products', function (Blueprint $table) {
            $table->string('sku')->nullable()->unique()->after('name');
            $table->unsignedInteger('low_stock_threshold')->nullable()->after('stock');
            $table->boolean('has_variants')->default(false)->after('low_stock_threshold');
        });
    }

    public function down(): void
    {
        // Match the forward guard so rollback also works against the same sparse preview schema.
        if (! Schema::hasTable('products')) {
            return;
        }

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['sku', 'low_stock_threshold', 'has_variants']);
        });
    }
};
