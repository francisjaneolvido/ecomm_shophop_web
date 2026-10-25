<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Isang batch/manifest ng parcels na ipinapadala ng branch pataas sa province hub.
        Schema::create('parcel_manifests', function (Blueprint $table) {
            $table->id();
            $table->string('code', 40)->unique();
            $table->foreignId('logistics_partner_id')->constrained('logistics_partners')->cascadeOnDelete();
            $table->foreignId('from_sorting_center_id')->constrained('sorting_centers')->restrictOnDelete();
            $table->foreignId('to_sorting_center_id')->constrained('sorting_centers')->restrictOnDelete();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('transit_rider_id')->nullable()->constrained('riders')->nullOnDelete();
            $table->string('status', 20)->default('prepared'); // prepared | dispatched | cancelled (received ay kinukuwenta)
            $table->unsignedInteger('parcel_count')->default(0);
            $table->string('note', 255)->nullable();
            $table->timestamp('prepared_at')->nullable();
            $table->timestamp('dispatched_at')->nullable();
            $table->timestamps();

            $table->index(['from_sorting_center_id', 'status'], 'parcel_manifests_from_status_idx');
        });

        Schema::create('parcel_manifest_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manifest_id')->constrained('parcel_manifests')->cascadeOnDelete();
            $table->foreignId('delivery_id')->constrained('deliveries')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['manifest_id', 'delivery_id']);
        });

        // Para malaman kung anong manifest ang pinagmulan ng bawat transfer.
        Schema::table('parcel_transfers', function (Blueprint $table) {
            $table->foreignId('manifest_id')->nullable()->after('delivery_id')
                ->constrained('parcel_manifests')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('parcel_transfers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('manifest_id');
        });
        Schema::dropIfExists('parcel_manifest_items');
        Schema::dropIfExists('parcel_manifests');
    }
};