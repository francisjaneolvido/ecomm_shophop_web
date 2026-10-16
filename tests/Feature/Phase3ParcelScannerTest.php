<?php

namespace Tests\Feature;

use App\Models\Logistics\Delivery;
use App\Services\ParcelScanService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

/** Disposable minimum schema: test custody transitions without requiring SMTP, Flutter, or full production seeders. */
class Phase3ParcelScannerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Schema::dropAllTables();
        Schema::create('orders', function (Blueprint $t) { $t->id(); $t->string('status'); $t->timestamps(); });
        Schema::create('sorting_centers', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('logistics_partner_id'); $t->string('name');
            $t->string('status'); $t->timestamps();
        });
        Schema::create('deliveries', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('order_id'); $t->string('tracking_code');
            $t->unsignedBigInteger('logistics_partner_id');
            $t->unsignedBigInteger('origin_sorting_center_id')->nullable();
            $t->unsignedBigInteger('current_sorting_center_id')->nullable();
            $t->string('status'); $t->timestamp('at_sorting_center_at')->nullable(); $t->timestamps();
        });
        Schema::create('pickup_requests', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('order_id'); $t->string('status');
            $t->timestamp('received_at')->nullable(); $t->timestamps();
        });
        Schema::create('parcel_transfers', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('delivery_id');
            $t->unsignedBigInteger('from_sorting_center_id');
            $t->unsignedBigInteger('to_sorting_center_id');
            $t->unsignedBigInteger('transit_rider_id')->nullable();
            $t->timestamp('departed_at')->nullable();
            $t->string('status'); $t->timestamp('received_at')->nullable(); $t->timestamps();
        });
        Schema::create('parcel_scan_events', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('delivery_id');
            $t->unsignedBigInteger('sorting_center_id'); $t->unsignedBigInteger('operator_user_id');
            $t->unsignedBigInteger('parcel_transfer_id')->nullable();
            $t->string('event_type'); $t->timestamp('scanned_at'); $t->timestamps();
        });
        DB::table('orders')->insert(['id'=>1, 'status'=>'to-receive', 'created_at'=>now(), 'updated_at'=>now()]);
        DB::table('sorting_centers')->insert([
            ['id'=>1,'logistics_partner_id'=>10,'name'=>'Origin','status'=>'active'],
            ['id'=>2,'logistics_partner_id'=>10,'name'=>'Destination','status'=>'active'],
            ['id'=>3,'logistics_partner_id'=>11,'name'=>'Foreign','status'=>'active'],
        ]);
        DB::table('deliveries')->insert([
            'id'=>1,'order_id'=>1,'tracking_code'=>'SHP-20261015-000001',
            'logistics_partner_id'=>10,'origin_sorting_center_id'=>1,'status'=>Delivery::PICKED_UP,
        ]);
    }

    public function test_origin_scan_is_logged_and_cannot_be_repeated(): void
    {
        $scanner = app(ParcelScanService::class);
        $receipt = $scanner->receive(10, 1, 23, 'SHP-20261015-000001');
        $this->assertSame('pickup_received', $receipt['event']);
        $this->assertSame(Delivery::AT_SORTING_CENTER, Delivery::find(1)->status);
        $this->assertSame(1, DB::table('parcel_scan_events')->count());
        $this->expectException(ValidationException::class);
        $scanner->receive(10, 1, 23, 'SHP-20261015-000001');
    }

    public function test_wrong_branch_does_not_change_custody(): void
    {
        try {
            app(ParcelScanService::class)->receive(10, 2, 23, 'SHP-20261015-000001');
            $this->fail('Expected branch mismatch to be rejected.');
        } catch (ValidationException $e) {
            $this->assertSame(Delivery::PICKED_UP, Delivery::find(1)->status);
            $this->assertSame(0, DB::table('parcel_scan_events')->count());
        }
    }

    public function test_transfer_requires_departure_and_destination_branch(): void
    {
        DB::table('deliveries')->where('id',1)->update(['status'=>Delivery::SORTED,'current_sorting_center_id'=>1]);
        DB::table('parcel_transfers')->insert([
            'id'=>1, 'delivery_id'=>1, 'from_sorting_center_id'=>1, 'to_sorting_center_id'=>2,
            'transit_rider_id'=>7, 'status'=>'in_transit',
        ]);
        $scanner = app(ParcelScanService::class);
        try {
            $scanner->receive(10, 2, 23, 'SHP-20261015-000001');
            $this->fail('Expected pending departure to block receipt.');
        } catch (ValidationException $e) {
            $this->assertSame(0, DB::table('parcel_scan_events')->count());
        }
        DB::table('parcel_transfers')->where('id',1)->update(['departed_at'=>now()]);
        $receipt = $scanner->receive(10, 2, 23, 'SHP-20261015-000001');
        $this->assertSame('transfer_received', $receipt['event']);
        $this->assertSame(2, (int) Delivery::find(1)->current_sorting_center_id);
        $this->assertSame('received', DB::table('parcel_transfers')->value('status'));
    }
}
