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

    public function update(Request $request): RedirectResponse
    {
        // Ignore submitted identity/role/document/business fields: only the session's core profile is editable.
        $seller = $request->user()->seller;
        abort_unless($seller, 404);
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100', 'regex:/^[A-Za-zÀ-ÿñÑ\s\'.-]+$/'],
            'last_name' => ['required', 'string', 'max:100', 'regex:/^[A-Za-zÀ-ÿñÑ\s\'.-]+$/'],
            'middle_initial' => ['nullable', 'string', 'max:2'],
            'sex' => ['required', Rule::in($this->sexOptions())],
            'contact_no' => ['required', 'regex:/^09\d{9}$/'],
            'birthday' => ['required', 'date', 'before:today'],
        ]);

        // Success is flashed only after the existing Seller row has actually persisted.
        $seller->update($validated);

        return redirect()->route('seller.account')->with('status', 'Profile changes saved.');
    }

    // Match the existing Buyer convention: SQLite retains the original two-value CHECK constraint.
    private function sexOptions(): array
    {
        return DB::connection()->getDriverName() === 'sqlite'
            ? ['Male', 'Female']
            : ['Male', 'Female', 'Prefer not to say'];
    }
}
