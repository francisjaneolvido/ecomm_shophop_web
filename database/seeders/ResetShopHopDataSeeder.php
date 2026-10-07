<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class ResetShopHopDataSeeder extends Seeder
{
    /**
     * Remove all application data while keeping the database schema and
     * Laravel migration history intact, then create one ShopHop super admin.
     *
     * Intended for local/development databases only.
     */
    public function run(): void
    {
        if (app()->environment('production')) {
            throw new RuntimeException(
                'ResetShopHopDataSeeder is disabled in production.'
            );
        }

        if (DB::getDriverName() !== 'mysql') {
            throw new RuntimeException(
                'ResetShopHopDataSeeder currently supports MySQL only.'
            );
        }

        $database = DB::getDatabaseName();

        $tables = collect(DB::select(
            'SELECT TABLE_NAME AS table_name
             FROM information_schema.TABLES
             WHERE TABLE_SCHEMA = ?
               AND TABLE_TYPE = ?',
            [$database, 'BASE TABLE']
        ))
            ->pluck('table_name')
            ->filter(fn ($table) => $table !== 'migrations')
            ->values();

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        try {
            foreach ($tables as $table) {
                DB::statement('TRUNCATE TABLE `'.str_replace('`', '``', $table).'`');
            }
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }

        $adminUser = User::query()->create([
            'email' => 'admin@shophop.com',
            'password' => Hash::make('Admin2025!'),
            'account_type' => 'admin',
            'status' => 'approved',
            'email_verified_at' => now(),
        ]);

        if (Schema::hasTable('admins')) {
            Admin::query()->create([
                'user_id' => $adminUser->id,
                'first_name' => 'ShopHop',
                'last_name' => 'Admin',
                'role' => 'super_admin',
                'last_active_at' => null,
            ]);
        }

        $this->command?->info('ShopHop data reset complete.');
        $this->command?->info('All application tables were cleared except migrations.');
        $this->command?->info('Super Admin created: admin@shophop.com');
    }
}
