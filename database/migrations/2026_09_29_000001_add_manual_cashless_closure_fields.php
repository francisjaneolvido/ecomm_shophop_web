<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Persist one deadline and a human-readable closure event on the Buyer-owned group payment.
        Schema::table('manual_cashless_payments', function (Blueprint $table) {
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamp('closed_at')->nullable();
            $table->foreignId('closed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('closure_reason')->nullable();
        });

        // Grandfather unresolved pre-feature rows for a full day from rollout, regardless of old review dates.
        DB::table('manual_cashless_payments')
            ->whereIn('status', ['awaiting_proof', 'rejected'])
            ->update(['expires_at' => now()->addDay()]);
    }

    public function down(): void
    {
        Schema::table('manual_cashless_payments', function (Blueprint $table) {
            $table->dropForeign(['closed_by_user_id']);
            $table->dropColumn(['expires_at', 'closed_at', 'closed_by_user_id', 'closure_reason']);
        });
    }
};
