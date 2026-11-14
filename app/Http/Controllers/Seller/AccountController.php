<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
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

    public function updateShopLogo(Request $request): RedirectResponse
    {
        $seller = $request->user()->seller;
        abort_unless($seller, 404);

        $data = $request->validate([
            'logo' => ['nullable', 'required_without:remove_logo', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_logo' => ['nullable', 'boolean'],
        ]);
        if (! Schema::hasColumn('sellers', 'logo_path')) {
            return back()->withErrors(['logo' => 'Shop logo upload needs the pending database migration.']);
        }

        if (! $request->hasFile('logo') && ! $request->boolean('remove_logo')) {
            return back()->withErrors(['logo' => 'Choose a photo or remove the current logo.']);
        }

        $oldPath = $seller->logo_path;
        $newPath = $request->hasFile('logo')
            ? $request->file('logo')->store('shop-logos', 'public') : null;
        try {
            $seller->forceFill(['logo_path' => $newPath])->save();
        } catch (\Throwable $error) {
            if ($newPath) Storage::disk('public')->delete($newPath);
            throw $error;
        }
        if ($oldPath && $oldPath !== $newPath) {
            Storage::disk('public')->delete($oldPath);
        }

        return back()->with('status', $newPath ? 'Shop logo updated.' : 'Shop logo removed.');
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
