<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

// Reuse registration's Seller profile rather than presenting a second, fictional account identity.
class AccountController extends Controller
{
    public function show(Request $request): View
    {
        // The session owns this profile; absent registration data must never fall back to demo records.
        $seller = $request->user()->seller;
        abort_unless($seller, 404);

        return view('seller.account', [
            'seller' => $seller,
            'sellerName' => trim($seller->first_name.' '.$seller->last_name),
            'sellerBusinessName' => $seller->business_name,
            'sexOptions' => $this->sexOptions(),
        ]);
    }

    public function shopProfile(Request $request): View
    {
        $seller = $request->user()->seller;
        abort_unless($seller, 404);
        $products = \App\Models\Seller\Manage_inventory\Product::query()
            ->where('seller_id', $request->user()->id);
        $summary = [
            'products' => (clone $products)->count(),
            'active' => (clone $products)->where('status', 'active')->count(),
            'review' => (clone $products)->where('compliance_status', 'pending_review')->count(),
        ];
        return view('seller.shop-profile', compact('seller', 'summary'));
    }

    public function update(Request $request): RedirectResponse
    {
        app(\App\Services\SellerAccountService::class)->updateProfile($request);

        return redirect()->route('seller.account')->with('status', 'Profile changes saved.');
    }

    public function password(Request $request): RedirectResponse
    {
        app(\App\Services\SellerAccountService::class)->changePassword($request);
        return redirect()->route('seller.account')->with('status', 'Password changed.');
    }

    private function sexOptions(): array
    {
        return app(\App\Services\SellerAccountService::class)->sexOptions();
    }
}
