<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rider_lifecycle_profiles', function (Blueprint $t) {
            $t->id();
            $t->foreignId('rider_id')->unique()->constrained('riders')->restrictOnDelete();
            $t->foreignId('preferred_branch_id')->nullable()->constrained('sorting_centers')->restrictOnDelete();
            $t->string('application_status', 32)->default('pending_review');
            $t->string('operational_status', 32)->default('pending_activation');
            $t->string('availability_state', 16)->default('offline');
            $t->boolean('legacy_onboarding')->default(false);
            $t->json('preferred_coverage');
            $t->json('accepted_coverage')->nullable();
            $t->boolean('assignment_confirmation_required')->default(false);
            $t->json('document_request')->nullable();
            $t->timestamp('activated_at')->nullable();
            $t->timestamp('deactivated_at')->nullable();
            $t->timestamps();
            $t->index(['application_status', 'operational_status'], 'rmp_states_idx');
        });
        Schema::create('rider_barangay_capacities', function (Blueprint $t) {
            $t->id();
            $t->foreignId('logistics_partner_id')->constrained()->restrictOnDelete();
            $t->string('location_key', 64);
            $t->json('location');
            $t->unsignedInteger('rider_limit')->default(6);
            $t->string('updated_by_type', 12);
            $t->unsignedBigInteger('updated_by_id');
            $t->timestamps();
            $t->unique(['logistics_partner_id', 'location_key'], 'rmp_capacity_location_unique');
        });
        Schema::create('rider_capacity_overrides', function (Blueprint $t) {
            $t->id();
            $t->foreignId('logistics_partner_id')->constrained()->restrictOnDelete();
            $t->foreignId('rider_id')->constrained()->restrictOnDelete();
            $t->string('location_key', 64);
            $t->text('reason');
            $t->boolean('active')->default(true);
            $t->string('approved_by_type', 12);
            $t->unsignedBigInteger('approved_by_id');
            $t->timestamps();
            $t->unique(['rider_id', 'location_key'], 'rmp_override_rider_location_unique');
        });
        Schema::create('rider_transfer_requests', function (Blueprint $t) {
            $t->id();
            $t->foreignId('logistics_partner_id')->constrained()->restrictOnDelete();
            $t->foreignId('rider_id')->constrained()->restrictOnDelete();
            $t->foreignId('pending_rider_id')->nullable()->unique('rmp_transfer_pending_unique')->constrained('riders')->restrictOnDelete();
            $t->foreignId('from_branch_id')->constrained('sorting_centers')->restrictOnDelete();
            $t->foreignId('to_branch_id')->constrained('sorting_centers')->restrictOnDelete();
            $t->string('status', 16)->default('pending');
            $t->text('reason');
            $t->text('decision_reason')->nullable();
            $t->string('requested_by_type', 12);
            $t->unsignedBigInteger('requested_by_id');
            $t->string('decided_by_type', 12)->nullable();
            $t->unsignedBigInteger('decided_by_id')->nullable();
            $t->timestamp('decided_at')->nullable();
            $t->timestamps();
            $t->index(['logistics_partner_id', 'status'], 'rmp_transfer_company_state_idx');
        });
        Schema::create('rider_work_schedules', function (Blueprint $t) {
            $t->id();
            $t->foreignId('logistics_partner_id')->constrained()->restrictOnDelete();
            $t->foreignId('sorting_center_id')->constrained()->restrictOnDelete();
            $t->foreignId('rider_id')->constrained()->restrictOnDelete();
            $t->dateTime('starts_at');
            $t->dateTime('ends_at');
            $t->string('status', 16)->default('active');
            $t->string('created_by_type', 12);
            $t->unsignedBigInteger('created_by_id');
            $t->timestamps();
            $t->index(['rider_id', 'status', 'starts_at'], 'rmp_schedule_rider_state_idx');
        });
        Schema::create('rider_management_events', function (Blueprint $t) {
            $t->id();
            $t->foreignId('logistics_partner_id')->constrained()->restrictOnDelete();
            $t->foreignId('sorting_center_id')->nullable()->constrained()->restrictOnDelete();
            $t->foreignId('rider_id')->constrained()->restrictOnDelete();
            $t->string('event', 64);
            $t->string('actor_type', 12);
            $t->unsignedBigInteger('actor_id');
            $t->json('details');
            $t->timestamp('created_at')->useCurrent();
            $t->index(['logistics_partner_id', 'rider_id', 'id'], 'rmp_event_company_rider_idx');
        });
    }

    public function down(): void
    {
        foreach (['rider_management_events', 'rider_work_schedules', 'rider_transfer_requests', 'rider_capacity_overrides', 'rider_barangay_capacities', 'rider_lifecycle_profiles'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
