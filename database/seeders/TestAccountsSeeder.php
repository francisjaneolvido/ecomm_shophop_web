<?php

namespace Database\Seeders;

use App\Models\Buyer;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestAccountsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Creates 1 test buyer account and 1 test seller account
     * for manual/QA testing purposes.
     */
    public function run(): void
    {
        // ----- Test Buyer -----
        $buyerUser = User::create([
            'email' => 'test.buyer@example.com',
            'password' => Hash::make('password123'),
            'account_type' => 'buyer',
            'status' => 'approved',
            'email_verified_at' => now(),
        ]);

        Buyer::create([
            'user_id' => $buyerUser->id,
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
        ]);

        // ----- Test Seller -----
        $sellerUser = User::create([
            'email' => 'test.seller@example.com',
            'password' => Hash::make('password123'),
            'account_type' => 'seller',
            'status' => 'approved',
            'email_verified_at' => now(),
        ]);

        Seller::create([
            'user_id' => $sellerUser->id,
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
        ]);

        $this->command->info('Test buyer and seller accounts created:');
        $this->command->info('Buyer: test.buyer@example.com / password123');
        $this->command->info('Seller: test.seller@example.com / password123');
    }
}