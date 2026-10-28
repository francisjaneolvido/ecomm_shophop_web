<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new \RuntimeException('Demo account seeders can run only in local/testing environments.');
        }

        $this->call([
            /*
            |--------------------------------------------------------------------------
            | Existing Users / Admin Accounts
            |--------------------------------------------------------------------------
            |
            | Keep this first if your current UserSeeder creates
            | administrator or default system accounts.
            |
            */

            UserSeeder::class,


            /*
            |--------------------------------------------------------------------------
            | Demo Buyer Account
            |--------------------------------------------------------------------------
            */

            BuyerDemoSeeder::class,


            /*
            |--------------------------------------------------------------------------
            | Demo Seller Account
            |--------------------------------------------------------------------------
            */

            SellerDemoSeeder::class,

            // Additional test Buyer, Seller, Logistics, and Admin profiles.
            TestAccountsSeeder::class,
        ]);
    }
}