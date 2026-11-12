<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_financial_rules', function (Blueprint $t) {
            $t->id();
            $t->decimal('product_percent', 5, 2);
            $t->decimal('shipping_percent', 5, 2);
            $t->unsignedSmallInteger('seller_hold_days');
            $t->timestamp('effective_at')->unique();
            $t->foreignId('created_by_user_id')->constrained('users')->restrictOnDelete();
            $t->timestamp('created_at')->useCurrent();
        });
        Schema::table('order_financials', function (Blueprint $t) {
            $t->foreignId('platform_rule_id')->nullable()->constrained('platform_financial_rules')->restrictOnDelete();
            // Zero preserves the existing Buyer-confirmation requirement.
            $t->unsignedSmallInteger('seller_hold_days')->default(0);
        });
        Schema::create('partner_operating_expenses', function (Blueprint $t) {
            $t->id();
            $t->foreignId('logistics_partner_id')->constrained()->restrictOnDelete();
            $t->string('category', 40);
            $t->bigInteger('amount_minor');
            $t->date('incurred_on');
            $t->string('evidence_reference', 160);
            $t->unique(['logistics_partner_id', 'evidence_reference'], 'partner_expense_reference_unique');
            $t->string('payment_reference', 120)->nullable();
            $t->foreignId('created_by_user_id')->constrained('users')->restrictOnDelete();
            $t->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partner_operating_expenses');
        Schema::table('order_financials', function (Blueprint $t) {
            $t->dropConstrainedForeignId('platform_rule_id');
            $t->dropColumn('seller_hold_days');
        });
        Schema::dropIfExists('platform_financial_rules');
    }
};
