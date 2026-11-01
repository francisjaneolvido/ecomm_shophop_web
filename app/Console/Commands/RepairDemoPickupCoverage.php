<?php

namespace App\Console\Commands;

use App\Models\Buyer\Order\Order;
use App\Models\Logistics\Delivery;
use App\Models\Logistics\OperationalServiceArea;
use App\Models\Logistics\PickupRequest;
use App\Models\Logistics\Rider;
use App\Models\Logistics\SortingCenter;
use App\Services\RiderServiceAreaService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

/** Demo-only, operator-approved repair. Never assigns a parcel or changes custody. */
class RepairDemoPickupCoverage extends Command
{
    protected $signature = 'shophop:repair-demo-pickup-coverage
        {order : Existing ready-for-pickup order ID}
        {--apply : Explicitly add missing DEMO pickup coverage/link to an existing DEMO rider}';

    protected $description = 'Audit or safely repair existing nationwide demo pickup rider coverage for one order';

    public function handle(RiderServiceAreaService $matcher): int
    {
        if (! app()->environment(['local', 'testing'])) {
            $this->error('Refused: demo repair is allowed only in local/testing.');
            return self::FAILURE;
        }
        if (! Schema::hasTable('operational_service_areas') || ! Schema::hasTable('operational_area_rider')) {
            $this->error('Missing service area tables. Review migrations, without dropping any tables.');
            return self::FAILURE;
        }
        $id = (int) $this->argument('order');
        if ($id <= 0 || (string) $id !== (string) $this->argument('order')) {
            $this->error('Provide a valid positive integer order ID.');
            return self::FAILURE;
        }
        try {
            // Re-verify every condition under row locks before changing anything.
            $action = function () use ($id, $matcher): array {
                $order = Order::with(['seller.user'])->whereKey($id)->lockForUpdate()->first();
                if (! $order || ! $order->seller) throw new RuntimeException('Order or Seller record is missing.');
                $pickup = PickupRequest::where('order_id', $order->id)->lockForUpdate()->first();
                if (! $pickup || $pickup->status !== PickupRequest::REQUESTED || $order->status !== Order::STATUS_READY_FOR_PICKUP
                    || Delivery::where('order_id', $order->id)->exists()) {
                    throw new RuntimeException('Order is not an unassigned, requested pickup. No changes made.');
                }
                $center = SortingCenter::with('partner.user')->whereKey($pickup->origin_sorting_center_id)->first();
                if (! $center || ! str_starts_with((string) $center->code, 'NW-C-') || $center->type !== 'station_hub'
                    || $center->status !== 'active' || $center->archived_at !== null
                    || (int) $center->logistics_partner_id !== (int) $pickup->logistics_partner_id
                    || $center->partner?->user?->email !== 'nationwide.logistics@example.com') {
                    throw new RuntimeException('Origin is not an active Nationwide DEMO city branch. No changes made.');
                }
                $seller = $order->seller;
                $province = trim((string) $seller->province_name);
                $city = trim((string) $seller->municipality_name);
                $barangay = trim((string) $seller->barangay_name);
                if ($province === '' || $city === '' || $barangay === ''
                    || RiderServiceAreaService::normalize($center->province) !== RiderServiceAreaService::normalize($province)
                    || RiderServiceAreaService::normalizeCity($center->municipality) !== RiderServiceAreaService::normalizeCity($city)) {
                    throw new RuntimeException('Seller province/city/barangay is incomplete or does not match the origin branch. Fix the address/route first.');
                }
                $all = Rider::query()->with('operationalServiceAreas')
                    ->where('logistics_partner_id', $center->logistics_partner_id)
                    ->where('sorting_center_id', $center->id)
                    ->orderBy('id')->get();
                $riders = $all->filter(fn (Rider $r) => $r->status === 'active'
                    && $r->availability_status === 'available'
                    && filled($r->password) && filled($r->email)
                    && preg_match('/^nw\\.[0-9]+\\.rider[0-9]*@example\.com$/i', (string) $r->email) === 1);
                $areas = OperationalServiceArea::query()->where('sorting_center_id', $center->id)
                    ->where('logistics_partner_id', $center->logistics_partner_id)->get();
                $exact = $areas->filter(fn (OperationalServiceArea $a) =>
                    $a->barangay !== null && $matcher->matches($a, $province, $city, $barangay, 'pickup'));
                $covered = $areas->filter(fn (OperationalServiceArea $a) =>
                    $matcher->matches($a, $province, $city, $barangay, 'pickup'));
                // Diagnostic must not use the legacy "unconfigured branch" bypass.
                $eligible = $all->filter(fn (Rider $r) => $r->status === 'active'
                    && $r->availability_status === 'available'
                    && filled($r->email) && filled($r->password)
                    && $r->operationalServiceAreas->contains(fn (OperationalServiceArea $a) =>
                        (int) $a->sorting_center_id === (int) $center->id
                        && $matcher->matches($a, $province, $city, $barangay, 'pickup')));
                $this->line("Order #{$id}: Seller {$barangay}, {$city}, {$province}");
                $this->line("Origin branch: {$center->name} ({$center->code})");
                $this->line("Riders at branch: {$all->count()} | active+available demo riders: {$riders->count()}");
                $this->line("Matching pickup areas: {$covered->count()} | eligible logged-in riders: {$eligible->count()}");
                foreach ($all->take(8) as $rider) {
                    $this->line("  Rider #{$rider->id} {$rider->name} [{$rider->status} / {$rider->availability_status}] linked areas: {$rider->operationalServiceAreas->count()}");
                }
                if ($eligible->isNotEmpty()) return ['type' => 'ready', 'rider' => $eligible->first()->name];
                if ($riders->isEmpty()) throw new RuntimeException('No active/available DEMO rider account at this branch. Activate an existing rider via management first.');
                if (! $this->option('apply')) return ['type' => 'dryrun'];
                // An exact existing area is preferred. Never grant full-city coverage or change existing riders' status.
                $area = $exact->first();
                if (! $area) {
                    // Explicit opt-in creates only the Seller's specific DEMO barangay pickup row.
                    $key = RiderServiceAreaService::locationKey($province, $city, $barangay, 'pickup');
                    $area = OperationalServiceArea::query()->firstOrCreate([
                        'sorting_center_id' => $center->id, 'location_key' => $key,
                    ], [
                        'logistics_partner_id' => $center->logistics_partner_id,
                        'province' => $province, 'municipality' => $city, 'barangay' => $barangay,
                        'service_type' => 'pickup',
                    ]);
                    if ((int) $area->logistics_partner_id !== (int) $center->logistics_partner_id
                        || ! $matcher->matches($area, $province, $city, $barangay, 'pickup')
                        || $area->barangay === null) {
                        throw new RuntimeException('Existing service-area key conflicts with the Seller location. No changes made.');
                    }
                }
                $rider = $riders->first();
                $rider->operationalServiceAreas()->syncWithoutDetaching([$area->id]);
                // Force refresh of pivot and eligibility: never report success for a failed link.
                $rider->unsetRelation('operationalServiceAreas');
                $verified = $rider->operationalServiceAreas()->where('operational_service_areas.id', $area->id)->exists()
                    && (new RiderServiceAreaService)->eligible($rider, $center->id, $province, $city, $barangay, 'pickup');
                if (! $verified) {
                    throw new RuntimeException('Post-repair eligibility failed. Transaction rolled back.');
                }
                return ['type' => 'linked', 'rider' => $rider->name];
            };
            $result = $this->option('apply') ? DB::transaction($action, 2) : $action();
            if ($result['type'] === 'ready') {
                $this->info('Already covered by eligible rider: '.$result['rider'].'. No changes made.');
            } elseif ($result['type'] === 'dryrun') {
                $this->warn('DRY RUN: No records changed. If this is disposable DEMO data, rerun with --apply to link an existing DEMO rider to this exact pickup barangay.');
            } else {
                $this->info('DEMO pickup coverage linked to existing rider: '.$result['rider'].'. No order, custody, or COD records changed.');
            }
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('No repair completed: '.$e->getMessage());
            return self::FAILURE;
        }
    }
}
