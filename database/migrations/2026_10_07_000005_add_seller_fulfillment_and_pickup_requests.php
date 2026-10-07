<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $orderColumns = [
            'tracking_code' => fn (Blueprint $table) => $table->string('tracking_code', 40)->nullable()->unique(),
            'delivery_region_code' => fn (Blueprint $table) => $table->string('delivery_region_code', 20)->nullable(),
            'delivery_region_name' => fn (Blueprint $table) => $table->string('delivery_region_name', 150)->nullable(),
            'delivery_province_code' => fn (Blueprint $table) => $table->string('delivery_province_code', 20)->nullable(),
            'delivery_province_name' => fn (Blueprint $table) => $table->string('delivery_province_name', 150)->nullable(),
            'delivery_municipality_code' => fn (Blueprint $table) => $table->string('delivery_municipality_code', 20)->nullable(),
            'delivery_municipality_name' => fn (Blueprint $table) => $table->string('delivery_municipality_name', 150)->nullable(),
            'delivery_barangay_code' => fn (Blueprint $table) => $table->string('delivery_barangay_code', 20)->nullable(),
            'delivery_barangay_name' => fn (Blueprint $table) => $table->string('delivery_barangay_name', 150)->nullable(),
            'delivery_street' => fn (Blueprint $table) => $table->string('delivery_street', 255)->nullable(),
        ];

        foreach ($orderColumns as $column => $definition) {
            if (! Schema::hasColumn('orders', $column)) {
                Schema::table('orders', function (Blueprint $table) use ($definition) {
                    $definition($table);
                });
            }
        }


        if (! Schema::hasTable('pickup_requests')) {
            Schema::create('pickup_requests', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->unique()->constrained('orders')->cascadeOnDelete();
                $table->foreignId('logistics_partner_id')->constrained('logistics_partners')->restrictOnDelete();
                $table->foreignId('origin_sorting_center_id')->nullable()->constrained('sorting_centers')->nullOnDelete();
                $table->string('status', 30)->default('requested');
                $table->string('origin_province_code', 20)->nullable();
                $table->string('origin_province_name', 150)->nullable();
                $table->string('origin_municipality_code', 20)->nullable();
                $table->string('origin_municipality_name', 150)->nullable();
                $table->string('origin_barangay_code', 20)->nullable();
                $table->string('origin_barangay_name', 150)->nullable();
                $table->string('origin_street', 255)->nullable();
                $table->timestamp('requested_at')->nullable();
                $table->timestamp('assigned_at')->nullable();
                $table->timestamp('picked_up_at')->nullable();
                $table->timestamp('received_at')->nullable();
                $table->timestamps();

                $table->index(['logistics_partner_id', 'status'], 'pickup_requests_partner_status_idx');
            });
        }

        // Preserve historical Buyer address data as a structured Order snapshot where possible.
        if (Schema::hasTable('buyers')) {
            DB::table('orders')->orderBy('id')->get()->each(function ($order) {
                $buyer = DB::table('buyers')->where('id', $order->buyer_id)->first();
                if (! $buyer) {
                    return;
                }

                $updates = [];
                foreach ([
                    'province_code' => 'delivery_province_code',
                    'province_name' => 'delivery_province_name',
                    'municipality_code' => 'delivery_municipality_code',
                    'municipality_name' => 'delivery_municipality_name',
                    'barangay_code' => 'delivery_barangay_code',
                    'barangay_name' => 'delivery_barangay_name',
                    'street_address' => 'delivery_street',
                ] as $source => $target) {
                    if (property_exists($buyer, $source) && $buyer->{$source}) {
                        $updates[$target] = $buyer->{$source};
                    }
                }
                if ($updates) {
                    DB::table('orders')->where('id', $order->id)->update($updates);
                }
            });
        }

        // A deterministic shipment reference lets existing in-flight Orders print a label immediately.
        DB::table('orders')->whereNull('tracking_code')->orderBy('id')->get()->each(function ($order) {
            $date = $order->created_at ? date('Ymd', strtotime($order->created_at)) : date('Ymd');
            DB::table('orders')->where('id', $order->id)->update([
                'tracking_code' => 'SHP-'.$date.'-'.str_pad((string) $order->id, 6, '0', STR_PAD_LEFT),
            ]);
        });

        // Keep Delivery and Seller label references identical for existing Logistics records.
        if (Schema::hasTable('deliveries') && Schema::hasColumn('deliveries', 'tracking_code')) {
            DB::table('deliveries')->orderBy('id')->get()->each(function ($delivery) {
                $order = DB::table('orders')->where('id', $delivery->order_id)->first();
                if ($order?->tracking_code && $delivery->tracking_code !== $order->tracking_code) {
                    DB::table('deliveries')->where('id', $delivery->id)->update(['tracking_code' => $order->tracking_code]);
                }

                if ($order && ! DB::table('pickup_requests')->where('order_id', $order->id)->exists()) {
                    $seller = DB::table('sellers')->where('id', $order->seller_id)->first();
                    DB::table('pickup_requests')->insert([
                        'order_id' => $order->id,
                        'logistics_partner_id' => $delivery->logistics_partner_id,
                        'origin_sorting_center_id' => $delivery->origin_sorting_center_id ?? null,
                        'status' => ($delivery->picked_up_at ?? null) ? 'picked_up' : 'assigned',
                        'origin_province_code' => $seller->province_code ?? null,
                        'origin_province_name' => $seller->province_name ?? null,
                        'origin_municipality_code' => $seller->municipality_code ?? null,
                        'origin_municipality_name' => $seller->municipality_name ?? null,
                        'origin_barangay_code' => $seller->barangay_code ?? null,
                        'origin_barangay_name' => $seller->barangay_name ?? null,
                        'origin_street' => $seller->street_address ?? null,
                        'requested_at' => $order->updated_at ?? now(),
                        'assigned_at' => $delivery->assigned_at ?? null,
                        'picked_up_at' => $delivery->picked_up_at ?? null,
                        'created_at' => $order->updated_at ?? now(),
                        'updated_at' => now(),
                    ]);
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pickup_requests');

        $columns = [
            'tracking_code',
            'delivery_region_code', 'delivery_region_name',
            'delivery_province_code', 'delivery_province_name',
            'delivery_municipality_code', 'delivery_municipality_name',
            'delivery_barangay_code', 'delivery_barangay_name',
            'delivery_street',
        ];
        $existing = array_values(array_filter($columns, fn ($column) => Schema::hasColumn('orders', $column)));
        if ($existing) {
            Schema::table('orders', function (Blueprint $table) use ($existing) {
                $table->dropColumn($existing);
            });
        }
    }
};
