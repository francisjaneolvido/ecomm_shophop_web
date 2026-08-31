<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('account_type', ['buyer', 'seller', 'logistics', 'admin'])
                ->default('buyer')
                ->after('email');

            $table->enum('status', ['pending', 'approved', 'rejected', 'suspended'])
                ->default('pending')
                ->after('account_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['account_type', 'status']);
        });
    }
};
