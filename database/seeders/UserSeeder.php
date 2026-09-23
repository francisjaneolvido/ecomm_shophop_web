<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@shophop.com'],
            [
                // Keep the demo administrator aligned with the credentials supplied by the team.
                'name' => 'Admin',
                'password' => Hash::make('Admin1234!'),
                'account_type' => 'admin',
                'status' => 'approved',
                'email_verified_at' => now(),
            ]
        );
    }
}
