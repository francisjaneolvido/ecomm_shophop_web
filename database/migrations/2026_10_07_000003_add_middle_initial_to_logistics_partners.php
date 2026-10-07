<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('logistics_partners') || Schema::hasColumn('logistics_partners', 'rep_middle_initial')) {
            return;
        }

        Schema::table('logistics_partners', function (Blueprint $table) {
            $table->string('rep_middle_initial', 5)->nullable()->after('rep_first_name');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('logistics_partners') || ! Schema::hasColumn('logistics_partners', 'rep_middle_initial')) {
            return;
        }

        Schema::table('logistics_partners', function (Blueprint $table) {
            $table->dropColumn('rep_middle_initial');
        });
    }
};
