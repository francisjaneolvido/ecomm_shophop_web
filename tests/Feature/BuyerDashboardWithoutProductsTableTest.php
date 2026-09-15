<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class BuyerDashboardWithoutProductsTableTest extends TestCase
{
    public function test_authenticated_buyer_dashboard_renders_without_products_table(): void
    {
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('account_type');
            $table->string('status');
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamps();
        });
        Schema::create('buyers', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->timestamps();
        });

        $buyer = new User();
        $buyer->forceFill([
            'name' => 'Buyer',
            'email' => 'buyer@example.test',
            'password' => 'not-used-by-this-test',
            'account_type' => 'buyer',
            'status' => 'approved',
            'email_verified_at' => now(),
        ])->save();

        $this->actingAs($buyer)
            ->get(route('buyer.dashboard'))
            ->assertOk();
    }
}
