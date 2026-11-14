<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Buyer;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class MobileBuyerAccountController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $buyer = $this->buyer($request);

        return response()->json(['success' => true, 'data' => [
            ...$buyer->only(['first_name', 'last_name', 'middle_initial', 'sex', 'contact_no']),
            'birthday' => $buyer->birthday?->toDateString(),
            'email' => $request->user()->email,
            'sex_options' => $this->sexOptions(),
        ]]);
    }

    public function update(Request $request): JsonResponse
    {
        $buyer = $this->buyer($request);
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:100', "regex:/^[\\p{L}\\p{M}\\s'.-]+$/u"],
            'last_name' => ['required', 'string', 'max:100', "regex:/^[\\p{L}\\p{M}\\s'.-]+$/u"],
            'middle_initial' => ['nullable', 'string', 'max:2'],
            'sex' => ['required', Rule::in($this->sexOptions())],
            'contact_no' => ['required', 'regex:/^09\d{9}$/'],
            'birthday' => ['required', 'date_format:Y-m-d', 'before:today'],
        ]);
        $buyer->update($data);

        return $this->show($request);
    }

    public function password(Request $request): JsonResponse
    {
        $this->buyer($request);
        $data = $request->validate([
            'current_password' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'max:72', 'confirmed', 'different:current_password',
                Password::min(8)->mixedCase()->numbers()->symbols(),
                function (string $attribute, mixed $value, \Closure $fail): void {
                    // Bcrypt accepts at most 72 bytes, including multibyte characters.
                    if (is_string($value) && strlen($value) > 72) {
                        $fail('Please use a shorter password.');
                    }
                }],
        ]);
        /** @var User $user */
        $user = $request->user();
        if (! Hash::check($data['current_password'], $user->password)) {
            throw ValidationException::withMessages(['current_password' => 'The current password is incorrect.']);
        }
        $user->forceFill(['password' => Hash::make($data['password']), 'remember_token' => null])->save();

        return response()->json(['success' => true, 'message' => 'Password changed.']);
    }

    private function sexOptions(): array
    {
        return DB::connection()->getDriverName() === 'sqlite'
            ? ['Male', 'Female'] : ['Male', 'Female', 'Prefer not to say'];
    }

    private function buyer(Request $request): Buyer
    {
        $user = $request->user();
        abort_unless($user instanceof User && $user->account_type === 'buyer', 403, 'Buyer account required.');
        abort_unless($user->status === 'approved' && $user->email_verified_at, 403, 'Approved and verified buyer required.');
        abort_unless($user->buyer, 404, 'Buyer profile not found.');

        return $user->buyer;
    }
}
