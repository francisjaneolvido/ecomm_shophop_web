<?php

namespace Database\Seeders;

use App\Models\Buyer;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class BuyerDemoSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            $user = User::updateOrCreate(
                [
                    'email' => 'buyer@shophop.test',
                ],
                [
                    'password' => Hash::make('Buyer1234'),
                    'email_verified_at' => now(),
                    'account_type' => 'buyer',
                    'status' => 'approved',
                ]
            );

            Buyer::updateOrCreate(
                [
                    'user_id' => $user->id,
                ],
                [
                    'first_name' => 'Demo',
                    'last_name' => 'Buyer',
                    'middle_initial' => null,
                    'sex' => 'Female',

                    'contact_no' => '09123456789',
                    'birthday' => '2002-05-15',

                    'province_code' => '0434',
                    'province_name' => 'Laguna',

                    'municipality_code' => '043405',
                    'municipality_name' => 'Calamba City',

                    'barangay_code' => '043405001',
                    'barangay_name' => 'Barangay 1',

                    'street_address' => '123 Demo Street',

                    /*
                    |--------------------------------------------------------------------------
                    | Demo document
                    |--------------------------------------------------------------------------
                    | String path lang muna para ma-satisfy ang profile record.
                    | Maaari natin palitan later ng real demo document.
                    */

                    'valid_id_path' => 'seeders/demo-buyer-valid-id.jpg',
                ]
            );
        });
    }
}