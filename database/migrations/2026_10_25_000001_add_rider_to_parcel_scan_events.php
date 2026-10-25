<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('parcel_scan_events', function (Blueprint $table) {
            // operator_user_id remains the owning Logistics user for legacy reporting.
            // This column records the actual Rider who performed a custody QR scan.
            $table->foreignId('operator_rider_id')
                ->nullable()
                ->after('operator_staff_id')
                ->constrained('riders')
                ->nullOnDelete();

            $table->index(['operator_rider_id', 'scanned_at'], 'parcel_scan_rider_time_idx');
        });
    }

    public function down(): void
    {
        Schema::table('parcel_scan_events', function (Blueprint $table) {
            $table->dropIndex('parcel_scan_rider_time_idx');
            $table->dropConstrainedForeignId('operator_rider_id');
        });
    }
};
