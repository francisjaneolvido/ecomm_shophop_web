<?php

namespace Database\Seeders;

use App\Models\Admin;
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
     * Creates 1 test buyer, 1 test seller, 1 test logistics partner,
     * and 1 test admin for manual/QA testing purposes.
     * Safe to run multiple times.
     */
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new \RuntimeException('Demo account seeders can run only in local/testing environments.');
        }

        // ----- Test Buyer -----
        $buyerUser = User::firstOrCreate(
            ['email' => 'test.buyer@example.com'],
            [
                'password' => 'password123', // auto-hashed ng 'hashed' cast sa User model
                'account_type' => 'buyer',
                'status' => 'approved',
                'email_verified_at' => now(),
            ]
        );

        if ($buyerUser->account_type !== 'buyer') {
            throw new \RuntimeException('Cannot seed buyer account: email belongs to another role.');
        }

        Buyer::firstOrCreate(
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
        $sellerUser = User::firstOrCreate(
            ['email' => 'test.seller@example.com'],
            [
                'password' => 'password123',
                'account_type' => 'seller',
                'status' => 'approved',
                'email_verified_at' => now(),
            ]
        );

        if ($sellerUser->account_type !== 'seller') {
            throw new \RuntimeException('Cannot seed seller account: email belongs to another role.');
        }

        Seller::firstOrCreate(
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
        $logisticsUser = User::firstOrCreate(
            ['email' => 'test.logistics@example.com'],
            [
                'password' => 'password123',
                'account_type' => 'logistics',
                'status' => 'approved',
                'email_verified_at' => now(),
            ]
        );

        if ($logisticsUser->account_type !== 'logistics') {
            throw new \RuntimeException('Cannot seed logistics account: email belongs to another role.');
        }

        LogisticsPartner::firstOrCreate(
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

        // ----- Test Admin -----
        // Admin needs a matching admins profile for account management.
        $adminUser = User::firstOrCreate(
            ['email' => 'test.admin@example.com'],
            [
                'password' => 'password123',
                'account_type' => 'admin',
                'status' => 'approved',
                'email_verified_at' => now(),
            ]
        );

        if ($adminUser->account_type !== 'admin') {
            throw new \RuntimeException('Cannot seed Admin: test.admin@example.com belongs to another role.');
        }
        Admin::firstOrCreate(
            ['user_id' => $adminUser->id],
            ['first_name' => 'Test', 'last_name' => 'Admin', 'role' => 'super_admin']
        );

        $this->command?->info('Test accounts available (passwords apply to NEW accounts only):');
        $this->command?->info('Buyer:     test.buyer@example.com / password123');
        $this->command?->info('Seller:    test.seller@example.com / password123');
        $this->command?->info('Logistics: test.logistics@example.com / password123');
        $this->command?->info('Admin:     test.admin@example.com / password123');
    }
}