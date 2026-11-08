<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Logistics\Rider;
use App\Services\RiderManagementService;
use App\Services\RiderRegistrationDocuments;
use App\Services\RiderWorkAreaDirectory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\PersonalAccessToken;

class MobileRiderManagementController extends Controller
{
    public function __construct(private RiderManagementService $service, private RiderWorkAreaDirectory $directory) {}

    public function options(Request $request)
    {
        abort_unless($this->service->available(), 503, 'Rider Management is awaiting the reviewed migration.');
        $data = $request->validate(['company_id' => ['nullable', 'integer', 'min:1', 'required_with:branch_id'], 'branch_id' => ['nullable', 'integer', 'min:1']]);
        // company_id alone is valid for loading its Branch list.
        $options = $this->directory->options($request->integer('company_id') ?: null, $request->integer('branch_id') ?: null);
        $capacities = $this->service->capacities($request->integer('company_id'), $options['coverage']);
        foreach ($options['coverage'] as &$place) {
            $cap = $capacities[RiderWorkAreaDirectory::key($place)];
            $place['active_count'] = $cap['active_count'];
            $place['limit'] = $cap['limit'];
        }
        unset($place);

        return response()->json(['success' => true, 'data' => $options]);
    }

    private function rider(Request $request): Rider
    {
        abort_unless($this->service->available(), 503, 'Rider Management is awaiting the reviewed migration.');
        $actor = $request->user();
        abort_unless($actor instanceof Rider, 403);
        abort_unless($actor->tokenCan('rider:application') || $actor->tokenCan('*'), 403);
        $r = $actor->fresh(['partner.user']);
        abort_unless($r && $r->email_verified_at && $r->partner?->user?->status === 'approved', 403);

        return $r;
    }

    public function show(Request $request)
    {
        $r = $this->rider($request);
        $data = $this->service->applicationData($r);
        $data['has_operational_session'] = $request->user()->tokenCan('rider:operations') || $request->user()->tokenCan('*');
        if ($data['assignment_confirmation_required'] && $r->sorting_center_id) {
            $branch = $this->directory->branch((int) $r->logistics_partner_id, (int) $r->sorting_center_id);
            $data['branch_coverage_options'] = array_values($this->directory->coverage($branch));
        }

        return response()->json(['success' => true, 'status' => $r->canOperate() ? 'approved' : 'rider_application', 'data' => $data]);
    }

    public function confirmBranch(Request $request)
    {
        $r = $this->rider($request);
        $v = $request->validate(['branch_id' => ['required', 'integer'], 'coverage' => ['required', 'array', 'min:1'],
            'coverage.*' => ['required', 'string', 'size:64', 'distinct']]);
        $this->service->confirmBranch($r, $v['branch_id'], $v['coverage']);

        return $this->show($request);
    }

    public function documents(Request $request)
    {
        $r = $this->rider($request);
        $v = $request->validate(['or_cr' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'id_or_license' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120']]);
        $stored = [];
        try {
            foreach (['or_cr', 'id_or_license'] as $slot) {
                if ($request->hasFile($slot)) {
                    $stored[$slot] = RiderRegistrationDocuments::store($r, $slot, $request->file($slot));
                }
            }
            $this->service->resubmitDocuments($r, $stored);
        } catch (\Throwable $e) {
            foreach ($stored as $path) {
                Storage::disk(RiderRegistrationDocuments::DISK)->delete($path);
            }
            throw $e;
        }

        return $this->show($request);
    }

    public function session(Request $request)
    {
        $r = $this->rider($request);
        $data = DB::transaction(function () use ($r, $request) {
            $locked = Rider::lockForUpdate()->findOrFail($r->id);
            abort_unless($locked->canOperate(), 403, 'Branch activation is required.');
            $token = $locked->createToken('shophop-mobile-rider', ['rider:operations', 'rider:application'])->plainTextToken;
            $old = $request->user()->currentAccessToken();
            if ($old instanceof PersonalAccessToken) {
                $old->delete();
            }

            return ['token' => $token, 'token_type' => 'Bearer', 'email' => $locked->email, 'account_type' => 'rider'];
        });

        return response()->json(['success' => true, 'status' => 'approved', 'data' => $data]);
    }

    public function availability(Request $request)
    {
        $r = $this->rider($request);
        $v = $request->validate(['state' => ['required', 'in:online,offline,on_break']]);
        $this->service->availability(['company_id' => (int) $r->logistics_partner_id, 'actor_type' => 'rider', 'actor_id' => (int) $r->id],
            $r->id, $v['state']);

        return $this->show($request);
    }
}
