<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Logistics\Rider;
use App\Models\Logistics\RiderEmailVerificationCode;
use App\Models\LogisticsPartner;
use App\Notifications\RiderVerificationCodeNotification;
use App\Services\RiderRegistrationDocuments;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Throwable;

class MobileRiderAuthController extends Controller
{
    public function logisticsCenters(): JsonResponse
    {
        $centers = LogisticsPartner::query()
            ->with('user:id,status')
            ->whereHas('user', fn ($query) => $query->where('status', 'approved'))
            ->orderBy('company_name')
            ->get()
            ->map(fn (LogisticsPartner $partner) => [
                'id' => $partner->id,
                'company_name' => $partner->company_name,
                'municipality' => $partner->municipality,
                'province' => $partner->province,
                'label' => collect([
                    $partner->company_name,
                    $partner->municipality,
                    $partner->province,
                ])->filter()->implode(' · '),
            ])
            ->values();

        return response()->json([
            'success' => true,
            'status' => 'ok',
            'message' => 'Approved Logistics / Sorting Centers loaded.',
            'data' => [
                'centers' => $centers,
            ],
        ]);
    }

    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'logistics_partner_id' => ['required', 'integer'],
            'first_name' => ['required', 'string', 'max:100', 'regex:/^[A-Za-zÀ-ÿñÑ\s\'.-]+$/'],
            'last_name' => ['required', 'string', 'max:100', 'regex:/^[A-Za-zÀ-ÿñÑ\s\'.-]+$/'],
            'middle_initial' => ['nullable', 'string', 'max:2'],
            'sex' => ['required', Rule::in(['Male', 'Female', 'Prefer not to say'])],
            'email' => [
                'required',
                'email',
                'max:191',
                Rule::unique('riders', 'email'),
                Rule::unique('users', 'email'),
            ],
            'contact_no' => ['required', 'regex:/^09\d{9}$/'],
            'birthday' => ['required', 'date', 'before:today'],
            'province_code' => ['required', 'string', 'max:20'],
            'province_name' => ['required', 'string', 'max:150'],
            'municipality_code' => ['required', 'string', 'max:20'],
            'municipality_name' => ['required', 'string', 'max:150'],
            'barangay_code' => ['required', 'string', 'max:20'],
            'barangay_name' => ['required', 'string', 'max:150'],
            'street_address' => ['required', 'string', 'max:500'],
            'vehicle_type' => ['required', 'string', 'max:80'],
            'plate_number' => ['required', 'string', 'max:30'],
            'or_cr' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'id_or_license' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'password' => [
                'required',
                'confirmed',
                Password::min(8)->mixedCase()->numbers(),
            ],
            'terms' => ['required', 'accepted'],
        ]);

        $partner = LogisticsPartner::query()
            ->whereKey($validated['logistics_partner_id'])
            ->whereHas('user', fn ($query) => $query->where('status', 'approved'))
            ->first();

        if (! $partner) {
            return response()->json([
                'success' => false,
                'status' => 'validation_error',
                'message' => 'Please choose an approved Logistics / Sorting Center.',
                'errors' => [
                    'logistics_partner_id' => ['The selected Logistics / Sorting Center is unavailable.'],
                ],
            ], 422);
        }

        $storedPaths = [];
        try {
            $rider = DB::transaction(function () use ($validated, $request, $partner, &$storedPaths) {
                $rider = Rider::create([
                    'logistics_partner_id' => $partner->id,
                    'coverage_area_id' => null,
                    'name' => trim($validated['first_name'].' '.$validated['last_name']),
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
                    'street_address' => trim($validated['street_address']),
                    'vehicle_type' => trim($validated['vehicle_type']),
                    'plate_number' => strtoupper(trim($validated['plate_number'])),
                    'status' => 'pending',
                    'availability_status' => 'offline',
                    'applied_at' => now(),
                    'terms_accepted_at' => now(),
                    'email' => strtolower(trim($validated['email'])),
                    'password' => $validated['password'],
                ]);

                $orCrPath = RiderRegistrationDocuments::store($rider, 'or_cr', $request->file('or_cr'));
                $storedPaths[] = $orCrPath;
                $idPath = RiderRegistrationDocuments::store($rider, 'id_or_license', $request->file('id_or_license'));
                $storedPaths[] = $idPath;

                $rider->forceFill([
                    'or_cr_path' => $orCrPath,
                    'id_or_license_path' => $idPath,
                ])->saveOrFail();

                return $rider;
            });
        } catch (Throwable $exception) {
            foreach ($storedPaths as $path) {
                Storage::disk(RiderRegistrationDocuments::DISK)->delete($path);
            }
            throw $exception;
        }

        $sendResult = $this->issueVerificationCode($rider);

        return response()->json([
            'success' => true,
            'status' => 'email_unverified',
            'message' => $sendResult
                ? 'We sent a 6-digit Rider verification code to your email.'
                : 'Your Rider application was created, but we could not send the verification code. Please try resending it.',
            'data' => [
                'email' => $rider->email,
                'account_type' => 'rider',
                'logistics_center' => $partner->company_name,
            ],
        ], 201);
    }

    public function verifyEmail(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'code' => ['required', 'digits:6'],
        ]);

        $rider = Rider::query()->where('email', strtolower(trim($validated['email'])))->first();

        if (! $rider) {
            return response()->json([
                'success' => false,
                'status' => 'not_found',
                'message' => 'Rider application was not found.',
            ], 404);
        }

        if ($rider->email_verified_at) {
            return response()->json([
                'success' => true,
                'status' => $rider->status,
                'message' => 'Your Rider email is already verified.',
                'data' => $this->statusData($rider),
            ]);
        }

        $verification = RiderEmailVerificationCode::query()
            ->where('rider_id', $rider->id)
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

        if (! Hash::check($validated['code'], $verification->code_hash)) {
            $verification->increment('attempts');

            return response()->json([
                'success' => false,
                'status' => 'email_unverified',
                'message' => 'The verification code is incorrect.',
            ], 422);
        }

        DB::transaction(function () use ($rider, $verification) {
            $rider->forceFill(['email_verified_at' => now()])->saveOrFail();
            $verification->delete();
        });

        return response()->json([
            'success' => true,
            'status' => 'pending',
            'message' => 'Email verified. Your Rider application is now waiting for Logistics / Sorting Center approval.',
            'data' => $this->statusData($rider->fresh(['partner'])),
        ]);
    }

    public function resendVerification(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $rider = Rider::query()
            ->with('partner')
            ->where('email', strtolower(trim($validated['email'])))
            ->first();

        if (! $rider) {
            return response()->json([
                'success' => false,
                'status' => 'not_found',
                'message' => 'Rider application was not found.',
            ], 404);
        }

        if ($rider->email_verified_at) {
            return response()->json([
                'success' => true,
                'status' => $rider->status,
                'message' => 'Your Rider email is already verified.',
                'data' => $this->statusData($rider),
            ]);
        }

        $existing = RiderEmailVerificationCode::query()->where('rider_id', $rider->id)->first();
        if ($existing?->last_sent_at?->gt(now()->subMinute())) {
            return response()->json([
                'success' => false,
                'status' => 'cooldown',
                'message' => 'Please wait at least 60 seconds before requesting another code.',
            ], 429);
        }

        if (! $this->issueVerificationCode($rider)) {
            return response()->json([
                'success' => false,
                'status' => 'mail_error',
                'message' => 'We could not send the verification code. Please try again.',
            ], 503);
        }

        return response()->json([
            'success' => true,
            'status' => 'email_unverified',
            'message' => 'A new Rider verification code was sent to your email.',
            'data' => $this->statusData($rider),
        ]);
    }

    private function issueVerificationCode(Rider $rider): bool
    {
        $code = (string) random_int(100000, 999999);

        RiderEmailVerificationCode::updateOrCreate(
            ['rider_id' => $rider->id],
            [
                'code_hash' => Hash::make($code),
                'attempts' => 0,
                'expires_at' => now()->addMinutes(10),
                'last_sent_at' => now(),
            ],
        );

        try {
            $rider->loadMissing('partner');
            $rider->notify(new RiderVerificationCodeNotification(
                $code,
                $rider->partner?->company_name ?? 'your Logistics / Sorting Center',
            ));

            return true;
        } catch (Throwable $exception) {
            report($exception);
            return false;
        }
    }

    private function statusData(Rider $rider): array
    {
        return [
            'email' => $rider->email,
            'account_type' => 'rider',
            'rejection_reason' => $rider->rejection_reason,
            'logistics_center' => $rider->partner?->company_name,
        ];
    }
}
