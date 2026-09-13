<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('buyer_id')->after('id')->constrained('buyers')->cascadeOnDelete();
            $table->string('status')->after('buyer_id')->default('pending');
            $table->decimal('total_amount', 12, 2)->after('status')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['buyer_id']);
            $table->dropColumn(['buyer_id', 'status', 'total_amount']);
        });
    }
};