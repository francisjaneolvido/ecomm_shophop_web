<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

/**
 * LOCAL DEV ONLY: clear rows, never drop tables or Laravel migration history.
 * After clearing the data, restore only the known test login accounts.
 * Run manually: php artisan db:seed --class=ResetShopHopDataSeeder
 */
class ResetShopHopDataSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new RuntimeException('Data reset is allowed only with APP_ENV=local or testing.');
        }

        if (DB::getDriverName() !== 'mysql') {
            throw new RuntimeException('This reset targets MySQL only. The database was not changed.');
        }

        // Prevent accidental resets of externally hosted databases while in local mode.
        $host = (string) DB::connection()->getConfig('host');
        if (! in_array(strtolower($host), ['127.0.0.1', 'localhost', '::1'], true)) {
            throw new RuntimeException('Only a local MySQL database can be reset by this seeder.');
        }

        if (! Schema::hasTable('migrations') || ! Schema::hasTable('users')) {
            throw new RuntimeException('Required tables are missing. Run php artisan migrate first.');
        }

        $database = (string) DB::getDatabaseName();
        if ($database === '' || in_array(strtolower($database), ['mysql', 'information_schema', 'performance_schema', 'sys'], true)) {
            throw new RuntimeException('Refusing to reset an invalid/system database.');
        }

        $this->command?->warn("DANGER: All records in MySQL database [{$database}] will be deleted PERMANENTLY.");
        $this->command?->warn('Tables, columns, and the migrations table will remain.');
        $this->command?->warn('Only local demo account profiles will be recreated (no products or orders).');

        // A typed confirmation prevents accidental execution when following seeding instructions.
        $answer = $this->command?->ask('Type RESET to erase the local data (anything else cancels)');
        if ($answer !== 'RESET') {
            $this->command?->info('Cancelled. No data was changed.');
            return;
        }

        $tables = collect(DB::select(
            'SELECT TABLE_NAME AS table_name
             FROM information_schema.TABLES
             WHERE TABLE_SCHEMA = ? AND TABLE_TYPE = ?',
            [$database, 'BASE TABLE']
        ))->pluck('table_name')
          ->filter(fn ($table) => $table !== 'migrations')
          ->values();

        // MySQL TRUNCATE is not transactional: use this ONLY against a backup/dev DB.
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        try {
            foreach ($tables as $table) {
                DB::statement('TRUNCATE TABLE `'.str_replace('`', '``', $table).'`');
            }
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }

        // Preserve backwards compatibility with the original standalone reset admin.
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

        // The project's DatabaseSeeder already holds the standard buyer, seller,
        // logistics, and admin test accounts. Restore these AFTER the cleanup.
        $this->call(DatabaseSeeder::class);
        $this->call(RepairCheckoutDemoAddressesSeeder::class);

        $this->command?->info('Done: tables preserved, non-migration rows cleared, demo logins seeded.');
        $this->command?->info('Original Admin: admin@shophop.com / Admin2025!');
        $this->command?->warn('No products, orders, payouts, branches, or rider network have been recreated.');
    }
}
