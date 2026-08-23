<?php

namespace App\Http\Controllers\Logistics;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class RegistrationController extends Controller
{
    public function create(): \Illuminate\View\View
    {
        return view('logistics.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'business_registration_no' => ['required', 'string', 'max:100'],
            'line_of_business' => ['required', 'in:motorcycle_courier,van_truck_freight,same_day,other'],
            'rep_last_name' => ['nullable', 'string', 'max:100'],
            'rep_first_name' => ['nullable', 'string', 'max:100'],
            'rep_sex' => ['required', 'in:male,female'],
            'rep_birthday' => ['required', 'date', 'before:-18 years'],
            'email' => ['required', 'email', 'max:255'],
            'contact_no' => ['required', 'string', 'max:20'],
            'province' => ['required', 'string', 'max:100'],
            'municipality' => ['required', 'string', 'max:100'],
            'barangay' => ['required', 'string', 'max:100'],
            'street_address' => ['nullable', 'string', 'max:255'],
            'coverage_areas' => ['required', 'array', 'min:1'],
            'business_permit' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'accreditation_docs' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'rep_valid_id' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        // TODO: store the uploaded files (e.g. Storage::disk('s3')->putFile(...))
        // and create the LogisticsPartner record with status = 'pending'.
        // Then notify the platform admin, the same way new Seller
        // applications are surfaced today.

        return redirect()
            ->route('logistics.register')
            ->with('status', "Application submitted! Please wait for the ShopHop administrator's approval, sent to your registered e-mail.");
    }

    public function terms(): \Illuminate\View\View
    {
        return view('logistics.terms');
    }

    public function acceptTerms(Request $request): RedirectResponse
    {
        $request->validate([
            'terms_agree'          => ['required', 'accepted'],
            'agreement_rep_name'   => ['required', 'string', 'max:255'],
            'agreement_date'       => ['required', 'date'],
            'agreement_signature'  => ['required', 'string', 'max:255'],
        ]);

        session([
            'terms_accepted'  => true,
            'terms_signed_at' => now(),
        ]);

        return redirect()
            ->route('logistics.register')
            ->with('status', 'Thanks — you may now proceed with your application.');
    }
}