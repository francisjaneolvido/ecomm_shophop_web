<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Buyer;
use App\Models\EmailVerificationCode;
use App\Models\User;
use App\Notifications\BuyerVerificationCodeNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class MobileBuyerAuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
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

        $user = DB::transaction(function () use ($validated, $request) {
            $user = User::create([
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'account_type' => 'buyer',
                'status' => 'pending',
            ]);

            $validIdPath = $request
                ->file('valid_id')
                ->store('valid_ids/buyers', 'public');

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

        $code = (string) random_int(100000, 999999);

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

        try {
            $user->notify(
                new BuyerVerificationCodeNotification($code)
            );

            return response()->json([
                'success' => true,
                'status' => 'email_unverified',
                'message' => 'We sent a 6-digit verification code to your email.',
                'data' => [
                    'email' => $user->email,
                    'account_type' => $user->account_type,
                ],
            ], 201);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => true,
                'status' => 'email_unverified',
                'message' => 'Your account was created, but we could not send the verification code. Please try resending the code.',
                'data' => [
                    'email' => $user->email,
                    'account_type' => $user->account_type,
                ],
            ], 201);
        }
    }

    public function verifyEmail(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => [
                'required',
                'email',
            ],
            'code' => [
                'required',
                'digits:6',
            ],
        ]);

        $user = User::query()
            ->where('email', $validated['email'])
            ->where('account_type', 'buyer')
            ->first();

        if (! $user) {
            return response()->json([
                'success' => false,
                'status' => 'not_found',
                'message' => 'Buyer account was not found.',
            ], 404);
        }

        if ($user->email_verified_at) {
            return response()->json([
                'success' => true,
                'status' => $user->status,
                'message' => 'Your email is already verified.',
                'data' => [
                    'email' => $user->email,
                    'account_type' => $user->account_type,
                ],
            ]);
        }

        $verification = EmailVerificationCode::query()
            ->where('user_id', $user->id)
            ->first();

        if (! $verification) {
            return response()->json([
                'success' => false,
                'status' => 'email_unverified',
                'message' => 'No verification code was found. Please request a new code.',
            ], 422);
        }

        if ($verification->expires_at->isPast()) {
            return response()->json([
                'success' => false,
                'status' => 'email_unverified',
                'message' => 'Your verification code has expired. Please request a new code.',
            ], 422);
        }

        if ($verification->attempts >= 5) {
            return response()->json([
                'success' => false,
                'status' => 'email_unverified',
                'message' => 'Too many incorrect attempts. Please request a new verification code.',
            ], 422);
        }

        if (! Hash::check(
            $validated['code'],
            $verification->code_hash
        )) {
            $verification->increment('attempts');

            return response()->json([
                'success' => false,
                'status' => 'email_unverified',
                'message' => 'The verification code is incorrect.',
            ], 422);
        }

        try {
            DB::transaction(function () use ($user, $verification) {
                $user->forceFill([
                    'email_verified_at' => now(),
                ]);

                $user->saveOrFail();
                $user->refresh();

                if (is_null($user->email_verified_at)) {
                    throw new \RuntimeException(
                        'Email verification timestamp was not saved.'
                    );
                }

                $verification->delete();
            });
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'status' => 'email_unverified',
                'message' => 'We could not complete your email verification. Please try again.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'status' => 'pending',
            'message' => 'Email verified successfully! Your registration is now waiting for administrator approval.',
            'data' => [
                'email' => $user->email,
                'account_type' => $user->account_type,
            ],
        ]);
    }

    public function resendVerification(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => [
                'required',
                'email',
            ],
        ]);

        $user = User::query()
            ->where('email', $validated['email'])
            ->where('account_type', 'buyer')
            ->first();

        if (! $user) {
            return response()->json([
                'success' => false,
                'status' => 'not_found',
                'message' => 'Buyer account was not found.',
            ], 404);
        }

        if ($user->email_verified_at) {
            return response()->json([
                'success' => true,
                'status' => $user->status,
                'message' => 'Your email is already verified.',
            ]);
        }

        $verification = EmailVerificationCode::query()
            ->where('user_id', $user->id)
            ->first();

        if (
            $verification &&
            $verification->last_sent_at &&
            $verification->last_sent_at->gt(now()->subMinute())
        ) {
            return response()->json([
                'success' => false,
                'status' => 'cooldown',
                'message' => 'Please wait at least 60 seconds before requesting another code.',
            ], 429);
        }

        $code = (string) random_int(100000, 999999);

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

        try {
            $user->notify(
                new BuyerVerificationCodeNotification($code)
            );
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'status' => 'email_unverified',
                'message' => 'We could not send a new verification code. Please try again later.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'status' => 'email_unverified',
            'message' => 'A new verification code has been sent to your email.',
        ]);
    }
}
