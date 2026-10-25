<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settlement_entries', function (Blueprint $table) {
            $table->string('distribution_reference', 120)->nullable()->after('status');
            $table->timestamp('distributed_at')->nullable()->after('distribution_reference');
            $table->foreignId('distributed_by_user_id')->nullable()->after('distributed_at')
                ->constrained('users')->nullOnDelete();
        });

        Schema::table('center_revenue_ledger_entries', function (Blueprint $table) {
            $table->timestamp('posted_at')->nullable()->after('status');
        });

        Schema::table('linehaul_driver_earnings', function (Blueprint $table) {
            $table->timestamp('available_at')->nullable()->after('status');
        });

        // Completed trips are already fully earned; normalize old Patch-B rows.
        DB::table('linehaul_driver_earnings')
            ->where('status', 'pending')
            ->whereIn('linehaul_trip_id', function ($query) {
                $query->select('id')->from('linehaul_trips')->where('status', 'completed');
            })
            ->update(['status' => 'available', 'available_at' => now()]);
    }

    public function down(): void
    {
        Schema::table('linehaul_driver_earnings', function (Blueprint $table) {
            $table->dropColumn('available_at');
        });
        Schema::table('center_revenue_ledger_entries', function (Blueprint $table) {
            $table->dropColumn('posted_at');
        });
        Schema::table('settlement_entries', function (Blueprint $table) {
            $table->dropForeign(['distributed_by_user_id']);
            $table->dropColumn(['distribution_reference', 'distributed_at', 'distributed_by_user_id']);
        });
    }
};
