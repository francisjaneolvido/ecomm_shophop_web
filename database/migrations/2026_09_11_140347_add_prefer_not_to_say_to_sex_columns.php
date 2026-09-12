<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
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