<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('interisland_cargo_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manifest_id')->unique()->constrained('parcel_manifests')->restrictOnDelete();
            $table->foreignId('logistics_partner_id')->constrained('logistics_partners')->restrictOnDelete();
            $table->foreignId('route_link_id')->constrained('logistics_route_links')->restrictOnDelete();
            $table->string('mode', 12); // sea | air, immutable from verified route
            $table->string('carrier_name', 150);
            $table->string('booking_reference', 120);
            $table->string('status', 24)->default('booked'); // booked | in_transit | arrived | completed
            $table->foreignId('booked_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('departed_at')->nullable();
            $table->timestamp('arrived_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->index(['logistics_partner_id', 'status']);
        });
        Schema::table('parcel_transfers', function (Blueprint $table) {
            $table->foreignId('cargo_transfer_id')->nullable()->constrained('interisland_cargo_transfers')->nullOnDelete();
            $table->index(['cargo_transfer_id','status']);
        });
    }
    public function down(): void
    {
        Schema::table('parcel_transfers', function (Blueprint $table) {
            $table->dropIndex(['cargo_transfer_id','status']);
            $table->dropConstrainedForeignId('cargo_transfer_id');
        });
        Schema::dropIfExists('interisland_cargo_transfers');
    }
};
