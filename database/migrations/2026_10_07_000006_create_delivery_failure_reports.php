<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('delivery_failure_reports')) {
            Schema::create('delivery_failure_reports', function (Blueprint $table) {
                $table->id();
                $table->foreignId('delivery_id')->constrained('deliveries')->cascadeOnDelete();
                $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
                $table->foreignId('logistics_partner_id')->constrained('logistics_partners')->restrictOnDelete();
                $table->foreignId('rider_id')->nullable()->constrained('riders')->nullOnDelete();
                $table->unsignedInteger('attempt_no')->default(1);
                $table->text('reason');
                $table->timestamp('reported_at');
                $table->timestamps();

                $table->unique(['delivery_id', 'attempt_no'], 'delivery_failure_reports_attempt_unique');
                $table->index(['logistics_partner_id', 'reported_at'], 'delivery_failure_reports_partner_date_idx');
            });
        }

        if (Schema::hasTable('deliveries')
            && Schema::hasColumn('deliveries', 'failure_reason')
            && Schema::hasColumn('deliveries', 'delivery_failed_at')) {
            DB::table('deliveries')
                ->whereNotNull('failure_reason')
                ->whereNotNull('delivery_failed_at')
                ->orderBy('id')
                ->get()
                ->each(function ($delivery) {
                    if (DB::table('delivery_failure_reports')->where('delivery_id', $delivery->id)->exists()) {
                        return;
                    }

                    DB::table('delivery_failure_reports')->insert([
                        'delivery_id' => $delivery->id,
                        'order_id' => $delivery->order_id,
                        'logistics_partner_id' => $delivery->logistics_partner_id,
                        'rider_id' => $delivery->delivery_rider_id ?? $delivery->rider_id ?? null,
                        'attempt_no' => max(1, (int) ($delivery->delivery_attempts ?? 1)),
                        'reason' => $delivery->failure_reason,
                        'reported_at' => $delivery->delivery_failed_at,
                        'created_at' => $delivery->delivery_failed_at,
                        'updated_at' => $delivery->delivery_failed_at,
                    ]);
                });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_failure_reports');
    }
};
