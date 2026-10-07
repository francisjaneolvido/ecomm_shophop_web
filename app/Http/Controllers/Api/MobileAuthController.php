<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Logistics\Rider;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class MobileAuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $email = strtolower(trim($validated['email']));
        $user = User::query()->where('email', $email)->first();

        if ($user && Hash::check($validated['password'], $user->password)) {
            return $this->loginMarketplaceUser($user);
        }

        // Riders remain partner-owned operational identities, so mobile login falls back to the Rider guard model.
        $rider = Rider::query()
            ->with(['partner.user', 'coverageArea.sortingCenter', 'sortingCenter'])
            ->where('email', $email)
            ->first();

        if ($rider && $rider->password && Hash::check($validated['password'], $rider->password)) {
            return $this->loginRider($rider);
        }

        return response()->json([
            'success' => false,
            'status' => 'invalid_credentials',
            'message' => 'Incorrect email or password.',
        ], 401);
    }

    public function me(Request $request): JsonResponse
    {
        $actor = $request->user();

        if ($actor instanceof Rider) {
            $rider = $actor->fresh(['partner.user', 'coverageArea.sortingCenter', 'sortingCenter']);
            if (! $rider) {
                return response()->json([
                    'success' => false,
                    'status' => 'unavailable',
                    'message' => 'Rider account is unavailable.',
                ], 401);
            }

            return response()->json([
                'success' => true,
                'status' => $this->riderAccessStatus($rider),
                'message' => 'Authenticated Rider account loaded.',
                'data' => $this->riderData($rider),
            ]);
        }

        /** @var User $user */
        $user = $actor;
        $status = $user->status;

        if (
            in_array($user->account_type, ['buyer', 'seller'], true)
            && is_null($user->email_verified_at)
        ) {
            $status = 'email_unverified';
        }

        return response()->json([
            'success' => true,
            'status' => $status,
            'message' => 'Authenticated account loaded.',
            'data' => $this->userData($user),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json([
            'success' => true,
            'status' => 'logged_out',
            'message' => 'You have been signed out.',
        ]);
    }

    private function loginMarketplaceUser(User $user): JsonResponse
    {
        $data = $this->userData($user);

        if (
            in_array($user->account_type, ['buyer', 'seller'], true)
            && is_null($user->email_verified_at)
        ) {
            return response()->json([
                'success' => true,
                'status' => 'email_unverified',
                'message' => 'Please complete your email verification before signing in.',
                'data' => $data,
            ]);
        }

        if ($user->account_type !== 'admin' && $user->status !== 'approved') {
            return $this->reviewResponse($user->status, $data, 'account');
        }

        $token = $user->createToken('shophop-mobile')->plainTextToken;

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

    private function loginRider(Rider $rider): JsonResponse
    {
        $data = $this->riderData($rider);

        if (is_null($rider->email_verified_at)) {
            return response()->json([
                'success' => true,
                'status' => 'email_unverified',
                'message' => 'Please verify your Rider email before signing in.',
                'data' => $data,
            ]);
        }

        if ($rider->status !== 'active') {
            return $this->reviewResponse($rider->status, $data, 'Rider application');
        }

        if ($rider->partner?->user?->status !== 'approved') {
            return response()->json([
                'success' => true,
                'status' => 'unavailable',
                'message' => 'Your Logistics / Sorting Center is currently unavailable.',
                'data' => $data,
            ]);
        }

        $token = $rider->createToken('shophop-mobile-rider')->plainTextToken;

        return response()->json([
            'success' => true,
            'status' => 'approved',
            'message' => 'Rider sign in successful.',
            'data' => [
                ...$data,
                'token' => $token,
                'token_type' => 'Bearer',
            ],
        ]);
    }

    private function reviewResponse(string $status, array $data, string $subject): JsonResponse
    {
        if ($status === 'pending') {
            return response()->json([
                'success' => true,
                'status' => 'pending',
                'message' => "Your {$subject} is still waiting for approval.",
                'data' => $data,
            ]);
        }

        if ($status === 'rejected') {
            return response()->json([
                'success' => true,
                'status' => 'rejected',
                'message' => "Your {$subject} was not approved.",
                'data' => $data,
            ]);
        }

        if ($status === 'suspended') {
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

    private function userData(User $user): array
    {
        return [
            'email' => $user->email,
            'account_type' => $user->account_type,
            'rejection_reason' => $user->rejection_reason,
        ];
    }

    private function riderData(Rider $rider): array
    {
        return [
            'id' => $rider->id,
            'name' => $rider->name,
            'email' => $rider->email,
            'account_type' => 'rider',
            'rejection_reason' => $rider->rejection_reason,
            // `logistics_center` is kept for backward compatibility with older mobile builds.
            'logistics_center' => $rider->partner?->company_name,
            'logistics_company' => $rider->partner?->company_name,
            'sorting_center_id' => $rider->sorting_center_id,
            'sorting_center_name' => $rider->sortingCenter?->name,
            'sorting_center_code' => $rider->sortingCenter?->code,
            'sorting_center_address' => $rider->sortingCenter?->addressLabel(),
            'sorting_center_is_main' => (bool) ($rider->sortingCenter?->is_main ?? false),
            'coverage_area' => $rider->coverageArea?->area_name,
            'coverage_area_type' => $rider->coverageArea?->area_type,
            'coverage_area_sorting_center' => $rider->coverageArea?->sortingCenter?->name,
            'availability_status' => $rider->availability_status,
        ];
    }

    private function riderAccessStatus(Rider $rider): string
    {
        if (! $rider->email_verified_at) {
            return 'email_unverified';
        }
        if ($rider->status !== 'active') {
            return $rider->status;
        }
        if ($rider->partner?->user?->status !== 'approved') {
            return 'unavailable';
        }
        return 'approved';
    }
}
