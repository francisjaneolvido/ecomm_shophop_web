<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Link ng branch login account sa sorting center.
        if (! Schema::hasColumn('sorting_centers', 'user_id')) {
            Schema::table('sorting_centers', function (Blueprint $table) {
                $table->foreignId('user_id')
                    ->nullable()
                    ->after('logistics_partner_id')
                    ->constrained('users')
                    ->nullOnDelete();
            });
        }

        // 2. Idagdag ang 'branch' sa account_type enum (MySQL / MariaDB lang).
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement(
                "ALTER TABLE `users` MODIFY `account_type` ENUM('buyer','seller','logistics','admin','branch') NOT NULL"
            );
        }
    }

    public function down(): void
    {
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            // Alisin muna ang mga branch user para hindi mag-fail ang pag-revert ng enum.
            DB::table('users')->where('account_type', 'branch')->delete();

            DB::statement(
                "ALTER TABLE `users` MODIFY `account_type` ENUM('buyer','seller','logistics','admin') NOT NULL"
            );
        }

        if (Schema::hasColumn('sorting_centers', 'user_id')) {
            Schema::table('sorting_centers', function (Blueprint $table) {
                $table->dropConstrainedForeignId('user_id');
            });
        }
    }
};