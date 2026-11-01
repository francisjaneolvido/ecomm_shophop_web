<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Earlier interrupted MySQL DDL may have created this table before the
        // migration entry was written. Never drop or recreate existing data.
        if (!Schema::hasTable('operational_service_areas')) {
            Schema::create('operational_service_areas', function (Blueprint $table) {
                $table->id();
                $table->foreignId('logistics_partner_id')->constrained('logistics_partners')->cascadeOnDelete();
                $table->foreignId('sorting_center_id')->constrained('sorting_centers')->cascadeOnDelete();
                $table->string('province', 150)->nullable(); // NCR has no province
                $table->string('municipality', 150);
                $table->string('barangay', 150)->nullable(); // null = entire municipality/city
                $table->enum('service_type', ['pickup', 'delivery', 'both'])->default('both');
                $table->char('location_key', 64);
                $table->timestamps();
                $table->unique(['sorting_center_id', 'location_key'], 'ops_area_center_location_unique');
                $table->index(['logistics_partner_id', 'sorting_center_id']);
            });
        }

        $this->requireColumns('operational_service_areas', [
            'id', 'logistics_partner_id', 'sorting_center_id', 'province',
            'municipality', 'barangay', 'service_type', 'location_key',
            'created_at', 'updated_at',
        ]);
        if (!Schema::hasTable('operational_area_rider')) {
            Schema::create('operational_area_rider', function (Blueprint $table) {
                $table->foreignId('operational_service_area_id')->constrained('operational_service_areas')->cascadeOnDelete();
                $table->foreignId('rider_id')->constrained('riders')->cascadeOnDelete();
                $table->primary(['operational_service_area_id', 'rider_id'], 'ops_area_rider_primary');
            });
        }

        $this->requireColumns('operational_area_rider', [
            'operational_service_area_id', 'rider_id',
        ]);
    }

    /**
     * Refuse to silently accept an unrelated/incomplete existing table.
     * Only the original migration name is recorded if the schema is compatible.
     */
    private function requireColumns(string $table, array $required): void
    {
        if (!Schema::hasTable($table)) {
            throw new \RuntimeException("Missing table {$table}; aborting migration safely.");
        }
        foreach ($required as $column) {
            if (!Schema::hasColumn($table, $column)) {
                throw new \RuntimeException(
                    "Existing table {$table} is missing required column {$column}. "
                    . 'Do not drop the table; inspect its schema and restore from backup if needed.'
                );
            }
        }
    }

    public function down(): void
    {
        // This reconciliation migration may have adopted tables that existed before
        // its history row was created. An automatic rollback could delete their data.
        throw new \RuntimeException(
            'Automatic rollback is disabled for operational service areas to protect existing data. ' .
            'Use a reviewed database backup and a separate, explicit rollback plan.'
        );
    }
};
