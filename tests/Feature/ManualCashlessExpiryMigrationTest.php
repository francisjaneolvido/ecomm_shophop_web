<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ManualCashlessExpiryMigrationTest extends TestCase
{
    public function test_existing_unresolved_payments_get_a_full_day_from_rollout(): void
    {
        // Run the real upgrade against pre-feature rows in a separate in-memory database.
        $originalConnection = DB::getDefaultConnection();
        config()->set('database.connections.expiry_migration', [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
            'foreign_key_constraints' => true,
        ]);
        DB::setDefaultConnection('expiry_migration');
        $this->travelTo(\Carbon\Carbon::parse('2026-09-30 12:00:00'));

        try {
            Schema::create('users', fn (Blueprint $table) => $table->id());
            Schema::create('manual_cashless_payments', function (Blueprint $table) {
                $table->id();
                $table->string('status');
                $table->timestamp('created_at');
                $table->timestamp('reviewed_at')->nullable();
            });
            foreach (['awaiting_proof', 'rejected', 'pending_review', 'verified', 'cancelled', 'expired'] as $status) {
                // Historical placement and review dates must not shorten the rollout grace window.
                DB::table('manual_cashless_payments')->insert([
                    'status' => $status,
                    'created_at' => now()->subDays(10),
                    'reviewed_at' => $status === 'rejected' ? now()->subDays(5) : null,
                ]);
            }

            (require database_path('migrations/2026_09_29_000001_add_manual_cashless_closure_fields.php'))->up();
            $deadlines = DB::table('manual_cashless_payments')->pluck('expires_at', 'status');
            $rolloutDeadline = now()->addDay()->format('Y-m-d H:i:s');
            $this->assertSame($rolloutDeadline, $deadlines['awaiting_proof']);
            $this->assertSame($rolloutDeadline, $deadlines['rejected']);
            foreach (['pending_review', 'verified', 'cancelled', 'expired'] as $status) {
                $this->assertNull($deadlines[$status], $status);
            }
        } finally {
            // Return the application to its original test connection after the isolated upgrade.
            DB::setDefaultConnection($originalConnection);
            DB::purge('expiry_migration');
            $this->travelBack();
        }
    }
}
