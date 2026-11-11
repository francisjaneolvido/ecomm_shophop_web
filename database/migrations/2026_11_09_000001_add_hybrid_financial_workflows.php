<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('marketplace_rate_settings', function (Blueprint $t) {
            $t->decimal('shipping_commission_percent', 5, 2)->default(0);
        });
        Schema::table('order_financials', function (Blueprint $t) {
            // Null denotes a historical snapshot; never infer historical rates from today's settings.
            $t->decimal('product_rate_snapshot', 5, 2)->nullable();
            $t->decimal('shipping_rate_snapshot', 5, 2)->nullable();
            $t->decimal('shipping_commission', 12, 2)->default(0);
            $t->string('financial_model', 20)->default('legacy');
        });
        Schema::create('financial_audit_events', function (Blueprint $t) {
            $t->id();
            $t->string('subject_type', 60);
            $t->unsignedBigInteger('subject_id');
            $t->string('action', 60);
            $t->string('actor_type', 30);
            $t->unsignedBigInteger('actor_id');
            $t->json('details');
            $t->timestamp('created_at');
            $t->index(['subject_type', 'subject_id']);
        });
        Schema::create('cod_remittance_batches', function (Blueprint $t) {
            $t->id();
            $t->uuid('reference')->unique();
            $t->foreignId('logistics_partner_id')->constrained()->restrictOnDelete();
            $t->foreignId('rider_id')->constrained('riders')->restrictOnDelete();
            $t->foreignId('sorting_center_id')->constrained('sorting_centers')->restrictOnDelete();
            $t->string('idempotency_key', 80);
            $t->bigInteger('expected_minor');
            $t->bigInteger('submitted_minor');
            $t->bigInteger('verified_minor')->nullable();
            $t->bigInteger('difference_minor')->nullable();
            $t->string('status', 24)->default('submitted');
            $t->string('receipt_reference', 120);
            $t->foreignId('verified_by_user_id')->nullable()->constrained('users')->restrictOnDelete();
            $t->timestamp('verified_at')->nullable();
            $t->timestamps();
            $t->unique(['rider_id', 'idempotency_key']);
        });
        Schema::create('cod_remittance_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('batch_id')->constrained('cod_remittance_batches')->restrictOnDelete();
            $t->foreignId('cod_settlement_id')->constrained('cod_settlements')->restrictOnDelete();
            $t->bigInteger('expected_minor');
            $t->bigInteger('submitted_minor');
            $t->bigInteger('verified_minor')->nullable();
            $t->unique(['batch_id', 'cod_settlement_id']);
        });
        Schema::create('cash_custody_movements', function (Blueprint $t) {
            $t->id();
            $t->foreignId('batch_id')->unique()->constrained('cod_remittance_batches')->restrictOnDelete();
            $t->foreignId('from_rider_id')->constrained('riders')->restrictOnDelete();
            $t->foreignId('to_sorting_center_id')->constrained('sorting_centers')->restrictOnDelete();
            $t->foreignId('received_by_user_id')->constrained('users')->restrictOnDelete();
            $t->bigInteger('amount_minor');
            $t->string('reference', 120);
            $t->timestamp('received_at');
        });
        Schema::create('partner_compensation_policies', function (Blueprint $t) {
            $t->id();
            $t->foreignId('logistics_partner_id')->constrained()->restrictOnDelete();
            $t->string('personnel_type', 20); // rider or linehaul_driver
            $t->unsignedBigInteger('personnel_id');
            $t->string('type', 24);
            $t->bigInteger('rate_minor');
            $t->string('schedule', 160);
            $t->date('effective_from');
            $t->date('effective_until')->nullable();
            $t->foreignId('created_by_user_id')->constrained('users')->restrictOnDelete();
            $t->timestamps();
            $t->index(['logistics_partner_id', 'personnel_type', 'personnel_id'], 'compensation_personnel_index');
        });
        Schema::create('partner_personnel_earnings', function (Blueprint $t) {
            $t->id();
            $t->foreignId('policy_id')->constrained('partner_compensation_policies')->restrictOnDelete();
            $t->foreignId('logistics_partner_id')->constrained()->restrictOnDelete();
            $t->string('personnel_type', 20);
            $t->unsignedBigInteger('personnel_id');
            $t->string('event_key', 120);
            $t->bigInteger('amount_minor');
            $t->string('status', 20)->default('pending');
            $t->foreignId('approved_by_user_id')->nullable()->constrained('users')->restrictOnDelete();
            $t->timestamp('approved_at')->nullable();
            $t->foreignId('paid_by_user_id')->nullable()->constrained('users')->restrictOnDelete();
            $t->timestamp('paid_at')->nullable();
            $t->string('payment_reference', 120)->nullable();
            $t->json('evidence');
            $t->timestamps();
            $t->unique(['logistics_partner_id', 'event_key'], 'compensation_event_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partner_personnel_earnings');
        Schema::dropIfExists('partner_compensation_policies');
        Schema::dropIfExists('cash_custody_movements');
        Schema::dropIfExists('cod_remittance_items');
        Schema::dropIfExists('cod_remittance_batches');
        Schema::dropIfExists('financial_audit_events');
        Schema::table('order_financials', fn (Blueprint $t) => $t->dropColumn(['product_rate_snapshot', 'shipping_rate_snapshot', 'shipping_commission', 'financial_model']));
        Schema::table('marketplace_rate_settings', fn (Blueprint $t) => $t->dropColumn('shipping_commission_percent'));
    }
};
