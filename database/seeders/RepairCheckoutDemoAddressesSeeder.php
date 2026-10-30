<?php

namespace Database\Seeders;

use App\Models\Buyer;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/** Opt-in local-only repair of known placeholder test accounts. Never modifies real addresses. */
class RepairCheckoutDemoAddressesSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new \RuntimeException('Only for local/testing.');
        }

        DB::transaction(function (): void {
            $location = [
                'province_code' => '0434', 'province_name' => 'Laguna',
                'municipality_code' => '043405', 'municipality_name' => 'Calamba City',
                'barangay_code' => '043405001', 'barangay_name' => 'Barangay 1',
            ];
            $buyerUser = User::where('email', 'test.buyer@example.com')->where('account_type', 'buyer')->first();
            if ($buyerUser) {
                $buyer = Buyer::where('user_id', $buyerUser->id)
                    ->where('province_name', 'Test Province')->where('municipality_name', 'Test Municipality')->first();
                if ($buyer) {
                    $buyer->fill($location + ['street_address' => '123 Demo Street'])->save();
                    $this->command?->info('Updated placeholder TEST buyer location to Calamba, Laguna.');
                }
            }
            $sellerUser = User::where('email', 'test.seller@example.com')->where('account_type', 'seller')->first();
            if ($sellerUser) {
                $seller = Seller::where('user_id', $sellerUser->id)
                    ->where('province_name', 'Test Province')->where('municipality_name', 'Test Municipality')->first();
                if ($seller) {
                    $seller->fill($location + ['street_address' => '456 Demo Seller Street'])->save();
                    $this->command?->info('Updated placeholder TEST seller location to Calamba, Laguna.');
                }
                // Only repair legacy dummy business category; keep real registrations unchanged.
                Seller::where('user_id', $sellerUser->id)->where('business_category', 'General Merchandise')
                    ->update(['business_category' => 'Pet Supplies']);
            }
        });
    }
}
