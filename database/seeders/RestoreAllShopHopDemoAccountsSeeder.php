<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Throwable;

/**
 * LOCAL ONLY, NON-DESTRUCTIVE: Restore all known ShopHop demo logins,
 * including Laguna and nationwide branch/hub/rider networks.
 *
 * Usage: php artisan db:seed --class=RestoreAllShopHopDemoAccountsSeeder
 *
 * Does not truncate/drop tables, delete rows, or overwrite existing passwords.
 * The logistics seeders use the public PSGC API; failures are reported per step.
 */
class RestoreAllShopHopDemoAccountsSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new RuntimeException('Demo account restoration is allowed only in local/testing.');
        }

        $host = strtolower((string) DB::connection()->getConfig('host'));
        if (! in_array($host, ['127.0.0.1', 'localhost', '::1'], true)) {
            throw new RuntimeException('Refusing to seed a non-local database host. No changes were made.');
        }

        if (! Schema::hasTable('users') || ! Schema::hasTable('admins')) {
            throw new RuntimeException('Required users/admins tables are missing. Run migrations first.');
        }

        $this->command?->info('RESTORE ONLY: existing rows will not be cleared.');
        $this->command?->warn('Existing account passwords are not changed. Listed demo passwords only work on newly created demo accounts.');

        // Legacy super-admin was created directly by ResetShopHopDataSeeder.
        // Restore it separately so it is also available without running RESET again.
        DB::transaction(function (): void {
            $email = 'admin@shophop.com';
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'password' => Hash::make('Admin2025!'),
                    'account_type' => 'admin',
                    'status' => 'approved',
                    'email_verified_at' => now(),
                ]
            );
            if ($user->account_type !== 'admin') {
                throw new RuntimeException("Cannot restore {$email}: email is already assigned to a different role.");
            }
            Admin::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'first_name' => 'ShopHop',
                    'last_name' => 'Admin',
                    'role' => 'super_admin',
                ]
            );
        });

        $failures = [];

        // Default test accounts: admin, buyer, seller, logistics + their role profiles.
        // The network steps below must run in dependency order.
        $steps = [
            'Standard admin/buyer/seller/logistics accounts' => DatabaseSeeder::class,
            'Demo checkout addresses' => RepairCheckoutDemoAddressesSeeder::class,
            'Laguna logistics hubs, branches and riders' => LagunaLogisticsSeeder::class,
            'Nationwide provincial hubs, branches and riders' => NationwideDemoLogisticsSeeder::class,
            'Nationwide city/municipality stations and riders' => NationwideDetailedNetworkSeeder::class,
        ];

        foreach ($steps as $label => $seederClass) {
            $this->command?->newLine();
            $this->command?->info("Restoring: {$label}");
            try {
                $this->call($seederClass);
                $this->command?->info("OK: {$label}");
            } catch (Throwable $e) {
                $failures[$label] = $e->getMessage();
                $this->command?->error("INCOMPLETE: {$label} — {$e->getMessage()}");
                // Continue so independent network/account groups can still be restored.
            }
        }

        $this->command?->newLine();
        $this->command?->info('Test logins for newly restored accounts:');
        $this->command?->line('Admin:     admin@shophop.com / Admin2025!');
        $this->command?->line('Admin:     admin@shophop.test / Admin1234');
        $this->command?->line('Admin:     test.admin@example.com / password123');
        $this->command?->line('Buyer:     buyer@shophop.test / Buyer1234');
        $this->command?->line('Buyer:     test.buyer@example.com / password123');
        $this->command?->line('Seller:    seller@shophop.test / Seller1234');
        $this->command?->line('Seller:    test.seller@example.com / password123');
        $this->command?->line('Logistics: test.logistics@example.com / password123');
        $this->command?->line('Laguna:    laguna.logistics@example.com / Password123');
        $this->command?->line('Laguna:    laguna.provincehub@example.com / Password123');
        $this->command?->line('Nationwide: nationwide.logistics@example.com / Password123');
        $this->command?->line('Branch/Rider: see console output of the individual logistics seeders; defaults use Password123.');

        if ($failures !== []) {
            $this->command?->error('Some seeders were NOT completed. No tables/records were cleared.');
            foreach ($failures as $label => $reason) {
                $this->command?->warn("- {$label}: {$reason}");
            }
            throw new RuntimeException('Demo account restore was partial. See errors above (PSGC API/internet or database constraints may be the cause).');
        }

        $this->command?->info('All 5 restore steps completed. Demo users and associated network accounts restored or already present.');
    }
}
