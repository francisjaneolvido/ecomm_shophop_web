<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Recover only the known empty prefix left by an interrupted first deployment.
        // Existing business tables and data are never altered or backfilled.
        $hasProfile = Schema::hasTable('rider_batch_vehicle_profiles');
        $hasBatch = Schema::hasTable('rider_batches');
        if ($hasProfile || $hasBatch) {
            if (! $hasProfile || ! $hasBatch || Schema::hasTable('rider_batch_items') || Schema::hasTable('rider_batch_events')
                || DB::table('rider_batch_vehicle_profiles')->exists()
                || DB::table('rider_batches')->exists()) {
                throw new RuntimeException('Unexpected or populated partial rider-batch schema. Review before proceeding.');
            }
            $expectedColumns = ['id', 'code', 'logistics_partner_id', 'sorting_center_id', 'rider_id', 'active_rider_id',
                'type', 'status', 'scheduled_start', 'scheduled_end', 'estimated_minutes', 'capacity_snapshot',
                'started_at', 'completed_at', 'cancelled_at', 'created_at', 'updated_at'];
            $actualColumns = Schema::getColumnListing('rider_batches');
            sort($expectedColumns);
            sort($actualColumns);
            if ($actualColumns !== $expectedColumns) {
                throw new RuntimeException('Unexpected partial rider_batches columns.');
            }
            // Finish only the missing indexes of the newly created empty batch table.
            Schema::table('rider_batches', function (Blueprint $t) {
                if (! Schema::hasIndex('rider_batches', 'rb_company_branch_status_idx')) {
                    $t->index(['logistics_partner_id', 'sorting_center_id', 'status'], 'rb_company_branch_status_idx');
                }
                if (! Schema::hasIndex('rider_batches', 'rider_batches_code_unique')) {
                    $t->unique('code', 'rider_batches_code_unique');
                }
                if (! Schema::hasIndex('rider_batches', 'rider_batches_active_rider_id_unique')) {
                    $t->unique('active_rider_id', 'rider_batches_active_rider_id_unique');
                }
            });
        } else {
            Schema::create('rider_batch_vehicle_profiles', function (Blueprint $t) {
                $t->id();
                $t->foreignId('rider_id')->unique()->constrained('riders')->restrictOnDelete();
                $t->string('vehicle_type', 80);
                $t->string('plate_number', 40)->nullable();
                $t->json('limits');
                $t->foreignId('configured_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                $t->foreignId('configured_by_staff_id')->nullable()->constrained('logistics_branch_staff')->nullOnDelete();
                $t->timestamps();
            });
            Schema::create('rider_batches', function (Blueprint $t) {
                $t->id();
                $t->string('code', 40)->unique();
                $t->foreignId('logistics_partner_id')->constrained()->restrictOnDelete();
                $t->foreignId('sorting_center_id')->constrained('sorting_centers')->restrictOnDelete();
                $t->foreignId('rider_id')->constrained('riders')->restrictOnDelete();
                // NULL for queued/closed batches; unique for an in-progress batch.
                $t->foreignId('active_rider_id')->nullable()->unique()->constrained('riders')->restrictOnDelete();
                $t->string('type', 12);
                $t->string('status', 20)->default('queued');
                $t->dateTime('scheduled_start');
                $t->dateTime('scheduled_end');
                $t->unsignedInteger('estimated_minutes');
                $t->json('capacity_snapshot');
                $t->timestamp('started_at')->nullable();
                $t->timestamp('completed_at')->nullable();
                $t->timestamp('cancelled_at')->nullable();
                $t->timestamps();
                $t->index(['rider_id', 'status', 'scheduled_start']);
                $t->index(['logistics_partner_id', 'sorting_center_id', 'status'], 'rb_company_branch_status_idx');
            });
        }
        Schema::create('rider_batch_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('rider_batch_id')->constrained()->restrictOnDelete();
            $t->foreignId('delivery_id')->constrained()->restrictOnDelete();
            // One live reservation across all batches; closed history retains delivery_id.
            $t->foreignId('reserved_delivery_id')->nullable()->unique()->constrained('deliveries')->restrictOnDelete();
            $t->unsignedInteger('stop_sequence');
            $t->string('stop_key', 64);
            $t->json('stop');
            $t->decimal('weight_kg', 12, 3);
            $t->decimal('volume_m3', 14, 6);
            $t->unsignedBigInteger('receipt_event_floor')->default(0);
            $t->json('outcome_snapshot')->nullable();
            $t->text('pickup_failure_reason')->nullable();
            $t->timestamp('pickup_failed_at')->nullable();
            $t->timestamps();
            $t->unique(['rider_batch_id', 'delivery_id']);
        });
        Schema::create('rider_batch_events', function (Blueprint $t) {
            $t->id();
            $t->foreignId('rider_batch_id')->constrained()->restrictOnDelete();
            $t->string('event', 60);
            $t->string('actor_type', 30);
            $t->unsignedBigInteger('actor_id');
            $t->json('details');
            $t->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rider_batch_events');
        Schema::dropIfExists('rider_batch_items');
        Schema::dropIfExists('rider_batches');
        Schema::dropIfExists('rider_batch_vehicle_profiles');
    }
};
