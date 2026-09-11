<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Sino at kailan na-review (admin's user_id, since admins.user_id -> users.id)
            $table->foreignId('reviewed_by')->nullable()->after('status')
                ->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');

            // Admin notes (approve o reject) at reason kapag reject
            $table->text('notes')->nullable()->after('reviewed_at');
            $table->text('rejection_reason')->nullable()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('reviewed_by');
            $table->dropColumn(['reviewed_at', 'notes', 'rejection_reason']);
        });
    }
};