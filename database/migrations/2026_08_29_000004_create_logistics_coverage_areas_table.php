<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('logistics_coverage_areas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('logistics_partner_id')
                ->constrained('logistics_partners')
                ->cascadeOnDelete();

            $table->string('area_name', 150); // province or region name
            $table->enum('area_type', ['province', 'region'])->default('province');
            $table->text('cities')->nullable(); // 'ALL' or '|'-separated city names

            $table->timestamp('created_at')->nullable()->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('logistics_coverage_areas');
    }
};
