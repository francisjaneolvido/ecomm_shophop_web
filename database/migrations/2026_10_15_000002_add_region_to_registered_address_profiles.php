<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        foreach (['buyers', 'sellers', 'riders'] as $table) {
            if (!Schema::hasTable($table)) continue;
            Schema::table($table, function (Blueprint $blueprint) use ($table) {
                if (!Schema::hasColumn($table, 'region_code')) {
                    $blueprint->string('region_code', 20)->nullable();
                }
                if (!Schema::hasColumn($table, 'region_name')) {
                    $blueprint->string('region_name', 150)->nullable();
                }
            });
        }
    }
    public function down(): void
    {
        foreach (['buyers', 'sellers', 'riders'] as $table) {
            if (!Schema::hasTable($table)) continue;
            $columns = array_values(array_filter(['region_code', 'region_name'],
                fn ($column) => Schema::hasColumn($table, $column)));
            if ($columns) Schema::table($table, fn (Blueprint $blueprint) => $blueprint->dropColumn($columns));
        }
    }
};
