<?php

// app/Http/Controllers/branch/ParcelController.php

namespace App\Http\Controllers\branch;

use App\Http\Controllers\Controller;
use App\Models\Logistics\Delivery;
use App\Models\Logistics\Rider;
use App\Models\Logistics\SortingCenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ParcelController extends Controller
{
    /**
     * Tab key sa view => status values sa deliveries table.
     * ASSUMPTION: i-adjust sa totoong status values ng Delivery model mo.
     */
    private const STATUS_MAP = [
        'at_branch'        => ['at_branch', 'sorted'],
        'out_for_delivery' => ['out_for_delivery'],
        'delivered'        => ['delivered'],
        'failed'           => ['failed', 'failed_attempt'],
        'returning'        => ['returning', 'return_to_sender'],
    ];

    /** Mga status na pwedeng i-assign sa rider. */
    private const ASSIGNABLE = ['at_branch', 'sorted', 'failed', 'failed_attempt'];

    public function index(Request $request): View
    {
        $center = $this->currentCenter($request);

        $status = $request->query('status', 'all');
        if ($status !== 'all' && ! array_key_exists($status, self::STATUS_MAP)) {
            $status = 'all';
        }
        $search = trim((string) $request->query('q', ''));

        $base = Delivery::query()->where('sorting_center_id', $center->id);

        // Stats (isang query, hindi apektado ng filter/search)
        $rows = (clone $base)->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')->pluck('total', 'status');

        $countFor = fn (string $key) => collect(self::STATUS_MAP[$key])
            ->sum(fn ($s) => (int) ($rows[$s] ?? 0));

        $counts = [
            'at_branch'        => $countFor('at_branch'),
            'out_for_delivery' => $countFor('out_for_delivery'),
            'failed'           => $countFor('failed'),
        ];

        $parcels = $base
            ->with(['order', 'rider'])
            ->when($status !== 'all', fn ($q) => $q->whereIn('status', self::STATUS_MAP[$status]))
            ->when($search !== '', function ($q) use ($search) {
                $like = '%'.str_replace(['%', '_'], ['\%', '\_'], $search).'%';
                $q->where(function ($w) use ($like) {
                    $w->where('tracking_number', 'like', $like)
                      ->orWhere('receiver_name', 'like', $like)
                      ->orWhereHas('order', fn ($o) => $o->where('order_number', 'like', $like));
                });
            })
            ->latest('updated_at')
            ->paginate(20)
            ->withQueryString()
            ->through(fn (Delivery $d) => $this->present($d));

        $riders = Rider::query()
            ->where('sorting_center_id', $center->id)
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('branch.parcels.index', [
            'center'       => $center,
            'parcels'      => $parcels,
            'counts'       => $counts,
            'riders'       => $riders,
            'activeStatus' => $status,
            'search'       => $search,
        ]);
    }

    public function assign(Request $request): RedirectResponse
    {
        $center = $this->currentCenter($request);

        $data = $request->validate([
            'parcel_ids'   => ['required', 'array', 'min:1', 'max:100'],
            'parcel_ids.*' => ['string', 'max:64'],
            'rider_id'     => ['required', 'integer'],
        ], [
            'parcel_ids.required' => 'Pumili muna ng kahit isang parcel.',
            'rider_id.required'   => 'Pumili ng rider.',
        ]);

        // Dapat rider ng branch na ito at active
        $rider = Rider::query()
            ->where('sorting_center_id', $center->id)
            ->where('status', 'active')
            ->find($data['rider_id']);

        if (! $rider) {
            return back()->with('error', 'Hindi valid ang rider na napili.');
        }

        $assigned = DB::transaction(function () use ($center, $rider, $data) {
            $deliveries = Delivery::query()
                ->where('sorting_center_id', $center->id)
                ->whereIn('tracking_number', $data['parcel_ids'])
                ->whereIn('status', self::ASSIGNABLE)
                ->lockForUpdate()
                ->get();

            foreach ($deliveries as $delivery) {
                $delivery->update([
                    'rider_id' => $rider->id,
                    'status'   => 'out_for_delivery',
                ]);
            }

            return $deliveries->count();
        });

        if ($assigned === 0) {
            return back()->with('error', 'Walang na-assign. Baka na-update na ang mga parcel.');
        }

        $skipped = count($data['parcel_ids']) - $assigned;
        $message = "{$assigned} parcel(s) na-assign kay {$rider->name}.";
        if ($skipped > 0) {
            $message .= " {$skipped} ang nilaktawan dahil hindi na pwedeng i-assign.";
        }

        return back()->with('success', $message);
    }

    /** Hugis ng data na hinihingi ng branch.parcels.index view. */
    private function present(Delivery $d): array
    {
        $viewStatus = collect(self::STATUS_MAP)
            ->search(fn ($values) => in_array($d->status, $values, true)) ?: 'at_branch';

        return [
            'tracking' => $d->tracking_number,
            'order'    => $d->order->order_number ?? '—',
            'receiver' => $d->receiver_name,
            'barangay' => $d->delivery_address,
            'cod'      => (float) ($d->cod_amount ?? 0),
            'status'   => $viewStatus,
            'rider'    => $d->rider->name ?? null,
            'updated'  => optional($d->updated_at)->format('M d, g:i A'),
            'note'     => $d->failure_note ?? null,
        ];
    }

    /**
     * ASSUMPTION: paano malaman ang branch ng naka-login.
     * Palitan ayon sa auth setup mo (guard, relation, o session).
     */
    private function currentCenter(Request $request): SortingCenter
    {
        $user = $request->user();

        $center = SortingCenter::query()
            ->where('id', $user->sorting_center_id ?? null)
            ->first();

        abort_unless($center, 403, 'Walang naka-link na branch sa account mo.');

        return $center;
    }
}