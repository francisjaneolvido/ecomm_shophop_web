<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // The Buyer's chosen courier. Nullable: older orders and "no preference" checkouts leave it empty,
        // and the Seller then picks the Logistics partner as before.
        if (! Schema::hasColumn('orders', 'logistics_partner_id')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->foreignId('logistics_partner_id')->nullable()
                    ->constrained('logistics_partners')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('orders', 'logistics_partner_id')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropForeign(['logistics_partner_id']);
                $table->dropColumn('logistics_partner_id');
            });
        }
    }
};