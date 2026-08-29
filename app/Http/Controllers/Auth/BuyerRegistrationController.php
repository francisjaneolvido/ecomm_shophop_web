<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Buyer;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class BuyerRegistrationController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100', 'regex:/^[A-Za-zÀ-ÿñÑ\s\'.-]+$/'],
            'last_name' => ['required', 'string', 'max:100', 'regex:/^[A-Za-zÀ-ÿñÑ\s\'.-]+$/'],
            'middle_initial' => ['nullable', 'string', 'max:2'],
            'sex' => ['required', 'in:Male,Female'],
            'email' => ['required', 'email', 'max:191', 'unique:users,email'],
            'contact_no' => ['required', 'regex:/^09\d{9}$/'],
            'birthday' => ['required', 'date', 'before:today'],
            'province_code' => ['required', 'string', 'max:20'],
            'province_name' => ['required', 'string', 'max:150'],
            'municipality_code' => ['required', 'string', 'max:20'],
            'municipality_name' => ['required', 'string', 'max:150'],
            'barangay_code' => ['required', 'string', 'max:20'],
            'barangay_name' => ['required', 'string', 'max:150'],
            'street_address' => ['required', 'string'],
            'valid_id' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
            'terms' => ['required', 'accepted'],
        ]);

        DB::transaction(function () use ($validated, $request) {
            $user = User::create([
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'account_type' => 'buyer',
                'status' => 'pending',
            ]);

            $validIdPath = $request->file('valid_id')->store('valid_ids/buyers', 'public');

            Buyer::create([
                'user_id' => $user->id,
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'middle_initial' => $validated['middle_initial'] ?? null,
                'sex' => $validated['sex'],
                'contact_no' => $validated['contact_no'],
                'birthday' => $validated['birthday'],
                'province_code' => $validated['province_code'],
                'province_name' => $validated['province_name'],
                'municipality_code' => $validated['municipality_code'],
                'municipality_name' => $validated['municipality_name'],
                'barangay_code' => $validated['barangay_code'],
                'barangay_name' => $validated['barangay_name'],
                'street_address' => $validated['street_address'],
                'valid_id_path' => $validIdPath,
            ]);
        });

        return redirect('/')
            ->with('status', 'Your buyer account has been submitted! Please wait for admin approval before signing in.');
    }
}