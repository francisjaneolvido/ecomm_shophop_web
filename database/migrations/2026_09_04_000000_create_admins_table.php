<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Dagdag na table para sa admin/staff profile details.
     * Ginagamit pa rin natin yung existing `users` table para sa
     * login (email, password, account_type='admin', status).
     * Ang `status` column ng users ang gagamitin natin para sa
     * Active/Disabled:
     *   - 'approved'  => Active
     *   - 'suspended' => Disabled
     */
    public function up(): void
    {
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');

            $table->string('first_name', 100);
            $table->string('last_name', 100);

            $table->enum('role', ['super_admin', 'compliance_officer', 'support_staff'])
                ->default('support_staff');

            // Pwedeng i-update tuwing may request/login si admin
            // (hal. sa middleware o sa login controller).
            $table->timestamp('last_active_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admins');
    }
};