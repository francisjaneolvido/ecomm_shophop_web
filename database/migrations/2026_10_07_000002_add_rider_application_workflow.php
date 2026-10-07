<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('riders', function (Blueprint $table) {
            $table->string('first_name', 100)->nullable()->after('name');
            $table->string('last_name', 100)->nullable()->after('first_name');
            $table->string('middle_initial', 2)->nullable()->after('last_name');
            $table->string('sex', 30)->nullable()->after('middle_initial');
            $table->string('contact_no', 20)->nullable()->after('sex');
            $table->date('birthday')->nullable()->after('contact_no');
            $table->string('province_code', 20)->nullable()->after('birthday');
            $table->string('province_name', 150)->nullable()->after('province_code');
            $table->string('municipality_code', 20)->nullable()->after('province_name');
            $table->string('municipality_name', 150)->nullable()->after('municipality_code');
            $table->string('barangay_code', 20)->nullable()->after('municipality_name');
            $table->string('barangay_name', 150)->nullable()->after('barangay_code');
            $table->text('street_address')->nullable()->after('barangay_name');
            $table->string('plate_number', 30)->nullable()->after('vehicle_type');
            $table->string('or_cr_path')->nullable()->after('plate_number');
            $table->string('id_or_license_path')->nullable()->after('or_cr_path');
            $table->timestamp('email_verified_at')->nullable()->after('email');
            $table->text('rejection_reason')->nullable()->after('availability_status');
            $table->timestamp('applied_at')->nullable()->after('rejection_reason');
            $table->timestamp('approved_at')->nullable()->after('applied_at');
            $table->timestamp('rejected_at')->nullable()->after('approved_at');
            $table->timestamp('terms_accepted_at')->nullable()->after('rejected_at');
            $table->index(['logistics_partner_id', 'status', 'email_verified_at'], 'riders_partner_review_idx');
        });

        Schema::create('rider_email_verification_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rider_id')->unique()->constrained('riders')->cascadeOnDelete();
            $table->string('code_hash');
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamp('expires_at');
            $table->timestamp('last_sent_at')->nullable();
            $table->timestamps();
        });

        // Riders provisioned by Logistics before self-registration already passed an operator-controlled trust step.
        DB::table('riders')
            ->whereNotNull('email')
            ->whereNotNull('password')
            ->whereNull('email_verified_at')
            ->update(['email_verified_at' => now()]);
    }

    public function down(): void
    {
        Schema::dropIfExists('rider_email_verification_codes');

        Schema::table('riders', function (Blueprint $table) {
            $table->dropIndex('riders_partner_review_idx');
            $table->dropColumn([
                'first_name',
                'last_name',
                'middle_initial',
                'sex',
                'contact_no',
                'birthday',
                'province_code',
                'province_name',
                'municipality_code',
                'municipality_name',
                'barangay_code',
                'barangay_name',
                'street_address',
                'plate_number',
                'or_cr_path',
                'id_or_license_path',
                'email_verified_at',
                'rejection_reason',
                'applied_at',
                'approved_at',
                'rejected_at',
                'terms_accepted_at',
            ]);
        });
    }
};
