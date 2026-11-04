<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('logistics_partners', function (Blueprint $table) {
            $table->string('logo_path', 255)->nullable();
            $table->string('public_description', 280)->nullable();
            $table->boolean('accepting_riders')->default(true);
        });
    }

    public function down(): void
    {
        Schema::table('logistics_partners', function (Blueprint $table) {
            $table->dropColumn(['logo_path', 'public_description', 'accepting_riders']);
        });
    }
};
