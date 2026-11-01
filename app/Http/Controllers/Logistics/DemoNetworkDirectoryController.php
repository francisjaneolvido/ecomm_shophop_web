<?php

namespace App\Http\Controllers\Logistics;

use App\Http\Controllers\Controller;
use App\Models\Logistics\SortingCenter;
use App\Models\LogisticsPartner;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Read-only account directory; never exposes password hashes or resets credentials. */
class DemoNetworkDirectoryController extends Controller
{
    public function index(Request $request): View
    {
        $partner = LogisticsPartner::where('user_id', $request->user()->id)->firstOrFail();
        $scope = SortingCenter::query()->where('logistics_partner_id', $partner->id)
            ->where('type', 'station_hub')->where('code', 'like', 'NW-C-%');
        $total = (clone $scope)->count();
        $provinceCount = (clone $scope)->whereNotNull('province')->where('province', '!=', '')
            ->distinct()->count('province');
        $riderCount = \App\Models\Logistics\Rider::query()->where('logistics_partner_id', $partner->id)
            ->where('email', 'like', 'nw.%@example.com')->count();

        $search = trim((string) $request->query('q', ''));
        if ($search !== '') {
            $scope->where(fn ($q) => $q->where('name', 'like', '%'.$search.'%')
                ->orWhere('municipality', 'like', '%'.$search.'%')
                ->orWhere('province', 'like', '%'.$search.'%')
                ->orWhere('region', 'like', '%'.$search.'%')
                ->orWhere('code', 'like', '%'.$search.'%'));
        }
        $province = trim((string) $request->query('province', ''));
        if ($province !== '') $scope->where('province', $province);

        $centers = $scope->with('user:id,email,account_type,status')
            ->withCount('riders')->orderBy('province')->orderBy('municipality')
            ->paginate(35)->withQueryString();
        $provinces = SortingCenter::query()->where('logistics_partner_id', $partner->id)
            ->where('code', 'like', 'NW-C-%')->whereNotNull('province')->where('province', '!=', '')
            ->distinct()->orderBy('province')->pluck('province');

        return view('logistics.demo-network.index', compact(
            'partner', 'centers', 'total', 'provinceCount', 'riderCount', 'provinces', 'search', 'province'
        ));
    }
}
