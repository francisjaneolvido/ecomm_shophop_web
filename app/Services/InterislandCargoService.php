<?php
namespace App\Services;

use App\Models\Logistics\InterislandCargoTransfer;
use App\Models\Logistics\ParcelManifest;
use App\Models\Logistics\ParcelTransfer;
use App\Models\Logistics\RouteLink;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/** One sea/air cargo booking per manifest; hub operators confirm custody, not road drivers. */
class InterislandCargoService
{
    public function book(ParcelManifest $manifest, int $routeLinkId, string $carrier, string $reference, int $userId): InterislandCargoTransfer
    {
        return DB::transaction(function () use ($manifest, $routeLinkId, $carrier, $reference, $userId) {
            $m = ParcelManifest::whereKey($manifest->id)->lockForUpdate()->firstOrFail();
            if ($m->status !== ParcelManifest::READY || $m->linehaul_trip_id || $m->cargoTransfer()->exists()) {
                throw ValidationException::withMessages(['cargo' => 'Only a fully loaded, unassigned manifest can be booked for cargo.']);
            }
            $from = $m->fromCenter()->firstOrFail();
            $to = $m->toCenter()->firstOrFail();
            if ($from->type !== 'province_hub' || $to->type !== 'province_hub' ||
                $from->status !== 'active' || $to->status !== 'active' || $from->archived_at || $to->archived_at) {
                throw ValidationException::withMessages(['cargo' => 'Cargo must connect two active Province Hubs in this network.']);
            }
            $link = RouteLink::whereKey($routeLinkId)
                ->where('logistics_partner_id', $m->logistics_partner_id)
                ->where('from_center_id', $from->id)->where('to_center_id', $to->id)
                ->where('status', 'verified')->whereNotNull('verified_at')
                ->whereIn('mode', ['sea','air'])->first();
            if (! $link) {
                throw ValidationException::withMessages(['route_link_id' => 'No verified Sea/Air connection exists for this exact hub-to-hub leg.']);
            }
            $count = DB::table('parcel_manifest_items')->where('manifest_id', $m->id)->count();
            $loaded = DB::table('parcel_manifest_items')->where('manifest_id', $m->id)->whereNotNull('loaded_at')->count();
            if ($count < 1 || $loaded !== $count) {
                throw ValidationException::withMessages(['cargo' => 'All parcels must be physically loaded and confirmed first.']);
            }
            return InterislandCargoTransfer::create([
                'manifest_id'=>$m->id, 'logistics_partner_id'=>$m->logistics_partner_id,
                'route_link_id'=>$link->id, 'mode'=>$link->mode,
                'carrier_name'=>trim($carrier),'booking_reference'=>trim($reference),
                'status'=>InterislandCargoTransfer::BOOKED,'booked_by_user_id'=>$userId,
            ]);
        }, 3);
    }

    /** Origin hub operator confirms real carrier handoff after physical load/count check. */
    public function depart(int $manifestId, int $originCenterId, int $partnerId, int $operatorUserId, ?int $staffId = null): void
    {
        DB::transaction(function () use ($manifestId, $originCenterId, $partnerId, $operatorUserId, $staffId) {
            $m = ParcelManifest::whereKey($manifestId)->where('logistics_partner_id', $partnerId)
                ->where('from_sorting_center_id', $originCenterId)->lockForUpdate()->firstOrFail();
            $cargo = InterislandCargoTransfer::where('manifest_id', $m->id)->lockForUpdate()->first();
            if (! $cargo || $cargo->status !== InterislandCargoTransfer::BOOKED || $m->status !== ParcelManifest::READY || $m->linehaul_trip_id) {
                throw ValidationException::withMessages(['cargo' => 'Cargo booking must be booked and manifest fully ready before carrier handoff.']);
            }
            if (! RouteLink::whereKey($cargo->route_link_id)->where('status', 'verified')->whereNotNull('verified_at')
                ->where('logistics_partner_id',$partnerId)->where('from_center_id',$m->from_sorting_center_id)
                ->where('to_center_id',$m->to_sorting_center_id)->where('mode',$cargo->mode)->exists()) {
                throw ValidationException::withMessages(['cargo' => 'The verified route link was suspended or changed. Revalidate it before handoff.']);
            }
            $items = DB::table('parcel_manifest_items')->where('manifest_id',$m->id)->lockForUpdate()->get();
            if ($items->isEmpty() || $items->contains(fn ($i) => ! $i->loaded_at)) {
                throw ValidationException::withMessages(['cargo' => 'Complete physical manifest loading first.']);
            }
            $ids=$items->pluck('delivery_id')->all();
            $deliveries=\App\Models\Logistics\Delivery::whereIn('id',$ids)->orderBy('id')->lockForUpdate()->get();
            if ($deliveries->count() !== $items->count() ||
                $deliveries->contains(fn($d) => (int)$d->current_sorting_center_id !== $originCenterId ||
                    $d->status !== \App\Models\Logistics\Delivery::SORTED)) {
                throw ValidationException::withMessages(['cargo'=>'One or more parcels are no longer sorted at this origin. Reconcile before dispatch.']);
            }
            if (ParcelTransfer::whereIn('delivery_id',$ids)->where('status',ParcelTransfer::IN_TRANSIT)->exists()) {
                throw ValidationException::withMessages(['cargo' => 'A parcel in this manifest is already in transit.']);
            }
            $now=now();
            foreach ($items as $item) {
                $transfer=ParcelTransfer::create([
                    'delivery_id'=>$item->delivery_id,'manifest_id'=>$m->id,
                    'from_sorting_center_id'=>$m->from_sorting_center_id,
                    'to_sorting_center_id'=>$m->to_sorting_center_id,
                    'status'=>ParcelTransfer::IN_TRANSIT,'cargo_transfer_id'=>$cargo->id,
                    'departed_at'=>$now,'dispatched_at'=>$now,
                ]);
                DB::table('parcel_scan_events')->insert([
                    'delivery_id'=>$item->delivery_id,'sorting_center_id'=>$originCenterId,
                    'operator_user_id'=>$operatorUserId,'operator_staff_id'=>$staffId,
                    'operator_rider_id'=>null,'operator_linehaul_driver_id'=>null,
                    'parcel_transfer_id'=>$transfer->id,'event_type'=>'cargo_departed',
                    'scanned_at'=>$now,'created_at'=>$now,'updated_at'=>$now,
                ]);
            }
            $m->forceFill(['status'=>ParcelManifest::DISPATCHED,'dispatched_at'=>$now])->save();
            $cargo->forceFill(['status'=>InterislandCargoTransfer::IN_TRANSIT,'departed_at'=>$now])->save();
        }, 3);
    }

    public function completeIfReceived(int $cargoId): void
    {
        DB::transaction(function () use ($cargoId) {
            $cargo=InterislandCargoTransfer::whereKey($cargoId)->lockForUpdate()->first();
            if (! $cargo || $cargo->status !== InterislandCargoTransfer::ARRIVED) return;
            $total=ParcelTransfer::where('cargo_transfer_id',$cargoId)->count();
            if ($total < 1 || ParcelTransfer::where('cargo_transfer_id',$cargoId)
                ->where('status','!=',ParcelTransfer::RECEIVED)->exists()) return;
            $cargo->forceFill(['status'=>InterislandCargoTransfer::COMPLETED,'completed_at'=>now()])->save();
            ParcelManifest::whereKey($cargo->manifest_id)->update(['status'=>ParcelManifest::RECEIVED]);
        }, 3);
    }
}
