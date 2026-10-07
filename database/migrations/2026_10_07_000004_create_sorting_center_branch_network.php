<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sorting_centers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('logistics_partner_id')->constrained('logistics_partners')->cascadeOnDelete();
            $table->string('code', 50);
            $table->string('name', 180);
            $table->boolean('is_main')->default(false);
            $table->string('status', 20)->default('active');
            $table->string('contact_no', 30)->nullable();
            $table->string('region', 150);
            $table->string('province', 150);
            $table->string('municipality', 150);
            $table->string('barangay', 150);
            $table->string('street_no', 180);
            $table->string('unit_no', 180)->nullable();
            $table->timestamps();

            $table->unique(['logistics_partner_id', 'code'], 'sorting_centers_partner_code_unique');
            $table->index(['logistics_partner_id', 'status'], 'sorting_centers_partner_status_idx');
        });

        Schema::table('logistics_coverage_areas', function (Blueprint $table) {
            $table->foreignId('sorting_center_id')->nullable()->after('logistics_partner_id')
                ->constrained('sorting_centers')->cascadeOnDelete();
        });

        Schema::table('riders', function (Blueprint $table) {
            $table->foreignId('sorting_center_id')->nullable()->after('logistics_partner_id')
                ->constrained('sorting_centers')->nullOnDelete();
        });

        Schema::table('deliveries', function (Blueprint $table) {
            $table->foreignId('origin_sorting_center_id')->nullable()->after('logistics_partner_id')
                ->constrained('sorting_centers')->nullOnDelete();
            $table->foreignId('current_sorting_center_id')->nullable()->after('origin_sorting_center_id')
                ->constrained('sorting_centers')->nullOnDelete();
            $table->foreignId('destination_sorting_center_id')->nullable()->after('current_sorting_center_id')
                ->constrained('sorting_centers')->nullOnDelete();
        });

        Schema::create('parcel_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_id')->constrained('deliveries')->cascadeOnDelete();
            $table->foreignId('from_sorting_center_id')->constrained('sorting_centers')->restrictOnDelete();
            $table->foreignId('to_sorting_center_id')->constrained('sorting_centers')->restrictOnDelete();
            $table->string('status', 20)->default('in_transit');
            $table->timestamp('dispatched_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamps();

            $table->index(['delivery_id', 'status'], 'parcel_transfers_delivery_status_idx');
        });

        // Existing single-center partners become a company with one Main Sorting Center.
        DB::table('logistics_partners')->orderBy('id')->get()->each(function ($partner) {
            $centerId = DB::table('sorting_centers')->insertGetId([
                'logistics_partner_id' => $partner->id,
                'code' => 'SC-'.str_pad((string) $partner->id, 4, '0', STR_PAD_LEFT).'-MAIN',
                'name' => ($partner->company_name ?: 'ShopHop Logistics').' - Main Sorting Center',
                'is_main' => true,
                'status' => 'active',
                'contact_no' => $partner->contact_no,
                'region' => $partner->region,
                'province' => $partner->province,
                'municipality' => $partner->municipality,
                'barangay' => $partner->barangay,
                'street_no' => $partner->street_no,
                'unit_no' => $partner->unit_no,
                'created_at' => $partner->created_at ?? now(),
                'updated_at' => $partner->updated_at ?? now(),
            ]);

            DB::table('logistics_coverage_areas')
                ->where('logistics_partner_id', $partner->id)
                ->update(['sorting_center_id' => $centerId]);

            DB::table('riders')
                ->where('logistics_partner_id', $partner->id)
                ->update(['sorting_center_id' => $centerId]);

            DB::table('deliveries')
                ->where('logistics_partner_id', $partner->id)
                ->update([
                    'origin_sorting_center_id' => $centerId,
                    'current_sorting_center_id' => $centerId,
                    'destination_sorting_center_id' => $centerId,
                ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parcel_transfers');

        Schema::table('deliveries', function (Blueprint $table) {
            $table->dropConstrainedForeignId('destination_sorting_center_id');
            $table->dropConstrainedForeignId('current_sorting_center_id');
            $table->dropConstrainedForeignId('origin_sorting_center_id');
        });

        Schema::table('riders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('sorting_center_id');
        });

        Schema::table('logistics_coverage_areas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('sorting_center_id');
        });

        Schema::dropIfExists('sorting_centers');
    }
};
