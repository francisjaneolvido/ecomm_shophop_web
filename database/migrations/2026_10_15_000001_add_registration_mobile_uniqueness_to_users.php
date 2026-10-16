<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Legacy contact numbers are still checked against every existing role profile.
        // This UNIQUE field makes races between all newly registered web accounts safe.
        Schema::table('users', function (Blueprint $table) {
            $table->string('registration_contact_no', 11)->nullable()->unique();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['registration_contact_no']);
            $table->dropColumn('registration_contact_no');
        });
    }
};
