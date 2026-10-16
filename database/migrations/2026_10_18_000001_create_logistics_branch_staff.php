<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('logistics_branch_staff', function (Blueprint $table) {
            $table->id();
            $table->foreignId('logistics_partner_id')->constrained('logistics_partners')->cascadeOnDelete();
            $table->foreignId('sorting_center_id')->constrained('sorting_centers')->restrictOnDelete();
            $table->string('name', 150);
            $table->string('email', 190)->unique();
            $table->string('password');
            $table->string('role', 20)->default('scanner'); // manager or scanner
            $table->string('status', 20)->default('active'); // active or inactive
            $table->rememberToken();
            $table->timestamps();
            $table->index(['logistics_partner_id', 'sorting_center_id', 'status'], 'branch_staff_access_idx');
        });

        // Keep legacy operator_user_id for the approved company User; record the actual staff actor separately.
        Schema::table('parcel_scan_events', function (Blueprint $table) {
            $table->foreignId('operator_staff_id')->nullable()->after('operator_user_id')
                ->constrained('logistics_branch_staff')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('parcel_scan_events', function (Blueprint $table) {
            $table->dropConstrainedForeignId('operator_staff_id');
        });
        Schema::dropIfExists('logistics_branch_staff');
    }
};
