<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('sorting_centers', function (Blueprint $table) {
            $table->timestamp('deactivated_at')->nullable()->after('status');
            $table->timestamp('archived_at')->nullable()->after('deactivated_at');
            $table->index(['status', 'deactivated_at', 'archived_at'], 'centers_archive_due_idx');
        });
        Schema::table('cod_settlements', function (Blueprint $table) {
            // Logistics acknowledges Rider cash first, then records remittance to ShopHop.
            // Admin independently confirms receipt before COD payouts can be prepared.
            $table->string('admin_remittance_reference', 120)->nullable();
            $table->timestamp('admin_remitted_at')->nullable();
            $table->decimal('admin_received_amount', 12, 2)->nullable();
            $table->foreignId('admin_received_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('admin_received_at')->nullable();
            $table->index(['admin_remitted_at', 'admin_received_at'], 'cod_admin_cash_queue_idx');
        });
    }

    public function down(): void
    {
        Schema::table('cod_settlements', function (Blueprint $table) {
            $table->dropIndex('cod_admin_cash_queue_idx');
            $table->dropForeign(['admin_received_by_user_id']);
            $table->dropColumn(['admin_remittance_reference','admin_remitted_at','admin_received_amount','admin_received_by_user_id','admin_received_at']);
        });
        Schema::table('sorting_centers', function (Blueprint $table) {
            $table->dropIndex('centers_archive_due_idx');
            $table->dropColumn(['deactivated_at','archived_at']);
        });
    }
};
