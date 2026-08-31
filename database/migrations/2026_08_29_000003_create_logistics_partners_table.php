<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('logistics_partners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            // Step 1: Terms & Agreement
            $table->string('agreement_rep_name', 150);
            $table->date('agreement_date');
            $table->string('agreement_signature_path');

            // Step 2: Company Details
            $table->string('company_name', 200);
            $table->string('business_registration_no', 100);
            $table->enum('line_of_business', ['motorcycle_courier', 'van_truck_freight', 'same_day', 'other']);
            $table->string('rep_last_name', 100)->nullable();
            $table->string('rep_first_name', 100)->nullable();
            $table->string('rep_valid_id_path');
            $table->string('rep_id_number', 100);
            $table->enum('rep_sex', ['male', 'female']);
            $table->date('rep_birthday');
            $table->string('contact_no', 20);
            $table->string('region', 150);
            $table->string('province', 150);
            $table->string('municipality', 150);
            $table->string('barangay', 150);
            $table->string('street_no', 150);
            $table->string('unit_no', 150);

            // Step 6: Coverage & Documents
            $table->string('business_permit_path');
            $table->string('accreditation_docs_path')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('logistics_partners');
    }
};
