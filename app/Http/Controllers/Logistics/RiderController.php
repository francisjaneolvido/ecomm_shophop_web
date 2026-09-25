<?php

namespace App\Http\Controllers\Logistics;

use App\Http\Controllers\Controller;
use App\Models\Logistics\Rider;
use App\Models\LogisticsPartner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RiderController extends Controller
{
    public function index(Request $request): View
    {
        // Only the authenticated partner's persisted Riders appear; HR preview records are unavailable.
        $partner = $this->partner($request);
        $riders = Rider::where('logistics_partner_id', $partner->id)->orderBy('name')->get();

        return view('logistics.riders', compact('riders'));
    }

    public function store(Request $request): RedirectResponse
    {
        // The form supplies Rider details, while partner ownership comes only from the approved session.
        $partner = $this->partner($request);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'vehicle_type' => ['required', 'string', 'max:80'],
            // New Riders receive partner-provisioned credentials without a predictable default password.
            'email' => ['required', 'email', 'max:255', Rule::unique('riders', 'email')],
            'password' => ['required', 'string', 'min:12', 'confirmed'],
        ]);
        $data['password'] = Hash::make($data['password']);
        Rider::create($data + ['logistics_partner_id' => $partner->id, 'status' => 'active']);

        return redirect()->route('logistics.riders.index')->with('status', 'Rider added.');
    }

    public function provision(Request $request, int $rider): RedirectResponse
    {
        // Only the owning partner may turn a legacy Rider into an identity, and this action cannot rotate credentials.
        $partner = $this->partner($request);
        $ownedRider = Rider::where('logistics_partner_id', $partner->id)->findOrFail($rider);
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255', Rule::unique('riders', 'email')],
            'password' => ['required', 'string', 'min:12', 'confirmed'],
        ]);
        if (Rider::whereKey($ownedRider->id)->whereNull('email')->whereNull('password')
            ->update(['email' => $data['email'], 'password' => Hash::make($data['password'])]) !== 1) {
            return back()->withErrors(['rider' => 'Credentials already provisioned.']);
        }
        return back()->with('status', 'Rider credentials provisioned.');
    }

    public function suspend(Request $request, int $rider): RedirectResponse
    {
        return $this->setStatus($request, $rider, 'active', 'suspended');
    }

    public function activate(Request $request, int $rider): RedirectResponse
    {
        return $this->setStatus($request, $rider, 'suspended', 'active');
    }

    private function setStatus(Request $request, int $riderId, string $from, string $to): RedirectResponse
    {
        // A foreign Rider is invisible, and stale/repeated availability changes cannot silently succeed.
        $partner = $this->partner($request);
        $rider = Rider::where('logistics_partner_id', $partner->id)->findOrFail($riderId);
        if (Rider::whereKey($rider->id)->where('status', $from)->update(['status' => $to]) !== 1) {
            return back()->withErrors(['rider' => 'Rider status changed. Refresh and review it.']);
        }

        return back()->with('status', 'Rider status updated.');
    }

    private function partner(Request $request): LogisticsPartner
    {
        // An approved Logistics role alone does not prove a registered partner profile exists.
        return LogisticsPartner::where('user_id', $request->user()->id)->first() ?? abort(403, 'Logistics profile unavailable.');
    }
}
