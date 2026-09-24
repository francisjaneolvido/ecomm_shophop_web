<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Existing Orders schemas may already carry these purchase fields; preserve them during upgrades.
        $missing = array_filter(['buyer_id', 'status', 'total_amount'],
            fn (string $column) => ! Schema::hasColumn('orders', $column));

        Schema::table('orders', function (Blueprint $table) use ($missing) {
            if (in_array('buyer_id', $missing, true)) {
                $table->foreignId('buyer_id')->after('id')->constrained('buyers')->cascadeOnDelete();
            }
            if (in_array('status', $missing, true)) {
                $table->string('status')->after('buyer_id')->default('pending');
            }
            if (in_array('total_amount', $missing, true)) {
                $table->decimal('total_amount', 12, 2)->after('status')->default(0);
            }
        });
    }

    public function down(): void
    {
        // The canonical create migration owns these columns, so rollback must preserve existing Orders.
    }
};
