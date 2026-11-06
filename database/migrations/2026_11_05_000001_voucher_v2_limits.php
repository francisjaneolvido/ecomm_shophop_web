<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('vouchers', function (Blueprint $table) {
            $table->decimal('max_discount', 12, 2)->nullable();
            $table->unsignedInteger('per_buyer_limit')->nullable();
        });
        Schema::table('platform_vouchers', function (Blueprint $table) {
            $table->decimal('max_discount', 12, 2)->nullable();
            $table->unsignedInteger('per_buyer_limit')->nullable();
            // Budget is an optional monetary ceiling for new promotions.
            $table->decimal('budget_amount', 14, 2)->nullable();
            $table->decimal('spent_amount', 14, 2)->default(0);
        });
    }
    public function down(): void
    {
        Schema::table('platform_vouchers', fn (Blueprint $table) => $table->dropColumn([
            'max_discount', 'per_buyer_limit', 'budget_amount', 'spent_amount',
        ]));
        Schema::table('vouchers', fn (Blueprint $table) => $table->dropColumn([
            'max_discount', 'per_buyer_limit',
        ]));
    }
};
