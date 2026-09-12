<?php

namespace Database\Seeders;

use App\Models\Seller;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SellerDemoSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            $user = User::updateOrCreate(
                [
                    'email' => 'seller@shophop.test',
                ],
                [
                    'password' => Hash::make('Seller1234'),
                    'email_verified_at' => now(),
                    'account_type' => 'seller',
                    'status' => 'approved',
                ]
            );

            Seller::updateOrCreate(
                [
                    'user_id' => $user->id,
                ],
                [
                    'first_name' => 'Demo',
                    'last_name' => 'Seller',
                    'middle_initial' => null,

                    'sex' => 'Male',

                    'contact_no' => '09987654321',
                    'birthday' => '1998-08-20',

                    'province_code' => '0434',
                    'province_name' => 'Laguna',

                    'municipality_code' => '043405',
                    'municipality_name' => 'Calamba City',

                    'barangay_code' => '043405001',
                    'barangay_name' => 'Barangay 1',

                    'street_address' => '456 Seller Avenue',

                    'business_name' => 'ShopHop Demo Store',

                    'business_category' => 'Electronics and Gadgets',

                    'valid_id_path' => 'seeders/demo-seller-valid-id.jpg',

                    'business_permit_path' =>
                        'seeders/demo-seller-business-permit.jpg',
                ]
            );
        });
    }
}