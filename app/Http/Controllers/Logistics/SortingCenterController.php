<?php

namespace App\Http\Controllers\Logistics;

use App\Http\Controllers\Controller;
use App\Models\Logistics\SortingCenter;
use App\Models\LogisticsPartner;
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
        $centers = $partner->sortingCenters()
            ->with(['coverageAreas', 'riders'])
            ->orderByDesc('is_main')
            ->orderBy('name')
            ->get();

        return view('logistics.sorting-centers.index', compact('partner', 'centers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $partner = $this->partner($request);
        $data = $this->validatedCenter($request);

        DB::transaction(function () use ($partner, $data) {
            $center = $partner->sortingCenters()->create([
                ...$this->centerPayload($data),
                'code' => $this->nextCode($partner),
                'is_main' => false,
                'status' => 'active',
            ]);

            $this->syncCoverage($partner, $center, $data['coverage']);
        });

        return back()->with('status', 'Sorting Center branch added.');
    }

    public function update(Request $request, int $center): RedirectResponse
    {
        $partner = $this->partner($request);
        $ownedCenter = $partner->sortingCenters()->findOrFail($center);
        $data = $this->validatedCenter($request);

        DB::transaction(function () use ($partner, $ownedCenter, $data) {
            $ownedCenter->update($this->centerPayload($data));
            $this->syncCoverage($partner, $ownedCenter, $data['coverage']);
        });

        return back()->with('status', 'Sorting Center updated.');
    }

    public function toggle(Request $request, int $center): RedirectResponse
    {
        $partner = $this->partner($request);
        $ownedCenter = $partner->sortingCenters()->findOrFail($center);

        if ($ownedCenter->is_main) {
            return back()->withErrors(['sorting_center' => 'The Main Sorting Center must remain active.']);
        }

        $next = $ownedCenter->status === 'active' ? 'inactive' : 'active';
        $ownedCenter->update(['status' => $next]);

        return back()->with('status', 'Sorting Center status updated.');
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
        $existingIds = $center->coverageAreas()->pluck('id');

        if ($existingIds->isNotEmpty()
            && DB::table('deliveries')->whereIn('destination_area_id', $existingIds)->exists()) {
            throw ValidationException::withMessages([
                'coverage' => 'Coverage used by an existing parcel cannot be replaced yet. Add the new branch/area first, then finish the active deliveries.',
            ]);
        }

        // Branch-owned coverage can be replaced when no persisted parcel depends on it.
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
