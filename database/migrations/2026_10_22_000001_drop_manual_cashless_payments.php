<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        if (Schema::hasColumn('orders', 'manual_cashless_payment_id')) {
            // Drop the foreign key first (if one exists), then the column.
            try {
                Schema::table('orders', function (Blueprint $table) {
                    $table->dropForeign(['manual_cashless_payment_id']);
                });
            } catch (\Throwable $e) {
                // No foreign key (or the driver does not support dropping it): continue.
            }

            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('manual_cashless_payment_id');
            });
        }

        Schema::dropIfExists('manual_cashless_payments');

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        // Manual cashless payments were permanently removed. Nothing to restore.
    }
};