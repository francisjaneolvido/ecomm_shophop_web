<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('parcel_transfers', function (Blueprint $table) {
            $table->foreignId('transit_rider_id')->nullable()->constrained('riders')->nullOnDelete();
            $table->timestamp('departed_at')->nullable();
        });
        Schema::create('parcel_scan_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_id')->constrained('deliveries')->cascadeOnDelete();
            $table->foreignId('sorting_center_id')->constrained('sorting_centers')->restrictOnDelete();
            $table->foreignId('operator_user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('parcel_transfer_id')->nullable()->constrained('parcel_transfers')->nullOnDelete();
            $table->string('event_type', 32); // pickup_received or transfer_received
            $table->timestamp('scanned_at');
            $table->timestamps();
            $table->unique(['delivery_id','event_type','parcel_transfer_id'], 'parcel_scan_event_unique');
            $table->index(['sorting_center_id','scanned_at']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('parcel_scan_events');
        Schema::table('parcel_transfers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('transit_rider_id');
            $table->dropColumn('departed_at');
        });
    }
};
