<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cod_discrepancy_cases', function (Blueprint $t) {
            $t->id();
            $t->uuid('reference')->unique();
            $t->foreignId('cod_settlement_id')->unique()->constrained('cod_settlements')->restrictOnDelete();
            $t->foreignId('logistics_partner_id')->constrained()->restrictOnDelete();
            $t->foreignId('sorting_center_id')->nullable()->constrained('sorting_centers')->restrictOnDelete();
            $t->bigInteger('opening_difference_minor');
            $t->string('status', 24)->default('open');
            $t->string('origin', 24)->default('verification');
            $t->foreignId('opened_by_user_id')->nullable()->constrained('users')->restrictOnDelete();
            $t->timestamp('opened_at');
            $t->timestamp('resolved_at')->nullable();
            $t->timestamps();
            $t->index(['logistics_partner_id', 'status']);
        });
        Schema::create('cod_discrepancy_resolutions', function (Blueprint $t) {
            $t->id();
            $t->foreignId('case_id')->constrained('cod_discrepancy_cases')->restrictOnDelete();
            $t->string('kind', 24);
            $t->bigInteger('amount_minor');
            $t->bigInteger('received_snapshot_minor');
            $t->text('reason');
            $t->string('evidence_reference', 160);
            $t->string('recipient', 160)->nullable();
            $t->string('status', 24)->default('pending');
            $t->foreignId('proposed_by_user_id')->constrained('users')->restrictOnDelete();
            $t->foreignId('reviewed_by_user_id')->nullable()->constrained('users')->restrictOnDelete();
            $t->text('review_notes')->nullable();
            $t->timestamp('reviewed_at')->nullable();
            $t->foreignId('completed_by_user_id')->nullable()->constrained('users')->restrictOnDelete();
            $t->timestamp('completed_at')->nullable();
            $t->timestamps();
        });
        Schema::create('cod_cash_adjustments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('resolution_id')->unique()->constrained('cod_discrepancy_resolutions')->restrictOnDelete();
            $t->foreignId('cod_settlement_id')->constrained('cod_settlements')->restrictOnDelete();
            $t->foreignId('logistics_partner_id')->constrained()->restrictOnDelete();
            $t->bigInteger('amount_minor');
            $t->string('reference', 120);
            $t->string('recipient_acknowledgment', 160);
            $t->foreignId('recorded_by_user_id')->constrained('users')->restrictOnDelete();
            $t->timestamp('recorded_at');
            $t->unique(['logistics_partner_id', 'reference'], 'cod_adjustment_partner_reference');
        });

        // Index existing differences without changing receipts, cash balances or payouts.
        DB::table('cod_settlements')->where('status', 'discrepancy')->orderBy('id')->chunkById(100, function ($rows) {
            foreach ($rows as $cash) {
                $center = DB::table('cod_remittance_items as i')
                    ->join('cod_remittance_batches as b', 'b.id', '=', 'i.batch_id')
                    ->where('i.cod_settlement_id', $cash->id)->orderByDesc('b.id')->value('b.sorting_center_id');
                DB::table('cod_discrepancy_cases')->insert([
                    'reference' => (string) Str::uuid(), 'cod_settlement_id' => $cash->id,
                    'logistics_partner_id' => $cash->logistics_partner_id, 'sorting_center_id' => $center,
                    'opening_difference_minor' => (int) round(((float) $cash->received_amount - (float) $cash->expected_amount) * 100),
                    'status' => 'open', 'origin' => 'migration', 'opened_by_user_id' => $cash->reconciled_by_user_id,
                    'opened_at' => $cash->updated_at ?: $cash->collected_at ?: now(), 'created_at' => now(), 'updated_at' => now(),
                ]);
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cod_cash_adjustments');
        Schema::dropIfExists('cod_discrepancy_resolutions');
        Schema::dropIfExists('cod_discrepancy_cases');
    }
};
