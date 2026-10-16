<?php

namespace App\Http\Controllers\Logistics;

use App\Http\Controllers\Controller;
use App\Models\Logistics\BranchStaff;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class BranchStaffWorkspaceController extends Controller
{
    private function staff(Request $request): BranchStaff
    {
        $staff = Auth::guard('logistics_staff')->user();
        abort_unless($staff instanceof BranchStaff, 403);
        abort_unless($staff->activeForBranch((int) $staff->logistics_partner_id, (int) $staff->sorting_center_id),
            403, 'Branch access is suspended.');
        return $staff;
    }

    public function index(Request $request): View
    {
        $staff = $this->staff($request)->load('sortingCenter', 'partner');
        $recentScans = \Illuminate\Support\Facades\DB::table('parcel_scan_events')
            ->join('deliveries', 'deliveries.id', '=', 'parcel_scan_events.delivery_id')
            ->where('parcel_scan_events.sorting_center_id', $staff->sorting_center_id)
            ->select('parcel_scan_events.event_type', 'parcel_scan_events.scanned_at',
                'deliveries.tracking_code')->orderByDesc('parcel_scan_events.id')->limit(15)->get();
        return view('logistics.staff.workspace', compact('staff', 'recentScans'));
    }

    public function changePassword(Request $request): RedirectResponse
    {
        $staff = $this->staff($request);
        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
        ]);
        if (! Hash::check($data['current_password'], $staff->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }
        $staff->update(['password' => $data['password']]);
        $staff->tokens()->delete();
        return back()->with('status', 'Password changed. Sign in again on mobile with the new password.');
    }
}
