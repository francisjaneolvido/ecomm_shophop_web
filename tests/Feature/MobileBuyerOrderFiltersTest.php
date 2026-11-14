<?php

namespace Tests\Feature;

use App\Http\Controllers\Api\MobileBuyerOrderController;
use App\Models\Buyer;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MobileBuyerOrderFiltersTest extends TestCase
{
    public function test_filters_and_counts_are_buyer_scoped_across_paginated_orders(): void
    {
        $this->assertSame('sqlite', DB::getDefaultConnection());
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        Schema::create('orders', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('buyer_id');
            $t->unsignedBigInteger('seller_id')->nullable();
            $t->string('status');
            $t->string('payment_method')->default('cod');
            $t->timestamps();
        });
        foreach (['deliveries', 'order_items', 'return_cases'] as $table) {
            Schema::create($table, function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('order_id');
            });
        }
        $user = new User;
        $user->forceFill(['account_type' => 'buyer', 'status' => 'approved', 'email_verified_at' => now()]);
        $buyer = new Buyer;
        $buyer->setRawAttributes(['id' => 1], true);
        $user->setRelation('buyer', $buyer);
        $statuses = array_merge(array_fill(0, 22, 'completed'), ['to-ship', 'CONFIRMED', 'PREPARING', 'READY_FOR_PICKUP', 'to-receive', 'cancelled']);
        foreach ($statuses as $status) {
            DB::table('orders')->insert(['buyer_id' => 1, 'status' => $status, 'created_at' => '2026-11-13 00:00:00']);
        }
        DB::table('orders')->insert(['buyer_id' => 2, 'status' => 'completed']);
        $read = function (array $query = []) use ($user) {
            $request = Request::create('/api/mobile/buyer/orders', 'GET', $query);
            $request->setUserResolver(fn () => $user);
            return (new MobileBuyerOrderController)->index($request)->getData(true)['data'];
        };
        $all = $read();
        $this->assertCount(20, $all['orders']);
        $this->assertTrue($all['has_more']);
        $this->assertSame(['all' => 28, 'to-ship' => 4, 'to-receive' => 1, 'completed' => 22, 'cancelled' => 1, 'to-rate' => 0], $all['status_counts']);
        $ship = $read(['status_group' => 'to-ship']);
        $this->assertCount(4, $ship['orders']);
        $this->assertEqualsCanonicalizing(['to-ship', 'CONFIRMED', 'PREPARING', 'READY_FOR_PICKUP'], array_column($ship['orders'], 'status'));
        $receive = $read(['status_group' => 'to-receive']);
        $this->assertSame(['to-receive'], array_column($receive['orders'], 'status'));
        $completed = $read(['status_group' => 'completed']);
        $this->assertCount(20, $completed['orders']);
        $this->assertTrue($completed['has_more']);
        $this->assertSame($all['status_counts'], $completed['status_counts']);
    }
}
