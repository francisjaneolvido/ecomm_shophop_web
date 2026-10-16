<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MobileBuyerAddressController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $buyer = $this->buyer($request);

        return response()->json([
            'success' => true,
            'data' => $buyer->only([
                'region_code', 'region_name', 'province_code', 'province_name',
                'municipality_code', 'municipality_name', 'barangay_code',
                'barangay_name', 'street_address', 'contact_no',
            ]),
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $buyer = $this->buyer($request);
        $data = $request->validate([
            'region_code' => ['required', 'string', 'max:20'],
            'region_name' => ['required', 'string', 'max:150'],
            'province_code' => ['required', 'string', 'max:20'],
            'province_name' => ['required', 'string', 'max:150'],
            'municipality_code' => ['required', 'string', 'max:20'],
            'municipality_name' => ['required', 'string', 'max:150'],
            'barangay_code' => ['required', 'string', 'max:20'],
            'barangay_name' => ['required', 'string', 'max:150'],
            'street_address' => ['required', 'string', 'max:255'],
            'contact_no' => ['required', 'regex:/^09\d{9}$/'],
        ]);

        $data['street_address'] = trim($data['street_address']);
        $buyer->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Default delivery address updated. Already placed orders are unchanged.',
            'data' => $buyer->fresh()->only([
                'region_code', 'region_name', 'province_code', 'province_name',
                'municipality_code', 'municipality_name', 'barangay_code',
                'barangay_name', 'street_address', 'contact_no',
            ]),
        ]);
    }

    private function buyer(Request $request)
    {
        $user = $request->user();
        abort_unless($user && $user->account_type === 'buyer', 403, 'Buyer account required.');
        abort_unless($user->status === 'approved' && $user->email_verified_at, 403, 'Approved and verified buyer required.');
        abort_unless($user->buyer, 404, 'Buyer profile not found.');

        return $user->buyer;
    }
}
