<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->enum('compliance_status', ['pending_review', 'approved', 'rejected'])
                  ->default('pending_review')
                  ->after('status');

            $table->string('rejection_reason')->nullable()->after('compliance_status');
            $table->text('rejection_notes')->nullable()->after('rejection_reason');
            $table->timestamp('submitted_at')->nullable()->after('rejection_notes');
            $table->timestamp('reviewed_at')->nullable()->after('submitted_at');

            $table->unsignedBigInteger('reviewed_by')->nullable()->after('reviewed_at');
            $table->foreign('reviewed_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['reviewed_by']);
            $table->dropColumn([
                'compliance_status',
                'rejection_reason',
                'rejection_notes',
                'submitted_at',
                'reviewed_at',
                'reviewed_by',
            ]);
        });
    }
};