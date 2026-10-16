<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EmailVerificationCode;
use App\Models\Seller;
use App\Models\User;
use App\Notifications\SellerVerificationCodeNotification;
use App\Services\RegistrationDocuments;
use App\Support\RegistrationIdentity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/** Mobile Seller registration shares the marketplace identity, Seller profile, OTP and Admin approval. */
class MobileSellerAuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $request->merge([
            'first_name' => trim((string) $request->input('first_name')),
            'last_name' => trim((string) $request->input('last_name')),
            'email' => RegistrationIdentity::email($request->input('email')),
            'contact_no' => RegistrationIdentity::mobile($request->input('contact_no')),
            'business_name' => trim((string) $request->input('business_name')),
        ]);

        $categories = collect(config('shophop_categories', []))->pluck('name')->all();
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'min:2', 'max:50', 'regex:/^[\p{L}][\p{L}\s\'.-]*$/u'],
            'last_name' => ['required', 'string', 'min:2', 'max:50', 'regex:/^[\p{L}][\p{L}\s\'.-]*$/u'],
            'middle_initial' => ['nullable', 'string', 'max:2'],
            'sex' => ['required', Rule::in(['Male', 'Female', 'Prefer not to say'])],
            'email' => ['required', 'email', 'max:191', RegistrationIdentity::emailUnique()],
            'contact_no' => ['required', 'regex:/^09\d{9}$/', RegistrationIdentity::mobileUnique()],
            'birthday' => ['required', 'date', 'before_or_equal:'.now()->subYears(18)->toDateString()],
            'region_code' => ['nullable', 'string', 'max:20'],
            'region_name' => ['nullable', 'string', 'max:150'],
            'province_code' => ['required', 'string', 'max:20'],
            'province_name' => ['required', 'string', 'max:150'],
            'municipality_code' => ['required', 'string', 'max:20'],
            'municipality_name' => ['required', 'string', 'max:150'],
            'barangay_code' => ['required', 'string', 'max:20'],
            'barangay_name' => ['required', 'string', 'max:150'],
            'street_address' => ['required', 'string'],
            'business_name' => [
                'required', 'string', 'min:3', 'max:30',
                RegistrationIdentity::companyUnique('sellers', 'business_name', 'This shop name is already registered.'),
            ],
            'business_category' => ['required', 'string', Rule::in($categories)],
            'valid_id' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'business_permit' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
            'terms' => ['required', 'accepted'],
        ]);

        // These records are shared with web Seller login, document review and Admin approval.
        $user = DB::transaction(function () use ($validated, $request): User {
            $user = User::create([
                'email' => $validated['email'],
                'registration_contact_no' => $validated['contact_no'],
                'password' => Hash::make($validated['password']),
                'account_type' => 'seller',
                'status' => 'pending',
            ]);

            $validIdPath = RegistrationDocuments::store($user, 'valid_id', $request->file('valid_id'));
            $permitPath = RegistrationDocuments::store($user, 'business_permit', $request->file('business_permit'));

            Seller::create([
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
                'business_name' => $validated['business_name'],
                'business_category' => $validated['business_category'],
                'valid_id_path' => $validIdPath,
                'business_permit_path' => $permitPath,
            ]);

            return $user;
        });

        $code = (string) random_int(100000, 999999);
        EmailVerificationCode::updateOrCreate(
            ['user_id' => $user->id],
            ['code_hash' => Hash::make($code), 'attempts' => 0,
                'expires_at' => now()->addMinutes(5), 'last_sent_at' => now()]
        );

        $sent = $this->sendCode($user, $code);

        return response()->json([
            'success' => true,
            'status' => 'email_unverified',
            'message' => $sent
                ? 'We sent a 6-digit verification code to your email.'
                : 'Your application was saved, but the code could not be sent. Please use Resend Code after the cooldown.',
            'data' => [
                'email' => $user->email,
                'account_type' => 'seller',
                'otp_delivery' => $sent ? 'sent' : 'failed',
            ],
        ], 201);
    }

    public function verifyEmail(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'code' => ['required', 'digits:6'],
        ]);

        $user = $this->sellerForEmail($validated['email']);
        if (! $user) {
            return $this->error('not_found', 'Seller account was not found.', 404);
        }

        if ($user->email_verified_at) {
            return $this->success($user, $user->status, 'Your email is already verified.');
        }

        $verification = EmailVerificationCode::where('user_id', $user->id)->first();
        if (! $verification) {
            return $this->error('email_unverified', 'No verification code was found. Please request a new code.');
        }
        if ($verification->expires_at->isPast()) {
            return $this->error('email_unverified', 'Your verification code has expired. Please request a new code.');
        }
        if ($verification->attempts >= 5) {
            return $this->error('email_unverified', 'Too many incorrect attempts. Please request a new code.');
        }
        if (! Hash::check((string) $validated['code'], $verification->code_hash)) {
            $verification->increment('attempts');
            return $this->error('email_unverified', 'The verification code is incorrect.');
        }

        DB::transaction(function () use ($user, $verification): void {
            $user->forceFill(['email_verified_at' => now()])->saveOrFail();
            $verification->delete();
        });

        // OTP is not equivalent to Admin approval. Seller remains pending.
        return $this->success(
            $user, 'pending',
            'Email verified. Your seller application is waiting for administrator approval.'
        );
    }

    public function resendVerification(Request $request): JsonResponse
    {
        $validated = $request->validate(['email' => ['required', 'email']]);
        $user = $this->sellerForEmail($validated['email']);
        if (! $user) {
            return $this->error('not_found', 'Seller account was not found.', 404);
        }
        if ($user->email_verified_at) {
            return $this->success($user, $user->status, 'Your email is already verified.');
        }

        $verification = EmailVerificationCode::where('user_id', $user->id)->first();
        if ($verification && $verification->last_sent_at && $verification->last_sent_at->gt(now()->subMinute())) {
            return $this->error('cooldown', 'Please wait at least 60 seconds before requesting another code.', 429);
        }

        $code = (string) random_int(100000, 999999);
        EmailVerificationCode::updateOrCreate(
            ['user_id' => $user->id],
            ['code_hash' => Hash::make($code), 'attempts' => 0,
                'expires_at' => now()->addMinutes(5), 'last_sent_at' => now()]
        );

        if (! $this->sendCode($user, $code)) {
            return $this->error('email_unverified', 'Could not send a new code. Please try again later.', 500);
        }

        return $this->success($user, 'email_unverified', 'A new code has been sent to your email.');
    }

    private function sellerForEmail(string $email): ?User
    {
        return User::where('email', RegistrationIdentity::email($email))
            ->where('account_type', 'seller')
            ->first();
    }

    private function sendCode(User $user, string $code): bool
    {
        try {
            $user->notify(new SellerVerificationCodeNotification($code));
            return true;
        } catch (\Throwable $e) {
            report($e);
            return false;
        }
    }

    private function success(User $user, string $status, string $message): JsonResponse
    {
        return response()->json([
            'success' => true,
            'status' => $status,
            'message' => $message,
            'data' => ['email' => $user->email, 'account_type' => 'seller'],
        ]);
    }

    private function error(string $status, string $message, int $httpStatus = 422): JsonResponse
    {
        return response()->json([
            'success' => false,
            'status' => $status,
            'message' => $message,
        ], $httpStatus);
    }
}
