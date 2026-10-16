<?php

namespace App\Services;

use App\Models\Buyer\Order\Order;
use App\Models\Finance\OrderFinancial;
use App\Models\Finance\SettlementEntry;
use App\Models\Logistics\Delivery;
use App\Models\Logistics\ReturnCase;
use App\Models\Logistics\Rider;
use App\Models\Logistics\SortingCenter;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/** Seller-return state transitions. No automatic bank transfer or inventory restock. */
class ParcelReturnService
{
    private function invalid(string $message): never
    { throw ValidationException::withMessages(['return' => $message]); }

    public function initiate(int $partnerId, int $deliveryId, int $operatorId, string $reason): void
    {
        DB::transaction(function () use ($partnerId,$deliveryId,$operatorId,$reason) {
            $parcel = Delivery::where('logistics_partner_id',$partnerId)->whereKey($deliveryId)
                ->lockForUpdate()->firstOrFail();
            $order = Order::whereKey($parcel->order_id)->lockForUpdate()->firstOrFail();
            if ($parcel->status !== Delivery::DELIVERY_FAILED || $order->status !== Order::STATUS_TO_RECEIVE
                || ! $order->isPaymentEligible()) $this->invalid('Only a failed, payment-eligible delivery can be returned.');
            if (ReturnCase::where('order_id',$order->id)->exists()) $this->invalid('Return already exists.');
            if (SettlementEntry::where('order_id',$order->id)->exists()) $this->invalid('Settlement has already been prepared; Admin must resolve this separately.');
            if ($order->payment_method === 'cod' && $order->codSettlement()->exists()) $this->invalid('COD cash has already been recorded. Resolve cash custody first.');
            $finance = OrderFinancial::where('order_id',$order->id)->lockForUpdate()->first();
            if (! $finance) $this->invalid('Financial snapshot missing. Contact Admin before returning.');
            $finance->update(['settlement_hold'=>true,'hold_reason'=>'Return-to-Seller in progress']);
            ReturnCase::create([
                'order_id'=>$order->id,'delivery_id'=>$parcel->id,'logistics_partner_id'=>$partnerId,
                'status'=>ReturnCase::REQUESTED,'reason'=>trim($reason),'requested_by_user_id'=>$operatorId,
                'refund_status'=>$order->payment_method === 'online' ? 'pending' : 'not_applicable',
                'refund_amount'=>$order->payment_method === 'online' ? (float) $order->total_amount : 0,
            ]);
            $parcel->update(['status'=>Delivery::RETURN_REQUESTED]);
        });
    }

    public function receiveBranch(int $partnerId, int $caseId, int $operatorId, int $branchId, string $tracking): void
    {
        DB::transaction(function () use ($partnerId,$caseId,$operatorId,$branchId,$tracking) {
            $case = ReturnCase::where('logistics_partner_id',$partnerId)->whereKey($caseId)->lockForUpdate()->firstOrFail();
            $parcel = Delivery::whereKey($case->delivery_id)->lockForUpdate()->firstOrFail();
            $branch = SortingCenter::where('logistics_partner_id',$partnerId)->where('status','active')->findOrFail($branchId);
            if ($case->status !== ReturnCase::REQUESTED || $parcel->status !== Delivery::RETURN_REQUESTED)
                $this->invalid('Return is not waiting for branch receipt.');
            if (! hash_equals((string)$parcel->tracking_code, trim($tracking))) $this->invalid('Tracking code does not match this return.');
            if ($parcel->destination_sorting_center_id && (int)$parcel->destination_sorting_center_id !== (int)$branch->id)
                $this->invalid('The parcel must be received at its destination branch before return dispatch.');
            $case->update(['status'=>ReturnCase::AT_BRANCH,'return_branch_id'=>$branch->id,
                'received_by_user_id'=>$operatorId,'branch_received_at'=>now()]);
            $parcel->update(['status'=>Delivery::RETURN_AT_BRANCH,'current_sorting_center_id'=>$branch->id]);
        });
    }

