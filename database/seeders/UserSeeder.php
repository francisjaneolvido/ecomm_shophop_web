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
                'name' => 'Admin',
                'password' => Hash::make('Admin123!'),
                'account_type' => 'admin',
                'status' => 'approved',
                'email_verified_at' => now(),
            ]
        );
    }
}