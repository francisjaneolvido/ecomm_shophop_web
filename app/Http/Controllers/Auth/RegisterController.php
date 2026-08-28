<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    /**
     * Show the ShopHop registration page.
     */
    public function create()
    {
        return view('auth.modals.account-type-modal');
    }

    /**
     * Store a newly registered customer.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'last_name' => ['required', 'string', 'max:100'],
            'first_name' => ['required', 'string', 'max:100'],
            'middle_initial' => ['nullable', 'string', 'max:2'],

            'sex' => ['required', 'in:Male,Female'],

            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email',
            ],

            'contact_no' => [
                'required',
                'regex:/^09\d{9}$/',
                'unique:users,contact_no',
            ],

            'birthday' => [
                'required',
                'date',
                'before_or_equal:today',
            ],

            'province_code' => ['required', 'string', 'max:20'],
            'province_name' => ['required', 'string', 'max:150'],

            'municipality_code' => ['required', 'string', 'max:20'],
            'municipality_name' => ['required', 'string', 'max:150'],

            'barangay_code' => ['required', 'string', 'max:20'],
            'barangay_name' => ['required', 'string', 'max:150'],

            'street_address' => ['required', 'string', 'max:500'],

            'valid_id' => [
                'required',
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'max:5120',
            ],

            'password' => [
                'required',
                'confirmed',
                Password::min(8),
            ],
        ], [
            'contact_no.regex' =>
                'The contact number must be an 11-digit Philippine mobile number starting with 09.',

            'valid_id.max' =>
                'The uploaded valid ID must not be larger than 5 MB.',
        ]);

        /*
        |--------------------------------------------------------------------------
        | AGE
        |--------------------------------------------------------------------------
        | Do not trust an age value from the browser.
        | Calculate it again on the server using the submitted birthday.
        |--------------------------------------------------------------------------
        */
        $birthday = Carbon::parse($validated['birthday']);
        $age = $birthday->age;

        /*
        |--------------------------------------------------------------------------
        | VALID ID UPLOAD
        |--------------------------------------------------------------------------
        | Files are stored under:
        | storage/app/public/valid_ids
        |
        | Run:
        | php artisan storage:link
        |--------------------------------------------------------------------------
        */
        $validIdPath = $request
            ->file('valid_id')
            ->store('valid_ids', 'public');

        /*
        |--------------------------------------------------------------------------
        | CREATE USER
        |--------------------------------------------------------------------------
        | The account starts as "pending" so an administrator can approve it.
        |--------------------------------------------------------------------------
        */
        User::create([
            'last_name' => $validated['last_name'],
            'first_name' => $validated['first_name'],
            'middle_initial' => $validated['middle_initial'] ?? null,

            'sex' => $validated['sex'],

            'email' => $validated['email'],
            'contact_no' => $validated['contact_no'],

            'birthday' => $birthday->format('Y-m-d'),
            'age' => $age,

            'province_code' => $validated['province_code'],
            'province_name' => $validated['province_name'],

            'municipality_code' => $validated['municipality_code'],
            'municipality_name' => $validated['municipality_name'],

            'barangay_code' => $validated['barangay_code'],
            'barangay_name' => $validated['barangay_name'],

            'street_address' => $validated['street_address'],

            'valid_id_path' => $validIdPath,

            'status' => 'pending',

            'password' => Hash::make($validated['password']),
        ]);

        /*
        |--------------------------------------------------------------------------
        | EMAIL
        |--------------------------------------------------------------------------
        | Later, send a "registration received" email here.
        | The approval/rejection email should be sent when an admin changes
        | the user's status.
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route('register')
            ->with(
                'success',
                'Registration submitted successfully. Please wait for the administrator\'s approval. The approval status will be sent to your email.'
            );
    }
}