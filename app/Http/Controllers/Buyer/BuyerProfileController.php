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
}
