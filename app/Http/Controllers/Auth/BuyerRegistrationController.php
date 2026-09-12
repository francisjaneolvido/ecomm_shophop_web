<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Buyer;
use App\Models\EmailVerificationCode;
use App\Models\User;
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

        $validated = $request->validate([
            'first_name' => [
                'required',
                'string',
                'max:100',
                'regex:/^[A-Za-zÀ-ÿñÑ\s\'.-]+$/',
            ],

            'last_name' => [
                'required',
                'string',
                'max:100',
                'regex:/^[A-Za-zÀ-ÿñÑ\s\'.-]+$/',
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
                'unique:users,email',
            ],

            'contact_no' => [
                'required',
                'regex:/^09\d{9}$/',
            ],

            'birthday' => [
                'required',
                'date',
                'before:today',
            ],

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
                    ->numbers(),
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
                'password' => Hash::make($validated['password']),
                'account_type' => 'buyer',
                'status' => 'pending',
            ]);


            /*
            |--------------------------------------------------------------------------
            | UPLOAD BUYER VALID ID
            |--------------------------------------------------------------------------
            */

            $validIdPath = $request
                ->file('valid_id')
                ->store('valid_ids/buyers', 'public');


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
                'expires_at' => now()->addMinutes(10),
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