<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class SellerAccountService
{
    public function sexOptions(): array
    {
        return DB::connection()->getDriverName() === 'sqlite'
            ? ['Male', 'Female'] : ['Male', 'Female', 'Prefer not to say'];
    }

    public function updateProfile(Request $request): void
    {
        $seller = $request->user()->seller;
        abort_unless($seller, 404);
        $seller->update($request->validate([
            'first_name' => ['required', 'string', 'max:100', "regex:/^[\\p{L}\\p{M}\\s'.-]+$/u"],
            'last_name' => ['required', 'string', 'max:100', "regex:/^[\\p{L}\\p{M}\\s'.-]+$/u"],
            'middle_initial' => ['nullable', 'string', 'max:2'],
            'sex' => ['required', Rule::in($this->sexOptions())],
            'contact_no' => ['required', 'regex:/^09\d{9}$/'],
            'birthday' => ['required', 'date', 'before:today'],
        ]));
    }

    public function changePassword(Request $request): void
    {
        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'confirmed', 'different:current_password', Password::min(10)->mixedCase()->numbers()],
        ]);
        /** @var User $user */
        $user = $request->user();
        if (! Hash::check($data['current_password'], $user->password)) {
            throw ValidationException::withMessages(['current_password' => 'The current password is incorrect.']);
        }
        $user->forceFill(['password' => Hash::make($data['password']), 'remember_token' => null])->save();
        if ($request->hasSession()) $request->session()->regenerate();
    }
}
