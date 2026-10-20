<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sorting_centers', function (Blueprint $table) {
            if (! Schema::hasColumn('sorting_centers', 'type')) {
                // province_hub | station_hub
                $table->string('type', 20)->default('station_hub')->after('id');
            }

            if (! Schema::hasColumn('sorting_centers', 'parent_id')) {
                // null kung province hub; id ng province hub kung station hub
                $table->unsignedBigInteger('parent_id')->nullable()->after('type');
            }

            if (! Schema::hasColumn('sorting_centers', 'province')) {
                $table->string('province')->nullable();
            }
        });

        Schema::table('sorting_centers', function (Blueprint $table) {
            $table->index(['type', 'parent_id'], 'sorting_centers_type_parent_idx');
        });
    }

    public function down(): void
    {
        Schema::table('sorting_centers', function (Blueprint $table) {
            $table->dropIndex('sorting_centers_type_parent_idx');
        });

        Schema::table('sorting_centers', function (Blueprint $table) {
            foreach (['type', 'parent_id', 'province'] as $column) {
                if (Schema::hasColumn('sorting_centers', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};