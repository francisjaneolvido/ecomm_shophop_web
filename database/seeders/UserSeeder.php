<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

/** Creates a local-only demo Admin; never replaces an existing password. */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new RuntimeException('Demo account seeders can run only in local/testing environments.');
        }

        DB::transaction(function (): void {
            $email = 'admin@shophop.test';
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'password' => Hash::make('Admin1234'),
                    'account_type' => 'admin',
                    'status' => 'approved',
                    'email_verified_at' => now(),
                ]
            );

            if ($user->account_type !== 'admin') {
                throw new RuntimeException("Cannot seed Admin: {$email} is already used by another account type.");
            }

            Admin::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'first_name' => 'Demo',
                    'last_name' => 'Administrator',
                    'role' => 'super_admin',
                ]
            );
        });

        $this->command?->info('Admin: admin@shophop.test (password Admin1234 for NEW account only).');
    }
}
