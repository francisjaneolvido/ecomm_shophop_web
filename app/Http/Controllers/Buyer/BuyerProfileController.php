<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class BuyerProfileController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $buyer = $request->user()->buyer;
        abort_unless($buyer, 404);

        // Only buyer-owned identity fields are editable here; email needs a separate verification flow.
        // The existing SQLite buyer enum still has a two-value CHECK; the MySQL migration adds the third value.
        $sexOptions = DB::connection()->getDriverName() === 'sqlite'
            ? ['Male', 'Female']
            : ['Male', 'Female', 'Prefer not to say'];

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100', 'regex:/^[A-Za-zÀ-ÿñÑ\s\'.-]+$/'],
            'last_name' => ['required', 'string', 'max:100', 'regex:/^[A-Za-zÀ-ÿñÑ\s\'.-]+$/'],
            'middle_initial' => ['nullable', 'string', 'max:2'],
            'sex' => ['required', Rule::in($sexOptions)],
            'contact_no' => ['required', 'regex:/^09\d{9}$/'],
            'birthday' => ['required', 'date', 'before:today'],
        ]);

        $buyer->update($validated);

        return redirect()->route('buyer.profile')->with('status', 'Profile changes saved.');
    }
    /** Updates the Buyer's single default/registration address.
     * Orders already placed keep their historical delivery snapshots. */
    public function updateAddress(Request $request): RedirectResponse
    {
        $buyer = $request->user()->buyer;
        abort_unless($buyer, 404);
        $data = $request->validate([
            'region_code' => ['required', 'string', 'max:20'],
            'region_name' => ['required', 'string', 'max:150'],
            'province_code' => ['required', 'string', 'max:20'],
            'province_name' => ['required', 'string', 'max:150'],
            'municipality_code' => ['required', 'string', 'max:20'],
            'municipality_name' => ['required', 'string', 'max:150'],
            'barangay_code' => ['required', 'string', 'max:20'],
            'barangay_name' => ['required', 'string', 'max:150'],
            'street' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'regex:/^09\d{9}$/'],
        ]);
        $buyer->update([
            'region_code' => $data['region_code'],
            'region_name' => $data['region_name'],
            'province_code' => $data['province_code'],
            'province_name' => $data['province_name'],
            'municipality_code' => $data['municipality_code'],
            'municipality_name' => $data['municipality_name'],
            'barangay_code' => $data['barangay_code'],
            'barangay_name' => $data['barangay_name'],
            'street_address' => trim($data['street']),
            'contact_no' => $data['phone'],
        ]);
        return redirect()->route('buyer.profile')->with('status', 'Your default delivery address was updated.');
    }

}
