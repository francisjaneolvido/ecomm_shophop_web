<?php

namespace App\Http\Controllers\Logistics;

use App\Http\Controllers\Controller;
use App\Models\LogisticsPartner;
use App\Models\User;
use App\Notifications\LogisticsVerificationCodeNotification;
// Logistics registration artifacts use the same private review contract as Buyer/Seller IDs.
use App\Services\RegistrationDocuments;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rules\Password;

class RegistrationController extends Controller
{
    public function create(): \Illuminate\View\View
    {
        return view('logistics.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('logisticsRegistration', [
            // Terms & Agreement
            'terms_agree' => ['required', 'accepted'],
            'agreement_rep_name' => ['required', 'string', 'max:150', 'regex:/^[^0-9]+$/'],
            'agreement_date' => ['required', 'date'],
            'agreement_signature' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],

            // Company Details
            'company_name' => ['required', 'string', 'max:255'],
            'business_registration_no' => ['required', 'string', 'max:100'],
            'line_of_business' => ['required', 'in:motorcycle_courier,van_truck_freight,same_day,other'],
            'rep_last_name' => ['required', 'string', 'max:100', 'regex:/^[^0-9]+$/'],
            'rep_first_name' => ['required', 'string', 'max:100', 'regex:/^[^0-9]+$/'],
            'rep_middle_initial' => ['nullable', 'string', 'max:5', 'regex:/^[A-Za-zÀ-ÖØ-öø-ÿÑñ. -]+$/'],
            'rep_valid_id' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'rep_id_number' => ['required', 'string', 'max:100', 'regex:/^[A-Za-z0-9\-]+$/'],
            'rep_sex' => ['required', 'in:male,female'],
            'rep_birthday' => ['required', 'date', 'before:-18 years'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'contact_no' => ['required', 'string', 'max:20'],
            'region' => ['required', 'string', 'max:150'],
            'province' => ['required', 'string', 'max:100'],
            'municipality' => ['required', 'string', 'max:100'],
            'barangay' => ['required', 'string', 'max:100'],
            'street_no' => ['required', 'string', 'max:150'],
            'unit_no' => ['required', 'string', 'max:150'],

            // Password
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],

            // Coverage & Documents
            'coverage_areas' => ['required', 'array', 'min:1'],
            'coverage_areas.*' => ['required', 'string', 'max:150'],
            'coverage_area_types' => ['required', 'array'],
            'coverage_area_types.*' => ['required', 'in:province,region'],
            'coverage_cities' => ['nullable', 'array'],
            'business_permit' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'accreditation_docs' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        $validated['email'] = strtolower(trim($validated['email']));

        $verification = $request->session()->get('logistics_email_verification');

        if (
            ! is_array($verification)
            || ! ($verification['verified'] ?? false)
            || ! isset($verification['email'])
            || strcasecmp((string) $verification['email'], $validated['email']) !== 0
        ) {
            return back()
                ->withInput($request->except(['password', 'password_confirmation']))
                ->withErrors([
                    'email' => 'Please verify this email address before submitting your Logistics / Sorting Center application.',
                ], 'logisticsRegistration');
        }

        DB::transaction(function () use ($validated, $request) {
            $user = User::create([
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'account_type' => 'logistics',
                'status' => 'pending',
            ]);

            $user->forceFill([
                'email_verified_at' => now(),
            ])->save();

            // Signature, ID, permit and optional accreditation must never become public assets.
            $signaturePath = RegistrationDocuments::store($user, 'agreement_signature', $request->file('agreement_signature'));
            $repValidIdPath = RegistrationDocuments::store($user, 'rep_valid_id', $request->file('rep_valid_id'));
            $businessPermitPath = RegistrationDocuments::store($user, 'business_permit', $request->file('business_permit'));
            $accreditationDocsPath = $request->hasFile('accreditation_docs')
                ? RegistrationDocuments::store($user, 'accreditation_docs', $request->file('accreditation_docs'))
                : null;

            $partner = LogisticsPartner::create([
                'user_id' => $user->id,
                'agreement_rep_name' => $validated['agreement_rep_name'],
                'agreement_date' => $validated['agreement_date'],
                'agreement_signature_path' => $signaturePath,
                'company_name' => $validated['company_name'],
                'business_registration_no' => $validated['business_registration_no'],
                'line_of_business' => $validated['line_of_business'],
                'rep_last_name' => $validated['rep_last_name'] ?? null,
                'rep_first_name' => $validated['rep_first_name'],
                'rep_middle_initial' => $validated['rep_middle_initial'] ?? null,
                'rep_valid_id_path' => $repValidIdPath,
                'rep_id_number' => $validated['rep_id_number'],
                'rep_sex' => $validated['rep_sex'],
                'rep_birthday' => $validated['rep_birthday'],
                'contact_no' => $validated['contact_no'],
                'region' => $validated['region'],
                'province' => $validated['province'],
                'municipality' => $validated['municipality'],
                'barangay' => $validated['barangay'],
                'street_no' => $validated['street_no'],
                'unit_no' => $validated['unit_no'],
                'business_permit_path' => $businessPermitPath,
                'accreditation_docs_path' => $accreditationDocsPath,
            ]);

            // The address entered during application becomes the company's primary sorting-center branch.
            // Additional branches are managed after administrator approval in the Logistics portal.
            $mainCenter = $partner->sortingCenters()->create([
                'code' => 'SC-'.str_pad((string) $partner->id, 4, '0', STR_PAD_LEFT).'-MAIN',
                'name' => $partner->company_name.' - Main Sorting Center',
                'is_main' => true,
                'status' => 'active',
                'contact_no' => $partner->contact_no,
                'region' => $partner->region,
                'province' => $partner->province,
                'municipality' => $partner->municipality,
                'barangay' => $partner->barangay,
                'street_no' => $partner->street_no,
                'unit_no' => $partner->unit_no,
            ]);

            $coverageCities = $validated['coverage_cities'] ?? [];
            $coverageAreaTypes = $validated['coverage_area_types'];

            foreach ($validated['coverage_areas'] as $areaName) {
                $partner->coverageAreas()->create([
                    'sorting_center_id' => $mainCenter->id,
                    'area_name' => $areaName,
                    'area_type' => $coverageAreaTypes[$areaName] ?? 'province',
                    'cities' => $coverageCities[$areaName] ?? null,
                ]);
            }
        });

        $request->session()->forget('logistics_email_verification');

        return redirect()
            ->route('home')
            ->with('status', "Application submitted! Please wait for the ShopHop administrator's approval, sent to your registered e-mail.");
    }

