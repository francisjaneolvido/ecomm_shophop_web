<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('products')) {
            return;
        }

        // This project also contains a later compliance migration. Some existing
        // databases may already have all or part of these columns, so add only
        // what is missing instead of failing with a duplicate-column error.
        if (! Schema::hasColumn('products', 'compliance_status')) {
            Schema::table('products', function (Blueprint $table) {
                $table->enum('compliance_status', ['pending_review', 'approved', 'rejected'])
                    ->default('pending_review')
                    ->after('status');
            });
        }

        if (! Schema::hasColumn('products', 'rejection_reason')) {
            Schema::table('products', function (Blueprint $table) {
                $table->string('rejection_reason')->nullable()->after('compliance_status');
            });
        }

        if (! Schema::hasColumn('products', 'rejection_notes')) {
            Schema::table('products', function (Blueprint $table) {
                $table->text('rejection_notes')->nullable()->after('rejection_reason');
            });
        }

        if (! Schema::hasColumn('products', 'submitted_at')) {
            Schema::table('products', function (Blueprint $table) {
                $table->timestamp('submitted_at')->nullable()->after('rejection_notes');
            });
        }

        if (! Schema::hasColumn('products', 'reviewed_at')) {
            Schema::table('products', function (Blueprint $table) {
                $table->timestamp('reviewed_at')->nullable()->after('submitted_at');
            });
        }

        if (! Schema::hasColumn('products', 'reviewed_by')) {
            Schema::table('products', function (Blueprint $table) {
                $table->unsignedBigInteger('reviewed_by')->nullable()->after('reviewed_at');
                $table->foreign('reviewed_by')->references('id')->on('users')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        // Intentionally left empty. These compliance columns can already belong
        // to the later product-compliance migration on existing databases.
        // Dropping them here could remove fields that this migration did not create.
    }
};
