<?php

namespace App\Http\Controllers\Logistics;

use App\Http\Controllers\Controller;
use App\Models\Logistics\Delivery;
use App\Models\Logistics\Rider;
use App\Models\Logistics\RiderTransferRequest;
use App\Models\Logistics\SortingCenter;
use App\Services\RiderManagementAccess;
use App\Services\RiderManagementService;
use App\Services\RiderRegistrationDocuments;
use App\Services\RiderWorkAreaDirectory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RiderManagementController extends Controller
{
    public function __construct(private RiderManagementAccess $access, private RiderManagementService $service,
        private RiderWorkAreaDirectory $directory) {}

    public function index(Request $request)
    {
        $scope = $this->access->request($request);
        abort_unless($this->service->available(), 503, 'Rider Management is awaiting the reviewed migration.');
        $request->validate([
            'q' => ['nullable', 'string', 'max:150'], 'branch' => ['nullable', 'integer', 'min:1'],
            'rider' => ['nullable', 'integer', 'min:1'], 'province' => ['nullable', 'string', 'max:150'],
            'barangay' => ['nullable', 'string', 'max:150'],
            'state' => ['nullable', 'in:pending_review,under_review,needs_documents,approved,rejected,pending_activation,active,inactive,suspended'],
        ]);
        $company = $scope['company_id'];
        $branches = SortingCenter::where('logistics_partner_id', $company)->where('type', 'station_hub')->where('is_main', false)
            ->where('status', 'active')->whereNull('archived_at')->orderBy('province')->orderBy('name')->get();
        $q = Rider::where('logistics_partner_id', $company);
        if ($scope['role'] === 'branch') {
            $q->where('sorting_center_id', $scope['branch_id']);
        }
        if ($request->filled('branch')) {
            $branch = $branches->find((int) $request->query('branch'));
            abort_unless($branch, 404);
            if ($scope['role'] === 'branch') {
                abort_unless((int) $branch->id === $scope['branch_id'], 404);
            }
            $q->where('sorting_center_id', $branch->id);
        }
        if ($request->filled('q')) {
            $term = mb_substr(trim($request->query('q')), 0, 150);
            $q->where(fn ($q) => $q->where('name', 'like', '%'.$term.'%')->orWhere('email', 'like', '%'.$term.'%'));
        }
        if ($request->filled('province')) {
            $q->whereHas('sortingCenter', fn ($q) => $q->where('province', $request->query('province')));
        }
        if ($request->filled('barangay')) {
            $q->whereHas('operationalServiceAreas', fn ($q) => $q->where('logistics_partner_id', $company)
                ->where('barangay', 'like', '%'.mb_substr($request->query('barangay'), 0, 150).'%'));
        }
        $state = $request->query('state');
        if (in_array($state, ['pending_review', 'under_review', 'needs_documents', 'approved', 'rejected'], true)) {
            $q->where(fn ($q) => $q->whereHas('lifecycleProfile', fn ($p) => $p->where('application_status', $state))
                ->orWhere(fn ($q) => $q->doesntHave('lifecycleProfile')->whereIn('status', match ($state) {
                    'pending_review' => ['pending'],'approved' => ['active', 'suspended'],'rejected' => ['rejected'],default => []
                })));
        } elseif (in_array($state, ['pending_activation', 'active', 'inactive', 'suspended'], true)) {
            $q->where(fn ($q) => $q->whereHas('lifecycleProfile', fn ($p) => $p->where('operational_status', $state))
                ->orWhere(fn ($q) => $q->doesntHave('lifecycleProfile')->whereIn('status', match ($state) {
                    'active' => ['active'],'suspended' => ['suspended'],'pending_activation' => ['pending'],default => []
                })));
            if ($state === 'pending_activation') {
                $q->whereHas('lifecycleProfile', fn ($p) => $p->where('application_status', 'approved'));
            }
        }
        $riders = $q->with(['lifecycleProfile.preferredBranch', 'sortingCenter', 'operationalServiceAreas'])
            ->orderBy('sorting_center_id')->orderByDesc('applied_at')->orderBy('name')->paginate(24)->withQueryString();
        if ($request->expectsJson()) {
            $prefix = $request->routeIs('logistics.staff.*') ? 'logistics.staff.rider-management.' : 'logistics.rider-management.';
            $active = Rider::where('logistics_partner_id', $company)->where('status', 'active')
                ->when($scope['role'] === 'branch', fn ($q) => $q->where('sorting_center_id', $scope['branch_id']))
                ->selectRaw('sorting_center_id, COUNT(*) as total')->groupBy('sorting_center_id')->pluck('total', 'sorting_center_id');

            return response()->json(['success' => true, 'data' => [
                'rows' => view('logistics.rider-management.rows', compact('riders', 'prefix'))->render(),
                'pagination' => (string) $riders->links(), 'active_by_branch' => $active, 'total' => $riders->total(),
            ]]);
        }
        $selected = null;
        $details = null;
        $coverage = [];
        $capacity = [];
        $performance = [];
        if ($request->filled('rider')) {
            $selected = Rider::where('logistics_partner_id', $company)
                ->when($scope['role'] === 'branch', fn ($q) => $q->where('sorting_center_id', $scope['branch_id']))
                ->findOrFail((int) $request->query('rider'));
            $details = $this->service->applicationData($selected);
            $bid = $selected->sorting_center_id ?: $selected->managementProfile()?->preferred_branch_id;
            $b = $branches->find($bid);
            if ($b) {
                $coverage = $this->directory->coverage($b);
                $capacity = $this->service->capacities($company, array_values($coverage));
            }
            $performance = Delivery::where('logistics_partner_id', $company)->where(fn ($q) => $q->where('rider_id', $selected->id)
                ->orWhere('pickup_rider_id', $selected->id)->orWhere('delivery_rider_id', $selected->id))
                ->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status')->all();
        }
        $transfers = RiderTransferRequest::with(['rider', 'fromBranch', 'toBranch'])->where('logistics_partner_id', $company)
            ->when($scope['role'] === 'branch', fn ($q) => $q->where(fn ($q) => $q->where('from_branch_id', $scope['branch_id'])->orWhere('to_branch_id', $scope['branch_id'])))
            ->latest('id')->paginate(12, ['*'], 'transfers_page')->withQueryString();
        $activeByBranch = Rider::where('logistics_partner_id', $company)->where('status', 'active')
            ->when($scope['role'] === 'branch', fn ($q) => $q->where('sorting_center_id', $scope['branch_id']))
            ->selectRaw('sorting_center_id, COUNT(*) as total')->groupBy('sorting_center_id')->pluck('total', 'sorting_center_id');
        $staff = $request->routeIs('logistics.staff.*');
        $prefix = $staff ? 'logistics.staff.rider-management.' : 'logistics.rider-management.';
        $center = $scope['center'];
        $layout = $staff ? 'logistics.rider-management.staff-layout' : ($scope['role'] === 'main' && ! $scope['center'] ? 'logistics.layouts.console' : 'branch.layout.app');

        return view('logistics.rider-management.index', compact('scope', 'branches', 'riders', 'selected', 'details', 'coverage', 'capacity',
            'performance', 'transfers', 'activeByBranch', 'prefix', 'layout', 'center'));
    }

    public function action(Request $request, int $rider)
    {
        $scope = $this->access->request($request);
        $data = $request->validate([
            'action' => ['required', 'string'], 'branch_id' => ['nullable', 'integer'], 'reason' => ['nullable', 'string', 'max:2000'],
            'documents' => ['nullable', 'array'], 'documents.*' => ['in:or_cr,id_or_license'],
            'coverage' => ['nullable', 'array'], 'coverage.*' => ['string', 'size:64', 'distinct'],
            'state' => ['nullable', 'string'], 'location_key' => ['nullable', 'string', 'size:64'],
            'capacity_action' => ['nullable', 'in:grant,revoke,limit'], 'rider_limit' => ['nullable', 'integer', 'min:0'],
            'starts_at' => ['nullable', 'date'], 'ends_at' => ['nullable', 'date'],
            'cancel_id' => ['nullable', 'integer'],
        ]);
        switch ($data['action']) {
            case 'under_review':case 'needs_documents':case 'approve':case 'reject':
                $this->service->review($scope, $rider, $data['action'], $data);
                break;
            case 'activate':$this->service->activate($scope, $rider, $data['coverage'] ?? []);
                break;
            case 'availability':$this->service->availability($scope, $rider, $data['state'] ?? '');
                break;
            case 'operational_status':$this->service->setOperationalStatus($scope, $rider, $data['state'] ?? '', $data['reason'] ?? '');
                break;
            case 'capacity':$this->service->setCapacity($scope, $rider, array_replace($data, ['action' => $data['capacity_action'] ?? '']));
                break;
            case 'schedule':
                foreach (['starts_at', 'ends_at'] as $time) {
                    if (! empty($data[$time])) {
                        $data[$time] = Carbon::parse($data[$time], 'Asia/Manila')->utc()->toIso8601String();
                    }
                }
                $this->service->schedule($scope, $rider, $data);
                break;
            case 'transfer':$this->service->requestTransfer($scope, $rider, (int) ($data['branch_id'] ?? 0), $data['reason'] ?? '');
                break;
            default:abort(422, 'Invalid Rider Management action.');
        }
        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'data' => ['rider' => $this->service->applicationData(
                Rider::where('logistics_partner_id', $scope['company_id'])->findOrFail($rider))]]);
        }

        return back()->with('status', 'Rider Management updated successfully.');
    }

    public function transferDecision(Request $request, int $transfer)
    {
        $scope = $this->access->request($request);
        $data = $request->validate(['decision' => ['required', 'in:approve,reject'], 'reason' => ['required', 'string', 'max:2000']]);
        $this->service->decideTransfer($scope, $transfer, $data['decision'] === 'approve', $data['reason']);

        return $request->expectsJson() ? response()->json(['success' => true]) : back()->with('status', 'Transfer decision recorded.');
    }

    public function document(Request $request, int $rider, string $slot)
    {
        $scope = $this->access->request($request);
        $r = Rider::where('logistics_partner_id', $scope['company_id'])
            ->when($scope['role'] === 'branch', fn ($q) => $q->where('sorting_center_id', $scope['branch_id']))->findOrFail($rider);
        $path = RiderRegistrationDocuments::scopedPath($r, $slot);
        $disk = Storage::disk(RiderRegistrationDocuments::DISK);
        abort_unless($path && $disk->exists($path), 404);

        return $disk->response($path, null, ['Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff',
            'Content-Security-Policy' => "sandbox; default-src 'none'"], 'inline');
    }
}
