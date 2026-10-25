<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('weight_kg', 8, 3)->default(0.500)->after('price');
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->decimal('weight_kg', 8, 3)->nullable()->after('price');
        });

        Schema::table('vouchers', function (Blueprint $table) {
            $table->string('kind', 20)->default('merchandise')->after('code'); // merchandise | shipping
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('seller_shipping_discount', 12, 2)->default(0)->after('voucher_discount');
        });

        Schema::table('order_financials', function (Blueprint $table) {
            $table->decimal('seller_shipping_discount', 12, 2)->default(0)->after('seller_discount');
            $table->string('shipping_route_band', 24)->nullable()->after('shipping_fee');
            $table->decimal('shipping_weight_kg', 10, 3)->default(0)->after('shipping_route_band');
            $table->decimal('shipping_base_fee', 12, 2)->default(0)->after('shipping_weight_kg');
            $table->decimal('shipping_distance_fee', 12, 2)->default(0)->after('shipping_base_fee');
            $table->decimal('shipping_weight_fee', 12, 2)->default(0)->after('shipping_distance_fee');
            $table->decimal('shipping_linehaul_fee', 12, 2)->default(0)->after('shipping_weight_fee');
            $table->decimal('shipping_express_surcharge', 12, 2)->default(0)->after('shipping_linehaul_fee');
        });

        Schema::create('marketplace_rate_settings', function (Blueprint $table) {
            $table->id();
            $table->decimal('commission_percent', 5, 2)->default(10);
            $table->decimal('base_shipping_fee', 12, 2)->default(40);
            $table->decimal('local_distance_fee', 12, 2)->default(20);
            $table->decimal('same_province_distance_fee', 12, 2)->default(35);
            $table->decimal('interprovince_distance_fee', 12, 2)->default(50);
            $table->decimal('same_province_linehaul_fee', 12, 2)->default(25);
            $table->decimal('interprovince_linehaul_fee', 12, 2)->default(50);
            $table->decimal('included_weight_kg', 8, 3)->default(1);
            $table->decimal('extra_weight_fee_per_kg', 12, 2)->default(15);
            $table->decimal('express_surcharge_percent', 5, 2)->default(40);
            $table->decimal('pickup_rider_fee', 12, 2)->default(20);
            $table->decimal('delivery_rider_fee', 12, 2)->default(25);
            $table->decimal('origin_branch_fee', 12, 2)->default(10);
            $table->decimal('destination_branch_fee', 12, 2)->default(10);
            $table->decimal('province_hub_fee', 12, 2)->default(10);
            $table->decimal('linehaul_branch_hub_driver_fee', 12, 2)->default(500);
            $table->decimal('linehaul_hub_hub_driver_fee', 12, 2)->default(900);
            $table->timestamps();
        });

        DB::table('marketplace_rate_settings')->insert([
            'id' => 1,
            'commission_percent' => 10,
            'base_shipping_fee' => 40,
            'local_distance_fee' => 20,
            'same_province_distance_fee' => 35,
            'interprovince_distance_fee' => 50,
            'same_province_linehaul_fee' => 25,
            'interprovince_linehaul_fee' => 50,
            'included_weight_kg' => 1,
            'extra_weight_fee_per_kg' => 15,
            'express_surcharge_percent' => 40,
            'pickup_rider_fee' => 20,
            'delivery_rider_fee' => 25,
            'origin_branch_fee' => 10,
            'destination_branch_fee' => 10,
            'province_hub_fee' => 10,
            'linehaul_branch_hub_driver_fee' => 500,
            'linehaul_hub_hub_driver_fee' => 900,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Schema::create('logistics_order_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->unique()->constrained('orders')->cascadeOnDelete();
            $table->foreignId('logistics_partner_id')->constrained('logistics_partners')->cascadeOnDelete();
            $table->decimal('gross_shipping', 12, 2)->default(0);
            $table->decimal('pickup_rider_fee', 12, 2)->default(0);
            $table->decimal('delivery_rider_fee', 12, 2)->default(0);
            $table->decimal('origin_branch_amount', 12, 2)->default(0);
            $table->decimal('destination_branch_amount', 12, 2)->default(0);
            $table->unsignedInteger('province_hub_count')->default(0);
            $table->decimal('province_hub_amount', 12, 2)->default(0);
            $table->decimal('linehaul_pool', 12, 2)->default(0);
            $table->decimal('logistics_margin', 12, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('center_revenue_ledger_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->nullable()->constrained('orders')->cascadeOnDelete();
            $table->foreignId('logistics_partner_id')->constrained('logistics_partners')->cascadeOnDelete();
            $table->foreignId('sorting_center_id')->constrained('sorting_centers')->cascadeOnDelete();
            $table->string('kind', 32); // origin_handling | destination_handling | hub_handling
            $table->decimal('amount', 12, 2);
            $table->string('status', 20)->default('allocated');
            $table->string('note', 255)->nullable();
            $table->timestamps();
            $table->unique(['order_id', 'sorting_center_id', 'kind'], 'center_revenue_order_center_kind_unique');
            $table->index(['logistics_partner_id', 'sorting_center_id', 'kind'], 'center_revenue_partner_center_kind_idx');
        });

        Schema::create('linehaul_driver_earnings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('linehaul_trip_id')->unique()->constrained('linehaul_trips')->cascadeOnDelete();
            $table->foreignId('logistics_partner_id')->constrained('logistics_partners')->cascadeOnDelete();
            $table->foreignId('driver_id')->constrained('linehaul_drivers')->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('status', 20)->default('pending'); // pending | paid
            $table->string('payment_reference', 120)->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('paid_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['driver_id', 'status'], 'linehaul_driver_earning_driver_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('linehaul_driver_earnings');
        Schema::dropIfExists('center_revenue_ledger_entries');
        Schema::dropIfExists('logistics_order_allocations');
        Schema::dropIfExists('marketplace_rate_settings');

        Schema::table('order_financials', function (Blueprint $table) {
            $table->dropColumn([
                'seller_shipping_discount', 'shipping_route_band', 'shipping_weight_kg',
                'shipping_base_fee', 'shipping_distance_fee', 'shipping_weight_fee',
                'shipping_linehaul_fee', 'shipping_express_surcharge',
            ]);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('seller_shipping_discount');
        });

        Schema::table('vouchers', function (Blueprint $table) {
            $table->dropColumn('kind');
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn('weight_kg');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('weight_kg');
        });
    }
};
