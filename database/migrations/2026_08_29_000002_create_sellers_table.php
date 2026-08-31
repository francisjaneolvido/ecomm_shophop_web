<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sellers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('middle_initial', 2)->nullable();
            $table->enum('sex', ['Male', 'Female']);
            $table->string('contact_no', 11);
            $table->date('birthday');

            $table->string('province_code', 20);
            $table->string('province_name', 150);
            $table->string('municipality_code', 20);
            $table->string('municipality_name', 150);
            $table->string('barangay_code', 20);
            $table->string('barangay_name', 150);
            $table->text('street_address');

            $table->string('business_name', 200);
            $table->string('business_category', 100);
            $table->string('valid_id_path');
            $table->string('business_permit_path');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sellers');
    }
};
