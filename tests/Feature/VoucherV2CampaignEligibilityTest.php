<?php
namespace Tests\Feature;

use App\Models\Finance\PlatformVoucher;
use App\Models\Seller\Manage_inventory\Voucher;
use App\Services\PlatformVoucherPricing;
use App\Services\SellerVoucherPricing;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

/** Isolated checkout voucher tests. NEVER point this at a non-disposable database. */
class VoucherV2CampaignEligibilityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (DB::getDefaultConnection() !== 'sqlite'
            || DB::connection()->getDatabaseName() !== ':memory:') {
            $this->markTestSkipped('Voucher V2 integration tests require isolated SQLite :memory: (never real business data).');
        }
        Schema::dropAllTables();
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('buyer_id');
            $table->uuid('checkout_group_id')->nullable();
            $table->unsignedBigInteger('voucher_id')->nullable();
            $table->unsignedBigInteger('platform_voucher_id')->nullable();
            $table->unsignedBigInteger('shipping_voucher_id')->nullable();
        });
        Schema::create('platform_vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('kind');
            $table->string('type');
            $table->decimal('value', 12, 2);
            $table->decimal('min_spend', 12, 2)->default(0);
            $table->decimal('max_discount', 12, 2)->nullable();
            $table->decimal('budget_amount', 14, 2)->nullable();
            $table->decimal('spent_amount', 14, 2)->default(0);
            $table->unsignedInteger('per_buyer_limit')->nullable();
            $table->unsignedInteger('usage_limit')->nullable();
            $table->unsignedInteger('used_count')->default(0);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('seller_id');
            $table->string('code')->unique();
            $table->string('name');
            $table->string('status')->default('active');
            $table->string('kind')->default('merchandise');
            $table->string('type');
            $table->decimal('value', 12, 2);
            $table->decimal('min_order_amount', 12, 2)->default(0);
            $table->decimal('max_discount', 12, 2)->nullable();
            $table->unsignedInteger('per_buyer_limit')->nullable();
            $table->unsignedInteger('usage_limit')->nullable();
            $table->unsignedInteger('used_count')->default(0);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();
        });
    }

    public function test_shop_hop_discount_applies_after_seller_savings_with_capped_multi_seller_allocation(): void
    {
        PlatformVoucher::create(['code' => 'SHOPHOP20', 'name' => 'Campaign', 'kind' => 'merchandise',
            'type' => 'percent', 'value' => 20, 'max_discount' => 100, 'min_spend' => 500,
            'per_buyer_limit' => 1, 'budget_amount' => 500]);
        $result = app(PlatformVoucherPricing::class)->price([
            11 => ['merchandise' => 900, 'shipping' => 80],
            22 => ['merchandise' => 450, 'shipping' => 70],
        ], 'SHOPHOP20', '', false, 35);
        $this->assertEqualsWithDelta(100, $result['merchandise_discount'], 0.001);
        $this->assertEqualsWithDelta(100, array_sum(array_column($result['allocations'], 'platform_discount')), .001);
        $this->assertSame(0.0, (float) $result['shipping_discount']);
    }

    public function test_campaign_budget_rejects_another_discount_before_checkout_is_created(): void
    {
        PlatformVoucher::create(['code' => 'LAST10', 'name' => 'Budget', 'kind' => 'merchandise',
            'type' => 'fixed', 'value' => 10, 'budget_amount' => 100, 'spent_amount' => 95]);
        $this->expectException(ValidationException::class);
        app(PlatformVoucherPricing::class)->price([11 => ['merchandise' => 100, 'shipping' => 0]], 'LAST10', '', false, 35);
    }

    public function test_platform_per_buyer_limit_is_once_per_checkout_group(): void
    {
        $v = PlatformVoucher::create(['code' => 'WELCOME30', 'name' => 'Welcome',
            'kind' => 'merchandise', 'type' => 'fixed', 'value' => 30, 'per_buyer_limit' => 1]);
        DB::table('orders')->insert([
            ['buyer_id' => 35, 'checkout_group_id' => '01999c1b-c882-711a-b68e-123456789abc', 'platform_voucher_id' => $v->id],
            ['buyer_id' => 35, 'checkout_group_id' => '01999c1b-c882-711a-b68e-123456789abc', 'platform_voucher_id' => $v->id],
        ]);
        $this->expectException(ValidationException::class);
        app(PlatformVoucherPricing::class)->price([11 => ['merchandise' => 500, 'shipping' => 30]], 'WELCOME30', '', false, 35);
    }

    public function test_seller_voucher_may_not_cross_shop_boundaries(): void
    {
        Voucher::create(['seller_id' => 56, 'name' => 'Seller 56', 'code' => 'SHOP56',
            'type' => 'fixed', 'value' => 50, 'kind' => 'merchandise', 'status' => 'active']);
        $this->expectException(ValidationException::class);
        app(SellerVoucherPricing::class)->find('SHOP56', 67, 35);
    }
}
