<?php

namespace App\Http\Controllers\Logistics;

use App\Http\Controllers\Controller;
use App\Models\Logistics\SortingCenter;
use App\Models\Logistics\ParcelTransfer;
use App\Models\Logistics\Delivery;
use App\Services\BranchLifecycleService;
use Illuminate\Support\Str;
use App\Models\LogisticsPartner;
use App\Services\PhilippineDeliveryCoverage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SortingCenterController extends Controller
{
    public function index(Request $request): View
    {
        $partner = $this->partner($request);
        $centers = $partner->sortingCenters()->whereNull('archived_at')
            ->with(['coverageAreas', 'riders', 'staff'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $term = trim((string) $request->query('search'));
                $query->where(function ($q) use ($term) {
                    $q->where('name', 'like', "%{$term}%")
                        ->orWhere('code', 'like', "%{$term}%")
                        ->orWhere('municipality', 'like', "%{$term}%")
                        ->orWhere('province', 'like', "%{$term}%");
                });
            })
            ->when(in_array($request->query('status'), ['active', 'inactive'], true),
                fn ($query) => $query->where('status', $request->query('status')))
            ->orderByDesc('is_main')->orderBy('name')->get();
        $totals = [
            'all' => $partner->sortingCenters()->whereNull('archived_at')->count(),
            'active' => $partner->sortingCenters()->whereNull('archived_at')->where('status', 'active')->count(),
            'needs_setup' => $partner->sortingCenters()->whereNull('archived_at')->where('is_main', false)
                ->where('status', 'inactive')->count(),
            'archived' => $partner->sortingCenters()->whereNotNull('archived_at')->count(),
        ];
        return view('logistics.sorting-centers.index', compact('partner', 'centers', 'totals'));
    }

    public function create(Request $request): View
    {
        $partner = $this->partner($request);
        return view('logistics.sorting-centers.form', ['partner' => $partner, 'center' => null]);
    }

    public function show(Request $request, int $center): View
    {
        $partner = $this->partner($request);
        $center = $partner->sortingCenters()->with(['coverageAreas', 'staff', 'riders'])->whereNull('archived_at')->findOrFail($center);
        $openParcels = Delivery::where('logistics_partner_id', $partner->id)
            ->whereNotIn('status', [Delivery::DELIVERED, Delivery::RETURNED])
            ->where(function ($query) use ($center) {
                $query->where('origin_sorting_center_id', $center->id)
                    ->orWhere('current_sorting_center_id', $center->id)
                    ->orWhere('destination_sorting_center_id', $center->id);
            })->count();
        $inTransit = ParcelTransfer::where('status', ParcelTransfer::IN_TRANSIT)
            ->where(function ($query) use ($center) {
                $query->where('from_sorting_center_id', $center->id)
                    ->orWhere('to_sorting_center_id', $center->id);
            })->count();
        return view('logistics.sorting-centers.show', compact('partner', 'center', 'openParcels', 'inTransit'));
    }

    public function edit(Request $request, int $center): View
    {
        $partner = $this->partner($request);
        $center = $partner->sortingCenters()->with('coverageAreas')->whereNull('archived_at')->findOrFail($center);
        return view('logistics.sorting-centers.form', compact('partner', 'center'));
    }

    public function store(Request $request): RedirectResponse
    {
        $partner = $this->partner($request);
        $data = $this->validatedCenter($request);

        $credentials = null;
        $createdCenter = null;
        DB::transaction(function () use ($partner, $data, &$credentials, &$createdCenter) {
            $center = $partner->sortingCenters()->create([
                ...$this->centerPayload($data),
                'code' => $this->nextCode($partner),
                'is_main' => false,
                'status' => 'inactive', // Draft until manager, coverage and assignments are ready.
            ]);

            $createdCenter = $center;
            $this->syncCoverage($partner, $center, $data['coverage']);
            if (filled($data['manager_name'] ?? null) && filled($data['manager_email'] ?? null)) {
                $email = strtolower(trim($data['manager_email']));
                BranchStaffController::assertNewEmail($email);
                $password = Str::password(18);
                $center->staff()->create([
                    'logistics_partner_id' => $partner->id,
                    'name' => trim($data['manager_name']),
                    'email' => $email, 'password' => $password,
                    'role' => 'manager', 'status' => 'active',
                ]);
                $credentials = ['email' => $email, 'password' => $password];
            }
        });

        return redirect()->route('logistics.sorting-centers.show', $createdCenter)
            ->with('status', 'Branch draft saved. Check the setup checklist, then activate.')
            ->with('new_staff_credentials', $credentials);
    }

    public function update(Request $request, int $center): RedirectResponse
    {
        $partner = $this->partner($request);
        $ownedCenter = $partner->sortingCenters()->whereNull('archived_at')->findOrFail($center);
        $data = $this->validatedCenter($request);

        DB::transaction(function () use ($partner, $ownedCenter, $data) {
            $ownedCenter->update($this->centerPayload($data));
            $this->syncCoverage($partner, $ownedCenter, $data['coverage']);
            if ($ownedCenter->status === 'active') $this->assertNoOverlappingCoverage($partner, $ownedCenter);
        });

        return redirect()->route('logistics.sorting-centers.show', $ownedCenter)
            ->with('status', 'Branch details and coverage saved.');
    }

    public function toggle(Request $request, int $center): RedirectResponse
    {
        $partner = $this->partner($request);
        $ownedCenter = $partner->sortingCenters()->whereNull('archived_at')->findOrFail($center);

        if ($ownedCenter->is_main) {
            return back()->withErrors(['sorting_center' => 'The Main Sorting Center must remain active.']);
        }

        $next = $ownedCenter->status === 'active' ? 'inactive' : 'active';
        if ($next === 'inactive') {
            $hasParcels = Delivery::where('logistics_partner_id', $partner->id)
                ->whereNotIn('status', [Delivery::DELIVERED, Delivery::RETURNED])
                ->where(function ($query) use ($ownedCenter) {
                    $query->where('origin_sorting_center_id', $ownedCenter->id)
                        ->orWhere('current_sorting_center_id', $ownedCenter->id)
                        ->orWhere('destination_sorting_center_id', $ownedCenter->id);
                })->exists();
            $hasTransfers = ParcelTransfer::where('status', ParcelTransfer::IN_TRANSIT)
                ->where(function ($query) use ($ownedCenter) {
                    $query->where('from_sorting_center_id', $ownedCenter->id)
                        ->orWhere('to_sorting_center_id', $ownedCenter->id);
                })->exists();
            if ($hasParcels || $hasTransfers) {
                return back()->withErrors(['sorting_center' => 'Branch has unfinished parcels or transfers. Complete or reroute them before deactivation.']);
            }
            // Invalidate staff tokens when branch becomes inactive; re-login after activation.
            foreach ($ownedCenter->staff as $staff) $staff->tokens()->delete();
        } else {
            if (! $ownedCenter->staff()->where('role', 'manager')->where('status', 'active')->exists()) {
                return back()->withErrors(['sorting_center' => 'Assign an active Branch Manager before activating this branch.']);
            }
            if (! $ownedCenter->coverageAreas()->exists()) {
                return back()->withErrors(['coverage' => 'Add at least one delivery coverage area before activating.']);
            }
            $this->assertNoOverlappingCoverage($partner, $ownedCenter);
        }
        $ownedCenter->update([
            'status' => $next,
            'deactivated_at' => $next === 'inactive' ? now() : null,
        ]);

        return redirect()->route('logistics.sorting-centers.show', $ownedCenter)
            ->with('status', 'Branch is now '.($next === 'active' ? 'active and ready for assignments.' : 'inactive.'));
    }

    public function archive(Request $request, int $center, BranchLifecycleService $lifecycle): RedirectResponse
    {
        $partner = $this->partner($request);
        $lifecycle->archive($center, (int) $partner->id);
        return redirect()->route('logistics.sorting-centers.index')
            ->with('status', 'Branch archived. Historical parcels, scan records and payouts are preserved.');
    }

    public function archived(Request $request): View
    {
        $partner = $this->partner($request);
        $centers = $partner->sortingCenters()->whereNotNull('archived_at')
            ->orderByDesc('archived_at')->get();
        return view('logistics.sorting-centers.archived', compact('partner', 'centers'));
    }

    public function restore(Request $request, int $center, BranchLifecycleService $lifecycle): RedirectResponse
    {
        $partner = $this->partner($request);
        $lifecycle->restore($center, (int) $partner->id);
        return redirect()->route('logistics.sorting-centers.show', $center)
            ->with('status', 'Branch restored as inactive. Review its coverage and manager before activating.');
    }

    private function validatedCenter(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:180'],
            'contact_no' => ['nullable', 'string', 'max:30'],
            'region' => ['required', 'string', 'max:150'],
            'province' => ['required', 'string', 'max:150'],
            'municipality' => ['required', 'string', 'max:150'],
            'barangay' => ['required', 'string', 'max:150'],
            'street_no' => ['required', 'string', 'max:180'],
            'unit_no' => ['nullable', 'string', 'max:180'],
            'coverage' => ['required', 'array', 'min:1', 'max:20'],
            'coverage.*.area_name' => ['required', 'string', 'max:150'],
            'coverage.*.area_type' => ['required', 'in:province,region'],
            'coverage.*.cities' => ['nullable', 'string', 'max:5000'],
            'manager_name' => ['nullable', 'required_with:manager_email', 'string','min:2','max:150'],
            'manager_email' => ['nullable', 'required_with:manager_name', 'email','max:190'],
        ]);
    }

    private function centerPayload(array $data): array
    {
        return [
            'name' => trim($data['name']),
            'contact_no' => filled($data['contact_no'] ?? null) ? trim($data['contact_no']) : null,
            'region' => trim($data['region']),
            'province' => trim($data['province']),
            'municipality' => trim($data['municipality']),
            'barangay' => trim($data['barangay']),
            'street_no' => trim($data['street_no']),
            'unit_no' => filled($data['unit_no'] ?? null) ? trim($data['unit_no']) : null,
        ];
    }

    private function syncCoverage(LogisticsPartner $partner, SortingCenter $center, array $coverage): void
    {
        // Never re-create area IDs when an address-only edit leaves coverage unchanged.
        $normalize = static function (string $type, string $name, string $cities): string {
            $cityList = strtoupper(trim($cities)) === 'ALL' || trim($cities) === ''
                ? ['ALL'] : collect(preg_split('/[|,]+/', $cities))
                    ->map(fn ($v) => mb_strtolower(trim($v)))
                    ->filter()->unique()->sort()->values()->all();
            return mb_strtolower(trim($type)).'|'.mb_strtolower(trim($name)).'|'.implode('|', $cityList);
        };
        $existing = $center->coverageAreas()->get()->map(
            fn ($row) => $normalize($row->area_type, $row->area_name, $row->cities ?: 'ALL')
        )->sort()->values()->all();
        $incoming = collect($coverage)->map(
            fn ($row) => $normalize($row['area_type'], $row['area_name'], (string) ($row['cities'] ?? 'ALL'))
        )->sort()->values()->all();
        if ($existing === $incoming) return;

        $existingIds = $center->coverageAreas()->pluck('id');
        if ($existingIds->isNotEmpty()
            && DB::table('deliveries')->whereIn('destination_area_id', $existingIds)->exists()) {
            throw ValidationException::withMessages([
                'coverage' => 'This coverage is referenced by existing parcels. Keep those areas until the deliveries finish.',
            ]);
        }
        $center->coverageAreas()->delete();

        foreach ($coverage as $row) {
            $cities = trim((string) ($row['cities'] ?? ''));
            $cities = $cities === '' ? 'ALL' : collect(preg_split('/[|,]+/', $cities))
                ->map(fn ($value) => trim((string) $value))
                ->filter()
                ->unique(fn ($value) => strtolower($value))
                ->implode('|');

            $partner->coverageAreas()->create([
                'sorting_center_id' => $center->id,
                'area_name' => trim($row['area_name']),
                'area_type' => $row['area_type'],
                'cities' => $cities ?: 'ALL',
            ]);
        }
    }

    /** Prevent competing active branches in the same province/region and city. */
    private function assertNoOverlappingCoverage(LogisticsPartner $partner, SortingCenter $center): void
    {
        $matcher = app(PhilippineDeliveryCoverage::class);
        $mainActive = $partner->sortingCenters()->where('is_main', true)
            ->where('status', 'active')->exists();
        $others = $partner->coverageAreas()
            ->where(function ($query) use ($center) {
                $query->where('sorting_center_id', '!=', $center->id)
                    ->orWhereNull('sorting_center_id');
            })
            ->with('sortingCenter')->get()
            ->filter(fn ($area) => $area->sorting_center_id === null
                ? ($mainActive && ! $center->is_main)
                : $area->sortingCenter?->status === 'active');
        foreach ($center->coverageAreas as $candidate) {
            foreach ($others as $existing) {
                if ($matcher->overlaps($candidate, $existing)) {
                    throw ValidationException::withMessages(['coverage' =>
                        'Coverage overlaps an active branch in '.$candidate->area_name.'. Adjust the covered cities before activating.']);
                }
            }
        }
    }

    private function nextCode(LogisticsPartner $partner): string
    {
        $prefix = 'SC-'.str_pad((string) $partner->id, 4, '0', STR_PAD_LEFT).'-';
        $sequence = $partner->sortingCenters()->count() + 1;

        do {
            $code = $prefix.str_pad((string) $sequence, 2, '0', STR_PAD_LEFT);
            $sequence++;
        } while ($partner->sortingCenters()->where('code', $code)->exists());

        return $code;
    }

    private function partner(Request $request): LogisticsPartner
    {
        return LogisticsPartner::where('user_id', $request->user()->id)->first()
            ?? abort(403, 'Logistics profile unavailable.');
    }
}
