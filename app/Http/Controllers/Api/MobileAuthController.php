<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class MobileAuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => [
                'required',
                'email',
            ],
            'password' => [
                'required',
                'string',
            ],
        ]);

        $user = User::query()
            ->where('email', $validated['email'])
            ->first();

        if (
            ! $user ||
            ! Hash::check(
                $validated['password'],
                $user->password
            )
        ) {
            return response()->json([
                'success' => false,
                'status' => 'invalid_credentials',
                'message' => 'Incorrect email or password.',
            ], 401);
        }

        $data = [
            'email' => $user->email,
            'account_type' => $user->account_type,
            'rejection_reason' => $user->rejection_reason,
        ];

        if (
            in_array(
                $user->account_type,
                ['buyer', 'seller'],
                true
            ) &&
            is_null($user->email_verified_at)
        ) {
            return response()->json([
                'success' => true,
                'status' => 'email_unverified',
                'message' => 'Please complete your email verification before signing in.',
                'data' => $data,
            ]);
        }

        if (
            $user->account_type !== 'admin' &&
            $user->status !== 'approved'
        ) {
            if ($user->status === 'pending') {
                return response()->json([
                    'success' => true,
                    'status' => 'pending',
                    'message' => 'Your account is still waiting for approval.',
                    'data' => $data,
                ]);
            }

            if ($user->status === 'rejected') {
                return response()->json([
                    'success' => true,
                    'status' => 'rejected',
                    'message' => 'Your account application was not approved.',
                    'data' => $data,
                ]);
            }

            if ($user->status === 'suspended') {
                return response()->json([
                    'success' => true,
                    'status' => 'suspended',
                    'message' => 'Your ShopHop account is currently suspended.',
                    'data' => $data,
                ]);
            }

            return response()->json([
                'success' => true,
                'status' => 'unavailable',
                'message' => 'Your account is currently unavailable for sign in.',
                'data' => $data,
            ]);
        }

        $token = $user
            ->createToken('shophop-mobile')
            ->plainTextToken;

        return response()->json([
            'success' => true,
            'status' => 'approved',
            'message' => 'Sign in successful.',
            'data' => [
                ...$data,
                'token' => $token,
                'token_type' => 'Bearer',
            ],
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $status = $user->status;

        if (
            in_array(
                $user->account_type,
                ['buyer', 'seller'],
                true
            ) &&
            is_null($user->email_verified_at)
        ) {
            $status = 'email_unverified';
        }

        return response()->json([
            'success' => true,
            'status' => $status,
            'message' => 'Authenticated account loaded.',
            'data' => [
                'email' => $user->email,
                'account_type' => $user->account_type,
                'rejection_reason' => $user->rejection_reason,
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request
            ->user()
            ?->currentAccessToken()
            ?->delete();

        return response()->json([
            'success' => true,
            'status' => 'logged_out',
            'message' => 'You have been signed out.',
        ]);
    }
}
