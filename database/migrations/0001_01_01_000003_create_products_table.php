<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('products')) {
            return;
        }

        // This must precede the 2025 inventory and child-table migrations on a clean database.
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->string('name');
            $table->string('category');
            $table->decimal('price', 10, 2);
            $table->unsignedTinyInteger('discount')->default(0);
            $table->unsignedInteger('stock')->default(0);
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->enum('status', ['active', 'archived'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        // This can be recorded against an existing production table, so rollback must not destroy it.
    }
};
