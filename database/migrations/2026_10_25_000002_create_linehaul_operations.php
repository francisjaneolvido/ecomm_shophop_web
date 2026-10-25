<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fleet_vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('logistics_partner_id')->constrained('logistics_partners')->cascadeOnDelete();
            $table->foreignId('home_hub_id')->nullable()->constrained('sorting_centers')->nullOnDelete();
            $table->string('code', 40)->unique();
            $table->string('plate_number', 40)->unique();
            $table->string('vehicle_type', 40);
            $table->decimal('capacity_kg', 10, 2)->nullable();
            $table->decimal('volume_capacity_m3', 10, 2)->nullable();
            $table->string('owner_type', 20)->default('company'); // company | contracted
            $table->string('status', 20)->default('available'); // available | in_transit | maintenance | inactive
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->index(['logistics_partner_id', 'home_hub_id', 'status'], 'fleet_vehicle_hub_status_idx');
        });

        Schema::create('linehaul_drivers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('logistics_partner_id')->constrained('logistics_partners')->cascadeOnDelete();
            $table->string('name', 150);
            $table->string('contact_no', 40)->nullable();
            $table->string('license_no', 80)->nullable();
            $table->date('license_expiry')->nullable();
            $table->string('email', 190)->unique();
            $table->string('password');
            $table->string('status', 20)->default('active'); // active | inactive
            $table->rememberToken();
            $table->timestamps();

            $table->index(['logistics_partner_id', 'status'], 'linehaul_driver_partner_status_idx');
        });

        Schema::create('linehaul_trips', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->foreignId('logistics_partner_id')->constrained('logistics_partners')->cascadeOnDelete();
            $table->foreignId('from_sorting_center_id')->constrained('sorting_centers')->restrictOnDelete();
            $table->foreignId('to_sorting_center_id')->constrained('sorting_centers')->restrictOnDelete();
            $table->foreignId('vehicle_id')->constrained('fleet_vehicles')->restrictOnDelete();
            $table->foreignId('driver_id')->constrained('linehaul_drivers')->restrictOnDelete();
            $table->foreignId('assigned_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 24)->default('scheduled'); // scheduled | in_transit | arrived | completed | cancelled
            $table->string('note', 255)->nullable();
            $table->timestamp('departed_at')->nullable();
            $table->timestamp('arrived_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['logistics_partner_id', 'status'], 'linehaul_trip_partner_status_idx');
            $table->index(['vehicle_id', 'status'], 'linehaul_trip_vehicle_status_idx');
            $table->index(['driver_id', 'status'], 'linehaul_trip_driver_status_idx');
        });

        Schema::table('parcel_manifests', function (Blueprint $table) {
            $table->foreignId('linehaul_trip_id')->nullable()->after('transit_rider_id')
                ->constrained('linehaul_trips')->nullOnDelete();
            $table->index(['linehaul_trip_id', 'status'], 'parcel_manifest_trip_status_idx');
        });

        Schema::table('parcel_transfers', function (Blueprint $table) {
            $table->foreignId('linehaul_trip_id')->nullable()->after('transit_rider_id')
                ->constrained('linehaul_trips')->nullOnDelete();
            $table->index(['linehaul_trip_id', 'status'], 'parcel_transfer_trip_status_idx');
        });

        Schema::table('parcel_manifest_items', function (Blueprint $table) {
            $table->timestamp('loaded_at')->nullable()->after('delivery_id');
            $table->timestamp('received_at')->nullable()->after('loaded_at');
            $table->foreignId('loaded_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('loaded_by_staff_id')->nullable()->constrained('logistics_branch_staff')->nullOnDelete();
            $table->foreignId('received_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('received_by_staff_id')->nullable()->constrained('logistics_branch_staff')->nullOnDelete();
        });

        Schema::table('parcel_scan_events', function (Blueprint $table) {
            $table->foreignId('operator_linehaul_driver_id')->nullable()
                ->after('operator_rider_id')
                ->constrained('linehaul_drivers')->nullOnDelete();
            $table->index(['operator_linehaul_driver_id', 'scanned_at'], 'parcel_scan_linehaul_driver_time_idx');
        });
    }

    public function down(): void
    {
        Schema::table('parcel_scan_events', function (Blueprint $table) {
            $table->dropIndex('parcel_scan_linehaul_driver_time_idx');
            $table->dropConstrainedForeignId('operator_linehaul_driver_id');
        });

        Schema::table('parcel_manifest_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('loaded_by_user_id');
            $table->dropConstrainedForeignId('loaded_by_staff_id');
            $table->dropConstrainedForeignId('received_by_user_id');
            $table->dropConstrainedForeignId('received_by_staff_id');
            $table->dropColumn(['loaded_at', 'received_at']);
        });

        Schema::table('parcel_transfers', function (Blueprint $table) {
            $table->dropIndex('parcel_transfer_trip_status_idx');
            $table->dropConstrainedForeignId('linehaul_trip_id');
        });

        Schema::table('parcel_manifests', function (Blueprint $table) {
            $table->dropIndex('parcel_manifest_trip_status_idx');
            $table->dropConstrainedForeignId('linehaul_trip_id');
        });

        Schema::dropIfExists('linehaul_trips');
        Schema::dropIfExists('linehaul_drivers');
        Schema::dropIfExists('fleet_vehicles');
    }
};
