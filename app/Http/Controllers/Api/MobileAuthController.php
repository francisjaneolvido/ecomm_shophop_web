<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Logistics\BranchStaff;
use App\Models\Logistics\LinehaulDriver;
use App\Models\Logistics\Rider;
use App\Models\Logistics\SortingCenter;
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

        // Linehaul Drivers are provisioned by Logistics Fleet Management on web.
        $driver = LinehaulDriver::query()
            ->with(['partner.user'])
            ->where('email', $email)
            ->first();
        if ($driver && Hash::check($validated['password'], $driver->password)) {
            return $this->loginLinehaulDriver($driver);
        }

        // Company-provisioned branch operators have no public registration.
        $staff = BranchStaff::with(['partner.user', 'sortingCenter'])->where('email', $email)->first();
        if ($staff && Hash::check($validated['password'], $staff->password)) {
            if (! $staff->activeForBranch((int) $staff->logistics_partner_id, (int) $staff->sorting_center_id)) {
                return response()->json(['success' => true, 'status' => 'unavailable',
                    'message' => 'Your branch operator account or branch is inactive.',
                    'data' => ['account_type' => 'logistics_staff']], 403);
            }
            return response()->json(['success' => true, 'status' => 'approved',
                'message' => 'Branch scanner sign in successful.',
                'data' => [
                    'email' => $staff->email,
                    'account_type' => 'logistics_staff',
                    'company_name' => $staff->partner->company_name,
                    'staff_name' => $staff->name,
                    'staff_role' => $staff->role,
                    'sorting_center_id' => $staff->sorting_center_id,
                    'sorting_center_name' => $staff->sortingCenter->name,
                    'token' => $staff->createToken('shophop-branch-scanner')->plainTextToken,
                    'token_type' => 'Bearer',
                ],
            ]);
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

        if ($actor instanceof BranchStaff) {
            $staff = $actor->fresh(['partner.user', 'sortingCenter']);
            $active = $staff?->activeForBranch((int) $staff->logistics_partner_id, (int) $staff->sorting_center_id) ?? false;
            return response()->json(['success' => true, 'status' => $active ? 'approved' : 'unavailable',
                'message' => 'Authenticated branch operator.',
                'data' => ['account_type' => 'logistics_staff', 'email' => $staff?->email,
                    'sorting_center_id' => $staff?->sorting_center_id, 'staff_role' => $staff?->role]]);
        }
        if ($actor instanceof LinehaulDriver) {
            $driver = $actor->fresh(['partner.user']);
            $approved = $driver
                && $driver->status === 'active'
                && $driver->partner?->user?->status === 'approved';

            return response()->json([
                'success' => true,
                'status' => $approved ? 'approved' : 'unavailable',
                'message' => $approved ? 'Authenticated Linehaul Driver account loaded.' : 'Linehaul Driver account is unavailable.',
                'data' => $driver ? $this->linehaulDriverData($driver) : ['account_type' => 'linehaul_driver'],
            ], $approved ? 200 : 403);
        }
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

        if ($user->account_type === 'branch') {
            $center = $this->branchCenterForUser($user);
            $approved = $user->status === 'approved'
                && $center
                && $center->status === 'active'
                && ! $center->archived_at
                && $center->partner?->user?->status === 'approved';

            return response()->json([
                'success' => true,
                'status' => $approved ? 'approved' : 'unavailable',
                'message' => $approved
                    ? 'Authenticated station hub account loaded.'
                    : 'Station hub account is currently unavailable.',
                'data' => $this->branchUserData($user, $center),
            ]);
        }

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
        if ($user->account_type === 'branch') {
            return $this->loginBranchUser($user);
        }

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

    /**
     * Station-hub accounts are created by the web Logistics workflow/seeder
     * as normal User rows with account_type=branch. Mobile treats them as a
     * branch-scanner identity and locks the scanner to their assigned center.
     */
    private function loginBranchUser(User $user): JsonResponse
    {
        $center = $this->branchCenterForUser($user);

        if ($user->status !== 'approved' || ! $center || $center->status !== 'active' || $center->archived_at) {
            return response()->json([
                'success' => true,
                'status' => 'unavailable',
                'message' => 'Your station hub account or branch is currently inactive.',
                'data' => $this->branchUserData($user, $center),
            ], 403);
        }

        $center->loadMissing('partner.user');
        if ($center->partner?->user?->status !== 'approved') {
            return response()->json([
                'success' => true,
                'status' => 'unavailable',
                'message' => 'Your Logistics company is currently unavailable.',
                'data' => $this->branchUserData($user, $center),
            ], 403);
        }

        return response()->json([
            'success' => true,
            'status' => 'approved',
            'message' => 'Station hub scanner sign in successful.',
            'data' => [
                ...$this->branchUserData($user, $center),
                'token' => $user->createToken('shophop-station-scanner')->plainTextToken,
                'token_type' => 'Bearer',
            ],
        ]);
    }

    private function loginLinehaulDriver(LinehaulDriver $driver): JsonResponse
    {
        $data = $this->linehaulDriverData($driver);

        if ($driver->status !== 'active' || $driver->partner?->user?->status !== 'approved') {
            return response()->json([
                'success' => true,
                'status' => 'unavailable',
                'message' => 'Your Linehaul Driver account or Logistics company is currently inactive.',
                'data' => $data,
            ], 403);
        }

        return response()->json([
            'success' => true,
            'status' => 'approved',
            'message' => 'Linehaul Driver sign in successful.',
            'data' => [
                ...$data,
                'token' => $driver->createToken('shophop-mobile-linehaul')->plainTextToken,
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
        if ($user->account_type === 'branch') {
            return $this->branchUserData($user, $this->branchCenterForUser($user));
        }

        return [
            'email' => $user->email,
            'company_name' => $user->account_type === 'logistics' ? $user->logisticsPartner?->company_name : null,
            'account_type' => $user->account_type,
            'rejection_reason' => $user->rejection_reason,
        ];
    }

    private function branchCenterForUser(User $user): ?SortingCenter
    {
        if ($user->account_type !== 'branch') {
            return null;
        }

        return SortingCenter::query()
            ->with('partner.user')
            ->where('user_id', $user->id)
            ->whereNull('archived_at')
            ->first();
    }

    private function branchUserData(User $user, ?SortingCenter $center): array
    {
        return [
            'email' => $user->email,
            'account_type' => 'branch',
            'company_name' => $center?->partner?->company_name,
            'sorting_center_id' => $center?->id,
            'sorting_center_name' => $center?->name,
            'sorting_center_code' => $center?->code,
            'sorting_center_address' => $center?->addressLabel(),
            'rejection_reason' => $user->rejection_reason,
        ];
    }

    private function linehaulDriverData(LinehaulDriver $driver): array
    {
        return [
            'id' => $driver->id,
            'name' => $driver->name,
            'email' => $driver->email,
            'contact_no' => $driver->contact_no,
            'license_no' => $driver->license_no,
            'license_expiry' => optional($driver->license_expiry)?->toDateString(),
            'account_type' => 'linehaul_driver',
            'logistics_company' => $driver->partner?->company_name,
            'status' => $driver->status,
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
