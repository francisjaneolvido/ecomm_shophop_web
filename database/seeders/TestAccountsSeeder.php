<?php

namespace Database\Seeders;

use App\Models\Buyer;
use App\Models\LogisticsPartner;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Database\Seeder;

class TestAccountsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Creates 1 test buyer, 1 test seller, and 1 test logistics partner
     * for manual/QA testing purposes. Safe to run multiple times.
     */
    public function run(): void
    {
        // ----- Test Buyer -----
        $buyerUser = User::updateOrCreate(
            ['email' => 'test.buyer@example.com'],
            [
                'password' => 'password123', // auto-hashed ng 'hashed' cast sa User model
                'account_type' => 'buyer',
                'status' => 'approved',
                'email_verified_at' => now(),
            ]
        );

        Buyer::updateOrCreate(
            ['user_id' => $buyerUser->id],
            [
                'first_name' => 'Test',
                'last_name' => 'Buyer',
                'middle_initial' => null,
                'sex' => 'Male',
                'contact_no' => '09171234567',
                'birthday' => '2000-01-01',

                'province_code' => '0000000000',
                'province_name' => 'Test Province',
                'municipality_code' => '0000000000',
                'municipality_name' => 'Test Municipality',
                'barangay_code' => '0000000000',
                'barangay_name' => 'Test Barangay',
                'street_address' => '123 Test Street',

                'valid_id_path' => 'test/valid_id_placeholder.jpg',
            ]
        );

        // ----- Test Seller -----
        $sellerUser = User::updateOrCreate(
            ['email' => 'test.seller@example.com'],
            [
                'password' => 'password123',
                'account_type' => 'seller',
                'status' => 'approved',
                'email_verified_at' => now(),
            ]
        );

        Seller::updateOrCreate(
            ['user_id' => $sellerUser->id],
            [
                'first_name' => 'Test',
                'last_name' => 'Seller',
                'middle_initial' => null,
                'sex' => 'Female',
                'contact_no' => '09179876543',
                'birthday' => '1995-05-15',

                'province_code' => '0000000000',
                'province_name' => 'Test Province',
                'municipality_code' => '0000000000',
                'municipality_name' => 'Test Municipality',
                'barangay_code' => '0000000000',
                'barangay_name' => 'Test Barangay',
                'street_address' => '456 Test Avenue',

                'business_name' => 'Test Shop',
                'business_category' => 'General Merchandise',
                'valid_id_path' => 'test/valid_id_placeholder.jpg',
                'business_permit_path' => 'test/business_permit_placeholder.jpg',
            ]
        );

        // ----- Test Logistics Partner -----
        $logisticsUser = User::updateOrCreate(
            ['email' => 'test.logistics@example.com'],
            [
                'password' => 'password123',
                'account_type' => 'logistics',
                'status' => 'approved',
                'email_verified_at' => now(),
            ]
        );

        LogisticsPartner::updateOrCreate(
            ['user_id' => $logisticsUser->id],
            [
                // Step 1: Terms & Agreement
                'agreement_rep_name' => 'Test Logistics',
                'agreement_date' => now()->toDateString(),
                'agreement_signature_path' => 'test/signature_placeholder.png',

                // Step 2: Company Details
                'company_name' => 'Test Logistics Co.',
                'business_registration_no' => 'TEST-0000000001',
                'line_of_business' => 'motorcycle_courier',

                // Representative
                'rep_last_name' => 'Logistics',
                'rep_first_name' => 'Test',
                'rep_valid_id_path' => 'test/valid_id_placeholder.jpg',
                'rep_id_number' => 'ID-0000000001',
                'rep_sex' => 'male',
                'rep_birthday' => '1990-01-01',
                'contact_no' => '09175551234',

                // Address (plain text)
                'region' => 'Test Region',
                'province' => 'Test Province',
                'municipality' => 'Test Municipality',
                'barangay' => 'Test Barangay',
                'street_no' => '789 Test Road',
                'unit_no' => 'Unit 1',

                // Documents
                'business_permit_path' => 'test/business_permit_placeholder.jpg',
                'accreditation_docs_path' => null,
            ]
        );

        $this->command->info('Test accounts ready:');
        $this->command->info('Buyer:     test.buyer@example.com / password123');
        $this->command->info('Seller:    test.seller@example.com / password123');
        $this->command->info('Logistics: test.logistics@example.com / password123');
    }
}