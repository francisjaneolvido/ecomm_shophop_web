<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite has no MySQL-style MODIFY ENUM support, and it does not enforce this enum type.
        if (DB::connection()->getDriverName() === 'sqlite') {
            return;
        }

        DB::statement("
            ALTER TABLE buyers
            MODIFY COLUMN sex
            ENUM('Male', 'Female', 'Prefer not to say')
            NOT NULL
        ");

        DB::statement("
            ALTER TABLE sellers
            MODIFY COLUMN sex
            ENUM('Male', 'Female', 'Prefer not to say')
            NOT NULL
        ");
    }

    public function down(): void
    {
        // Keep rollback equally portable for the local SQLite demo database.
        if (DB::connection()->getDriverName() === 'sqlite') {
            return;
        }

        DB::statement("
            ALTER TABLE buyers
            MODIFY COLUMN sex
            ENUM('Male', 'Female')
            NOT NULL
        ");

        DB::statement("
            ALTER TABLE sellers
            MODIFY COLUMN sex
            ENUM('Male', 'Female')
            NOT NULL
        ");
    }
};