    public function assignRider(int $partnerId, int $caseId, int $riderId): void
    {
        DB::transaction(function () use ($partnerId,$caseId,$riderId) {
            $case=ReturnCase::where('logistics_partner_id',$partnerId)->whereKey($caseId)->lockForUpdate()->firstOrFail();
            $rider=Rider::where('logistics_partner_id',$partnerId)->where('status','active')
                ->whereNotNull('email_verified_at')->findOrFail($riderId);
            if ($case->status !== ReturnCase::AT_BRANCH || (int)$case->return_branch_id !== (int)$rider->sorting_center_id)
                $this->invalid('Return Rider must be active and assigned to the receiving branch.');
            $case->update(['status'=>ReturnCase::ASSIGNED,'return_rider_id'=>$riderId]);
            Delivery::whereKey($case->delivery_id)->update(['status'=>Delivery::RETURN_ASSIGNED]);
        });
    }

    public function riderCollect(int $riderId, int $partnerId, int $caseId): void
    {
        DB::transaction(function () use ($riderId,$partnerId,$caseId) {
            $case=ReturnCase::where('logistics_partner_id',$partnerId)->where('return_rider_id',$riderId)
                ->whereKey($caseId)->lockForUpdate()->firstOrFail();
            if ($case->status !== ReturnCase::ASSIGNED) $this->invalid('Return parcel is not ready for pickup.');
            $case->update(['status'=>ReturnCase::IN_TRANSIT,'rider_collected_at'=>now()]);
            Delivery::whereKey($case->delivery_id)->update(['status'=>Delivery::RETURN_IN_TRANSIT]);
        });
    }

    public function riderHandover(int $riderId, int $partnerId, int $caseId, string $proofPath): void
    {
        DB::transaction(function () use ($riderId,$partnerId,$caseId,$proofPath) {
            $case=ReturnCase::where('logistics_partner_id',$partnerId)->where('return_rider_id',$riderId)
                ->whereKey($caseId)->lockForUpdate()->firstOrFail();
            if ($case->status !== ReturnCase::IN_TRANSIT) $this->invalid('Confirm return collection before submitting Seller handover proof.');
            $case->update(['status'=>ReturnCase::AWAITING_SELLER,'handover_proof_path'=>$proofPath,
                'handover_submitted_at'=>now()]);
            Delivery::whereKey($case->delivery_id)->update(['status'=>Delivery::RETURN_AWAITING_SELLER]);
        });
    }

    public function sellerAcknowledge(int $sellerId, int $caseId): void
    {
        DB::transaction(function () use ($sellerId,$caseId) {
            $case=ReturnCase::whereKey($caseId)->lockForUpdate()->firstOrFail();
            $order=Order::where('seller_id',$sellerId)->whereKey($case->order_id)->lockForUpdate()->firstOrFail();
            if ($case->status !== ReturnCase::AWAITING_SELLER || $order->status !== Order::STATUS_TO_RECEIVE
                || ! $case->handover_proof_path) $this->invalid('A Rider handover proof is required before confirming Seller receipt.');
            $case->update(['status'=>ReturnCase::SELLER_RECEIVED,'seller_received_at'=>now()]);
            Delivery::whereKey($case->delivery_id)->update(['status'=>Delivery::RETURNED,'returned_at'=>now()]);
            $order->update(['status'=>Order::STATUS_RETURNED]);
            // Deliberately DO NOT restock inventory. Seller inspects actual returned goods first.
        });
    }

    public function recordRefund(int $caseId, int $adminId, string $reference): void
    {
        DB::transaction(function () use ($caseId,$adminId,$reference) {
            $case=ReturnCase::whereKey($caseId)->lockForUpdate()->firstOrFail();
            $order=Order::whereKey($case->order_id)->lockForUpdate()->firstOrFail();
            if ($case->status !== ReturnCase::SELLER_RECEIVED || $case->refund_status !== 'pending'
                || $order->status !== Order::STATUS_RETURNED || $order->payment_method !== 'online'
                || ! $order->isPaymentEligible() || (float)$case->refund_amount <= 0)
                $this->invalid('Only a verified, Seller-received prepaid return can be marked refunded.');
            if (SettlementEntry::where('order_id',$order->id)->exists()) $this->invalid('Payout entries exist. Manual reconciliation is required.');
            $case->update(['refund_status'=>'paid','refund_reference'=>trim($reference),
                'refunded_by_user_id'=>$adminId,'refunded_at'=>now()]);
            // Settlement hold remains: this returned order is not eligible for ShopHop Seller payout.
        });
    }
}
