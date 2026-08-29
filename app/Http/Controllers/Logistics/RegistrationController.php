<?php

namespace App\Http\Controllers\Logistics;

use App\Http\Controllers\Controller;
use App\Models\LogisticsPartner;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class RegistrationController extends Controller
{
    public function create(): \Illuminate\View\View
    {
        return view('logistics.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            // Terms & Agreement
            'terms_agree' => ['required', 'accepted'],
            'agreement_rep_name' => ['required', 'string', 'max:150', 'regex:/^[^0-9]+$/'],
            'agreement_date' => ['required', 'date'],
            'agreement_signature' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],

            // Company Details
            'company_name' => ['required', 'string', 'max:255'],
            'business_registration_no' => ['required', 'string', 'max:100'],
            'line_of_business' => ['required', 'in:motorcycle_courier,van_truck_freight,same_day,other'],
            'rep_last_name' => ['nullable', 'string', 'max:100'],
            'rep_first_name' => ['nullable', 'string', 'max:100'],
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

            // OTP (front-end only for now, still required so it can't be skipped)
            'otp_code' => ['required', 'digits:6'],

            // Password
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],

            // Coverage & Documents
            'coverage_areas' => ['required', 'array', 'min:1'],
            'coverage_areas.*' => ['required', 'string', 'max:150'],
            'coverage_cities' => ['nullable', 'array'],
            'business_permit' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'accreditation_docs' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        DB::transaction(function () use ($validated, $request) {
            $user = User::create([
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'account_type' => 'logistics',
                'status' => 'pending',
            ]);

            $signaturePath = $request->file('agreement_signature')->store('signatures', 'public');
            $repValidIdPath = $request->file('rep_valid_id')->store('valid_ids/logistics', 'public');
            $businessPermitPath = $request->file('business_permit')->store('business_permits/logistics', 'public');
            $accreditationDocsPath = $request->hasFile('accreditation_docs')
                ? $request->file('accreditation_docs')->store('accreditation_docs', 'public')
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
                'rep_first_name' => $validated['rep_first_name'] ?? null,
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

            $coverageCities = $validated['coverage_cities'] ?? [];

            foreach ($validated['coverage_areas'] as $areaName) {
                $partner->coverageAreas()->create([
                    'area_name' => $areaName,
                    'area_type' => 'province',
                    'cities' => $coverageCities[$areaName] ?? null,
                ]);
            }
        });

        return redirect()
            ->route('home')
            ->with('status', "Application submitted! Please wait for the ShopHop administrator's approval, sent to your registered e-mail.");
    }

    public function terms(): \Illuminate\View\View
    {
        return view('logistics.terms');
    }
}