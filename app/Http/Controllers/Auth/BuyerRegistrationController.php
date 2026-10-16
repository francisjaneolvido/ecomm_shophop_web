<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Buyer;
use App\Models\EmailVerificationCode;
use App\Models\User;
use App\Support\RegistrationIdentity;
// Shared sensitive-document storage also covers mobile registration and Admin review.
use App\Services\RegistrationDocuments;
use App\Notifications\BuyerVerificationCodeNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class BuyerRegistrationController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        /*
        |--------------------------------------------------------------------------
        | VALIDATE BUYER REGISTRATION
        |--------------------------------------------------------------------------
        */

        $request->merge([
            'registration_form' => 'buyer',
            'email' => RegistrationIdentity::email($request->input('email')),
            'contact_no' => RegistrationIdentity::mobile($request->input('contact_no')),
            'first_name' => trim((string) $request->input('first_name')),
            'last_name' => trim((string) $request->input('last_name')),
        ]);

        $validated = $request->validate([
            'first_name' => [
                'required',
                'string',
                'min:2',
                'max:50',
                'regex:/^[\p{L}][\p{L}\s\'.-]*$/u',
            ],

            'last_name' => [
                'required',
                'string',
                'min:2',
                'max:50',
                'regex:/^[\p{L}][\p{L}\s\'.-]*$/u',
            ],

            'middle_initial' => [
                'nullable',
                'string',
                'max:2',
            ],

            'sex' => [
                'required',
                Rule::in([
                    'Male',
                    'Female',
                    'Prefer not to say',
                ]),
            ],

            'email' => [
                'required',
                'email',
                'max:191',
                RegistrationIdentity::emailUnique(),
            ],

            'contact_no' => [
                'required',
                'regex:/^09\d{9}$/',
                RegistrationIdentity::mobileUnique(),
            ],

            'birthday' => [
                'required',
                'date',
                'before_or_equal:'.now()->subYears(18)->toDateString(),
            ],

            'region_code' => ['nullable', 'string', 'max:20'],
            'region_name' => ['nullable', 'string', 'max:150'],
            'province_code' => [
                'required',
                'string',
                'max:20',
            ],

            'province_name' => [
                'required',
                'string',
                'max:150',
            ],

            'municipality_code' => [
                'required',
                'string',
                'max:20',
            ],

            'municipality_name' => [
                'required',
                'string',
                'max:150',
            ],

            'barangay_code' => [
                'required',
                'string',
                'max:20',
            ],

            'barangay_name' => [
                'required',
                'string',
                'max:150',
            ],

            'street_address' => [
                'required',
                'string',
            ],

            'valid_id' => [
                'required',
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'max:5120',
            ],

            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],

            'terms' => [
                'required',
                'accepted',
            ],
        ]);


        /*
        |--------------------------------------------------------------------------
        | CREATE USER + BUYER PROFILE
        |--------------------------------------------------------------------------
        */

        $user = DB::transaction(function () use ($validated, $request) {

            /*
            |--------------------------------------------------------------------------
            | CREATE LOGIN ACCOUNT
            |--------------------------------------------------------------------------
            */

            $user = User::create([
                'email' => $validated['email'],
                'registration_contact_no' => $validated['contact_no'],
                'password' => Hash::make($validated['password']),
                'account_type' => 'buyer',
                'status' => 'pending',
            ]);


            /*
            |--------------------------------------------------------------------------
            | UPLOAD BUYER VALID ID
            |--------------------------------------------------------------------------
            */

            // Registration IDs belong to this account's private slot, never the public asset disk.
            $validIdPath = RegistrationDocuments::store($user, 'valid_id', $request->file('valid_id'));


            /*
            |--------------------------------------------------------------------------
            | CREATE BUYER PROFILE
            |--------------------------------------------------------------------------
            */

            Buyer::create([
                'user_id' => $user->id,

                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'middle_initial' => $validated['middle_initial'] ?? null,

                'sex' => $validated['sex'],
                'contact_no' => $validated['contact_no'],
                'birthday' => $validated['birthday'],

                'region_code' => $validated['region_code'] ?? null,
                'region_name' => $validated['region_name'] ?? null,
                'province_code' => $validated['province_code'],
                'province_name' => $validated['province_name'],

                'municipality_code' => $validated['municipality_code'],
                'municipality_name' => $validated['municipality_name'],

                'barangay_code' => $validated['barangay_code'],
                'barangay_name' => $validated['barangay_name'],

                'street_address' => $validated['street_address'],

                'valid_id_path' => $validIdPath,
            ]);

            return $user;
        });


        /*
        |--------------------------------------------------------------------------
        | GENERATE 6-DIGIT EMAIL VERIFICATION CODE
        |--------------------------------------------------------------------------
        */

        $code = (string) random_int(100000, 999999);


        /*
        |--------------------------------------------------------------------------
        | SAVE HASHED OTP
        |--------------------------------------------------------------------------
        */

        EmailVerificationCode::updateOrCreate(
            [
                'user_id' => $user->id,
            ],
            [
                'code_hash' => Hash::make($code),
                'attempts' => 0,
                'expires_at' => now()->addMinutes(5),
                'last_sent_at' => now(),
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | SAVE BUYER VERIFICATION SESSION
        |--------------------------------------------------------------------------
        */

        $request->session()->put(
            'buyer_verification_user_id',
            $user->id
        );


        /*
        |--------------------------------------------------------------------------
        | SEND OTP TO BUYER EMAIL
        |--------------------------------------------------------------------------
        */

        try {
            $user->notify(
                new BuyerVerificationCodeNotification($code)
            );
        } catch (\Throwable $e) {
            report($e);

            return redirect()
                ->route('buyer.verify-email.show')
                ->withErrors([
                    'code' => 'Your account was created, but we could not send the verification code. Please try resending the code.',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | REDIRECT TO EMAIL VERIFICATION PAGE
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route('buyer.verify-email.show')
            ->with(
                'status',
                'We sent a 6-digit verification code to your email.'
            );
    }
}
