<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Products created before the admin review flow have no submitted_at.
     * Mark them approved once so the existing catalogue does not disappear
     * from the buyer side. New products always get submitted_at from the
     * seller form, so they are never touched by this.
     */
    public function up(): void
    {
        if (! Schema::hasTable('products') || ! Schema::hasColumn('products', 'compliance_status')) {
            return;
        }

        DB::table('products')
            ->where('compliance_status', 'pending_review')
            ->whereNull('submitted_at')
            ->update([
                'compliance_status' => 'approved',
                'reviewed_at' => now(),
            ]);
    }

    public function down(): void
    {
        // Intentionally empty: we cannot know which rows were approved by this migration.
    }
};