    public function sendVerificationCode(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'resend' => ['sometimes', 'boolean'],
        ]);

        $email = strtolower(trim($validated['email']));
        $isResend = (bool) ($validated['resend'] ?? false);
        $state = $request->session()->get('logistics_email_verification');

        if (
            is_array($state)
            && isset($state['email'], $state['last_sent_at'])
            && strcasecmp((string) $state['email'], $email) === 0
        ) {
            $elapsed = max(0, now()->timestamp - (int) $state['last_sent_at']);
            $remaining = max(0, 30 - $elapsed);

            if ($remaining > 0) {
                if ($isResend) {
                    return response()->json([
                        'success' => false,
                        'message' => "Please wait {$remaining} seconds before requesting another code.",
                        'retry_after' => $remaining,
                    ], 429);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'A verification code was already sent to this email.',
                    'retry_after' => $remaining,
                ]);
            }
        }

        $code = (string) random_int(100000, 999999);

        $request->session()->put('logistics_email_verification', [
            'email' => $email,
            'code_hash' => Hash::make($code),
            'attempts' => 0,
            'expires_at' => now()->addMinutes(10)->timestamp,
            'last_sent_at' => now()->timestamp,
            'verified' => false,
        ]);

        try {
            Notification::route('mail', $email)
                ->notify(new LogisticsVerificationCodeNotification($code));
        } catch (\Throwable $e) {
            report($e);
            Log::error('Logistics verification email failed.', [
                'email' => $email,
                'exception' => $e::class,
                'message' => $e->getMessage(),
            ]);
            $request->session()->forget('logistics_email_verification');

            return response()->json([
                'success' => false,
                'message' => $this->mailFailureMessage($e),
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'A 6-digit verification code was sent to your email.',
            'retry_after' => 30,
        ]);
    }

    public function verifyVerificationCode(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'code' => ['required', 'digits:6'],
        ]);

        $email = strtolower(trim($validated['email']));
        $state = $request->session()->get('logistics_email_verification');

        if (
            ! is_array($state)
            || ! isset($state['email'], $state['code_hash'], $state['expires_at'])
            || strcasecmp((string) $state['email'], $email) !== 0
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Please request a new verification code for this email.',
            ], 422);
        }

        if ($state['verified'] ?? false) {
            return response()->json([
                'success' => true,
                'message' => 'Email already verified.',
            ]);
        }

        if ((int) $state['expires_at'] < now()->timestamp) {
            $request->session()->forget('logistics_email_verification');

            return response()->json([
                'success' => false,
                'message' => 'The verification code has expired. Please request a new code.',
            ], 422);
        }

        $attempts = (int) ($state['attempts'] ?? 0);

        if ($attempts >= 5) {
            return response()->json([
                'success' => false,
                'message' => 'Too many incorrect attempts. Please request a new verification code.',
            ], 429);
        }

        if (! Hash::check($validated['code'], (string) $state['code_hash'])) {
            $state['attempts'] = $attempts + 1;
            $request->session()->put('logistics_email_verification', $state);

            return response()->json([
                'success' => false,
                'message' => 'The verification code is incorrect.',
            ], 422);
        }

        $state['verified'] = true;
        $state['verified_at'] = now()->timestamp;
        $request->session()->put('logistics_email_verification', $state);

        return response()->json([
            'success' => true,
            'message' => 'Email verified successfully.',
        ]);
    }

    private function mailFailureMessage(\Throwable $e): string
    {
        $message = strtolower($e->getMessage());

        if (str_contains($message, '535') || str_contains($message, 'username and password not accepted') || str_contains($message, 'authentication')) {
            return 'Gmail rejected the SMTP credentials. Create a fresh Google App Password, update MAIL_PASSWORD, then run php artisan optimize:clear.';
        }

        if (str_contains($message, 'timed out') || str_contains($message, 'could not connect') || str_contains($message, 'connection refused')) {
            return 'The server could not connect to Gmail SMTP. Check internet/firewall access and confirm smtp.gmail.com:587 is reachable.';
        }

        return 'We could not send the verification code. Check storage/logs/laravel.log for the SMTP error, then try again.';
    }

    public function terms(): \Illuminate\View\View
    {
        return view('logistics.terms');
    }
}
