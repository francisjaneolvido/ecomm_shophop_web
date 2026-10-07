<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('riders', function (Blueprint $table) {
            // A rider can be assigned to one operational delivery area while remaining owned by one partner.
            $table->foreignId('coverage_area_id')->nullable()->after('logistics_partner_id')
                ->constrained('logistics_coverage_areas')->nullOnDelete();
            $table->string('availability_status', 20)->default('available')->after('status');
        });

        Schema::table('deliveries', function (Blueprint $table) {
            // One tracking code follows the same Seller Order across pickup, sorting, and final delivery.
            $table->string('tracking_code', 40)->nullable()->unique()->after('id');
            $table->foreignId('destination_area_id')->nullable()->after('logistics_partner_id')
                ->constrained('logistics_coverage_areas')->nullOnDelete();
            $table->foreignId('delivery_rider_id')->nullable()->after('rider_id')
                ->constrained('riders')->restrictOnDelete();

            // Sorting-center milestones required by the approved ERP flow.
            $table->timestamp('pickup_accepted_at')->nullable()->after('assigned_at');
            $table->timestamp('at_sorting_center_at')->nullable()->after('picked_up_at');
            $table->timestamp('sorted_at')->nullable()->after('at_sorting_center_at');
            $table->timestamp('delivery_assigned_at')->nullable()->after('sorted_at');
            $table->timestamp('out_for_delivery_at')->nullable()->after('delivery_assigned_at');
            $table->timestamp('delivery_failed_at')->nullable()->after('delivered_at');
            $table->text('failure_reason')->nullable()->after('delivery_failed_at');
            $table->timestamp('returned_at')->nullable()->after('failure_reason');
            $table->unsignedSmallInteger('delivery_attempts')->default(0)->after('returned_at');
        });

        // Preserve existing assignments while giving every persisted shipment a scannable tracking code.
        DB::table('deliveries')
            ->leftJoin('orders', 'orders.id', '=', 'deliveries.order_id')
            ->select('deliveries.id', 'deliveries.order_id', 'deliveries.status', 'deliveries.rider_id',
                'deliveries.delivered_rider_id', 'deliveries.in_transit_at', 'orders.created_at as order_created_at')
            ->orderBy('deliveries.id')
            ->get()
            ->each(function ($delivery) {
                $date = $delivery->order_created_at
                    ? Carbon::parse($delivery->order_created_at)->format('Ymd')
                    : now()->format('Ymd');
                $updates = [
                    'tracking_code' => 'SHP-'.$date.'-'.str_pad((string) $delivery->order_id, 6, '0', STR_PAD_LEFT),
                ];

                // The previous in_transit stage represented the rider's final-mile movement.
                if ($delivery->status === 'in_transit') {
                    $updates['status'] = 'out_for_delivery';
                    $updates['delivery_rider_id'] = $delivery->rider_id;
                    $updates['out_for_delivery_at'] = $delivery->in_transit_at;
                    $updates['delivery_attempts'] = 1;
                } elseif ($delivery->status === 'delivered') {
                    $updates['delivery_rider_id'] = $delivery->delivered_rider_id ?: $delivery->rider_id;
                    $updates['delivery_attempts'] = 1;
                }

                DB::table('deliveries')->where('id', $delivery->id)->update($updates);
            });
    }

    public function down(): void
    {
        Schema::table('deliveries', function (Blueprint $table) {
            $table->dropUnique(['tracking_code']);
            $table->dropConstrainedForeignId('destination_area_id');
            $table->dropConstrainedForeignId('delivery_rider_id');
            $table->dropColumn([
                'tracking_code',
                'pickup_accepted_at',
                'at_sorting_center_at',
                'sorted_at',
                'delivery_assigned_at',
                'out_for_delivery_at',
                'delivery_failed_at',
                'failure_reason',
                'returned_at',
                'delivery_attempts',
            ]);
        });

        Schema::table('riders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('coverage_area_id');
            $table->dropColumn('availability_status');
        });
    }
};
