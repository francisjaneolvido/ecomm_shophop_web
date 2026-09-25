<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Legacy Riders remain operational records until an owning partner explicitly provisions credentials.
        Schema::table('riders', function (Blueprint $table) {
            $table->string('email')->nullable()->unique();
            $table->string('password')->nullable();
        });

        // Event actors survive assignment changes, while private proof belongs to one completed Delivery.
        Schema::table('deliveries', function (Blueprint $table) {
            $table->foreignId('pickup_rider_id')->nullable()->constrained('riders')->restrictOnDelete();
            $table->foreignId('transit_rider_id')->nullable()->constrained('riders')->restrictOnDelete();
            $table->foreignId('delivered_rider_id')->nullable()->constrained('riders')->restrictOnDelete();
            $table->string('proof_path')->nullable();
        });
    }

    public function down(): void
    {
        // Rollback removes evidence columns before Rider credentials and leaves original rows intact.
        Schema::table('deliveries', function (Blueprint $table) {
            $table->dropConstrainedForeignId('pickup_rider_id');
            $table->dropConstrainedForeignId('transit_rider_id');
            $table->dropConstrainedForeignId('delivered_rider_id');
            $table->dropColumn('proof_path');
        });
        Schema::table('riders', function (Blueprint $table) {
            $table->dropUnique(['email']);
            $table->dropColumn(['email', 'password']);
        });
    }
};
