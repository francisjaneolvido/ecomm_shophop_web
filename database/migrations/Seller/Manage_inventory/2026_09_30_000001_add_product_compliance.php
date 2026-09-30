<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Rollout preserves the existing catalogue without inventing human review history.
return new class extends Migration
{
    public function up(): void
    {
        // A repeated forward invocation must never approve merchandise submitted after rollout.
        if (Schema::hasColumn('products', 'compliance_status')) {
            return;
        }

        // Capture the pre-rollout catalogue boundary so concurrent new auto-incremented rows cannot inherit approval.
        $lastExistingProductId = DB::table('products')->max('id');

        Schema::table('products', function (Blueprint $table) {
            // The database default quarantines new merchandise independently of archive state.
            $table->enum('compliance_status', ['pending_review', 'approved', 'rejected'])->default('pending_review')->index();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('rejection_reason')->nullable();
            $table->text('rejection_notes')->nullable();
        });

        // Compatibility approval applies only to the captured old catalogue; new submissions keep the pending default.
        if ($lastExistingProductId !== null) {
            DB::table('products')->where('id', '<=', $lastExistingProductId)->update(['compliance_status' => 'approved']);
        }
    }

    public function down(): void
    {
        // Removing the feature removes its reviewer constraint together with its metadata.
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['reviewed_by']);
            $table->dropIndex(['compliance_status']);
            $table->dropColumn(['compliance_status', 'submitted_at', 'reviewed_at', 'reviewed_by', 'rejection_reason', 'rejection_notes']);
        });
    }
};
