<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Buyer\Order\Order;
use App\Models\Buyer\Order\OrderItem;
use App\Models\Buyer\Review\Review;
use App\Models\Seller\Manage_inventory\Product;
use App\Models\Logistics\PickupRequest;
use App\Models\Seller;
use App\Services\LogisticsRoutingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(private readonly LogisticsRoutingService $routing)
    {
    }

    public function index(Request $request): View
    {
        return $this->orderDirectory($request, 'notifications');
    }

    public function show(Request $request, int $order): View
    {
        $ownedOrder = $this->ownedOrder($request, $order);
        $ownedOrder->load([
            'buyer', 'seller', 'items.product', 'items.variant',
            'pickupRequest.partner', 'pickupRequest.originSortingCenter',
            'delivery.rider', 'delivery.pickupRider', 'delivery.deliveryRider', 'delivery.deliveredRider',
            'delivery.originSortingCenter', 'delivery.currentSortingCenter', 'delivery.destinationSortingCenter',
            'delivery.transfers.fromCenter', 'delivery.transfers.toCenter', 'delivery.failureReports.rider',
            'codSettlement', 'returnCase.returnRider',
        ]);

        $logisticsPartners = collect();
        if ($ownedOrder->isPaymentEligible() && $ownedOrder->status === Order::STATUS_PREPARING) {
            $logisticsPartners = $this->routing->eligiblePartners($ownedOrder);
        }

        // Preserve the seller's directory filters without accepting an arbitrary redirect URL.
        $from = (string) $request->query('from', 'notifications');
        if (! in_array($from, ['notifications', 'prepare', 'courier', 'confirm'], true)) {
            $from = 'notifications';
        }
        $returnParams = [];
        foreach (['q', 'status', 'sort', 'per_page', 'page'] as $key) {
            $value = $request->query($key);
            if (is_scalar($value) && trim((string) $value) !== '') {
                $returnParams[$key] = substr((string) $value, 0, 100);
            }
        }
        $backUrl = route('seller.orders.' . $from, $returnParams);

        return view('seller.orders.show', [
            'order' => $ownedOrder,
            'logisticsPartners' => $logisticsPartners,
            'backUrl' => $backUrl,
        ]);
    }

    public function prepare(Request $request): View
    {
        return $this->orderDirectory($request, 'prepare');
    }

    public function courier(Request $request): View
    {
        return $this->orderDirectory($request, 'courier');
    }

    public function confirm(Request $request): View
    {
        return $this->orderDirectory($request, 'confirm');
    }

    /**
     * Seller-owned, server-filtered queues. Never fetch every Order into the browser.
     * All scopes are applied before counts, ordering, and pagination.
     */
    private function orderDirectory(Request $request, string $section): View
    {
        $seller = $this->seller($request);
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'string', 'max:32'],
            'sort' => ['nullable', 'string', 'in:newest,oldest,amount_high,amount_low,oldest_pending'],
            'per_page' => ['nullable', 'integer', 'in:25,50'],
        ]);
        $search = trim((string) ($validated['q'] ?? ''));
        $sort = (string) ($validated['sort'] ?? ($section === 'notifications' ? 'oldest_pending' : 'newest'));
        $perPage = (int) ($validated['per_page'] ?? 25);
        $status = (string) ($validated['status'] ?? 'all');

        $base = $this->ordersFor($seller);
        $statusCounts = (clone $base)->paymentEligible()
            ->selectRaw('status, COUNT(*) AS total')->groupBy('status')->pluck('total', 'status');
        $stats = [
            'new' => (int) ($statusCounts[Order::STATUS_TO_SHIP] ?? 0),
            'preparing' => (int) ($statusCounts[Order::STATUS_CONFIRMED] ?? 0)
                + (int) ($statusCounts[Order::STATUS_PREPARING] ?? 0),
            'pickup' => (int) ($statusCounts[Order::STATUS_READY_FOR_PICKUP] ?? 0),
            'shipping' => (int) ($statusCounts[Order::STATUS_TO_RECEIVE] ?? 0),
            'completed' => (int) ($statusCounts[Order::STATUS_COMPLETED] ?? 0),
        ];

        $tabs = match ($section) {
            'prepare' => [
                'all' => 'All to prepare', 'confirmed' => 'Confirmed', 'preparing' => 'Preparing',
            ],
            'courier' => [
                'all' => 'All handovers', 'awaiting' => 'Awaiting pickup', 'picked_up' => 'Picked up / in transit',
            ],
            'confirm' => [
                'all' => 'All deliveries', 'in_transit' => 'In transit',
                'delivered' => 'Delivered - awaiting buyer', 'completed' => 'Buyer confirmed',
                'exceptions' => 'Exceptions / returns',
            ],
            default => [
                'all' => 'All orders', 'new' => 'New', 'confirmed' => 'Confirmed',
                'preparing' => 'Preparing', 'ready' => 'Ready for pickup',
                'shipping' => 'Shipping', 'completed' => 'Completed',
                'cancelled' => 'Cancelled', 'returned' => 'Returned',
            ],
        };
        if (! array_key_exists($status, $tabs)) {
            $status = 'all';
        }

        $orders = (clone $base)->with([
            'buyer', 'items.product', 'items.variant', 'pickupRequest.partner',
            'delivery.deliveredRider', 'logisticsPartner', 'returnCase',
        ])->withCount('items');

        if ($section === 'prepare') {
            $orders->paymentEligible()->whereIn('status', [Order::STATUS_CONFIRMED, Order::STATUS_PREPARING]);
        } elseif ($section === 'courier') {
            $orders->paymentEligible()->whereIn('status', [Order::STATUS_READY_FOR_PICKUP, Order::STATUS_TO_RECEIVE]);
        } elseif ($section === 'confirm') {
            $orders->whereIn('status', [Order::STATUS_TO_RECEIVE, Order::STATUS_COMPLETED, Order::STATUS_RETURNED])
                ->whereHas('delivery');
        }

        if ($search !== '') {
            $orders->where(function ($query) use ($search) {
                $query->where('tracking_code', 'like', "%{$search}%")
                    ->orWhereHas('buyer', function ($buyer) use ($search) {
                        $buyer->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    });
                if (preg_match('/^(?:SHP[- ]?)?0*(\d+)$/i', $search, $match)) {
                    $query->orWhere('id', (int) $match[1]);
                }
            });
        }

        if ($section === 'confirm') {
            match ($status) {
                'in_transit' => $orders->where('status', Order::STATUS_TO_RECEIVE)
                    ->whereHas('delivery', fn ($q) => $q->whereNotIn('status', [
                        \App\Models\Logistics\Delivery::DELIVERED,
                        \App\Models\Logistics\Delivery::DELIVERY_FAILED,
                        \App\Models\Logistics\Delivery::RETURNED,
                        \App\Models\Logistics\Delivery::RETURN_REQUESTED,
                        \App\Models\Logistics\Delivery::RETURN_AT_BRANCH,
                        \App\Models\Logistics\Delivery::RETURN_ASSIGNED,
                        \App\Models\Logistics\Delivery::RETURN_IN_TRANSIT,
                        \App\Models\Logistics\Delivery::RETURN_AWAITING_SELLER,
                    ])),
                'delivered' => $orders->where('status', Order::STATUS_TO_RECEIVE)
                    ->whereHas('delivery', fn ($q) => $q->where('status', \App\Models\Logistics\Delivery::DELIVERED)),
                'completed' => $orders->where('status', Order::STATUS_COMPLETED),
                'exceptions' => $orders->where(fn ($q) => $q->where('status', Order::STATUS_RETURNED)
                    ->orWhereHas('delivery', fn ($d) => $d->whereIn('status', [
                        \App\Models\Logistics\Delivery::DELIVERY_FAILED,
                        \App\Models\Logistics\Delivery::RETURN_REQUESTED,
                        \App\Models\Logistics\Delivery::RETURN_AT_BRANCH,
                        \App\Models\Logistics\Delivery::RETURN_ASSIGNED,
                        \App\Models\Logistics\Delivery::RETURN_IN_TRANSIT,
                        \App\Models\Logistics\Delivery::RETURN_AWAITING_SELLER,
                    ]))),
                default => null,
            };
        } elseif ($status !== 'all') {
            $statusMap = match ($section) {
                'prepare' => ['confirmed' => Order::STATUS_CONFIRMED, 'preparing' => Order::STATUS_PREPARING],
                'courier' => ['awaiting' => Order::STATUS_READY_FOR_PICKUP, 'picked_up' => Order::STATUS_TO_RECEIVE],
                default => [
                    'new' => Order::STATUS_TO_SHIP, 'confirmed' => Order::STATUS_CONFIRMED,
                    'preparing' => Order::STATUS_PREPARING, 'ready' => Order::STATUS_READY_FOR_PICKUP,
                    'shipping' => Order::STATUS_TO_RECEIVE, 'completed' => Order::STATUS_COMPLETED,
                    'cancelled' => Order::STATUS_CANCELLED, 'returned' => Order::STATUS_RETURNED,
                ],
            };
            if (isset($statusMap[$status])) {
                $orders->where('status', $statusMap[$status]);
            }
        }

        match ($sort) {
            'oldest' => $orders->orderBy('created_at')->orderBy('id'),
            'amount_high' => $orders->orderByDesc('total_amount')->orderByDesc('id'),
            'amount_low' => $orders->orderBy('total_amount')->orderBy('id'),
            'oldest_pending' => $orders->orderByRaw('CASE WHEN status = ? THEN 0 ELSE 1 END', [Order::STATUS_TO_SHIP])
                ->orderBy('created_at')->orderBy('id'),
            default => $orders->orderByDesc('created_at')->orderByDesc('id'),
        };

        $orders = $orders->paginate($perPage)->withQueryString();
        $config = match ($section) {
            'prepare' => ['title' => 'Prepare Orders', 'description' => 'Pack confirmed orders and request pickup only when ready.'],
            'courier' => ['title' => 'Courier Handover', 'description' => 'Monitor ready parcels, assigned pickups and actual courier handoffs.'],
            'confirm' => ['title' => 'Delivery Tracking & Confirmations', 'description' => 'Monitor Rider delivery evidence and Buyer receipt confirmation. Sellers cannot mark parcels delivered.'],
            default => ['title' => 'Order Notifications', 'description' => 'Review new purchases, track orders and prioritize pending confirmations.'],
        };

        return view('seller.orders.' . $section, compact(
            'orders', 'tabs', 'status', 'search', 'sort', 'perPage', 'stats', 'config', 'section'
        ));
    }

    public function dashboard(Request $request): View
    {
        $seller = $this->seller($request);
        $orders = $this->ordersFor($seller);
        $counts = (clone $orders)->paymentEligible()->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')->pluck('total', 'status');
        $monthlyOrderCount = (clone $orders)
            ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->count();
        $recentOrders = (clone $orders)->with('buyer')->withCount('items')
            ->latest()->limit(6)->get()->map(fn (Order $order) => [
                'id' => $order->id,
                'buyer_name' => trim(($order->buyer?->first_name ?? '') . ' ' . ($order->buyer?->last_name ?? '')),
                'items_count' => $order->items_count,
                'total' => $order->total_amount,
                'status' => $order->status,
                'status_label' => $order->statusLabel(),
                'placed_at' => $order->created_at->format('M j, Y g:i A'),
            ]);

        $products = fn () => Product::query()->where('seller_id', $seller->user_id);
        $lowStockProducts = $products()
            ->where('status', 'active')
            ->where('stock', '>', 0)
            ->whereColumn('stock', '<=', 'low_stock_threshold')
            ->orderBy('stock')
            ->limit(8)
            ->get(['id', 'name', 'stock', 'low_stock_threshold']);
        $lowStockCount = $products()
            ->where('status', 'active')
            ->where('stock', '>', 0)
            ->whereColumn('stock', '<=', 'low_stock_threshold')
            ->count();
        $outOfStockCount = $products()
            ->where('status', 'active')
            ->where('stock', '<=', 0)
            ->count();

        $completedOrders = (clone $orders)->where('status', Order::STATUS_COMPLETED);
        $salesToday = (float) (clone $completedOrders)->whereDate('updated_at', today())->sum('total_amount');
        $salesMonth = (float) (clone $completedOrders)
            ->whereBetween('updated_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->sum('total_amount');
        $previousMonthSales = (float) (clone $completedOrders)
            ->whereBetween('updated_at', [now()->subMonthNoOverflow()->startOfMonth(), now()->subMonthNoOverflow()->endOfMonth()])
            ->sum('total_amount');
        $salesGrowthPct = $previousMonthSales > 0
            ? (($salesMonth - $previousMonthSales) / $previousMonthSales) * 100
            : ($salesMonth > 0 ? 100.0 : 0.0);

        $weekStart = now()->subDays(6)->startOfDay();
        $weekOrders = (clone $completedOrders)->where('updated_at', '>=', $weekStart)->get(['total_amount', 'updated_at']);
        $weeklySales = collect(range(0, 6))->map(function (int $offset) use ($weekStart, $weekOrders) {
            $day = $weekStart->copy()->addDays($offset);
            return [
                'label' => $day->format('D'),
                'amount' => (float) $weekOrders->filter(fn (Order $order) => $order->updated_at->isSameDay($day))->sum('total_amount'),
            ];
        });

        $topProducts = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->where('orders.seller_id', $seller->id)
            ->where('orders.status', Order::STATUS_COMPLETED)
            ->selectRaw('products.id, products.name, SUM(order_items.quantity) as sold, SUM(order_items.quantity * order_items.price) as revenue')
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('sold')
            ->limit(5)
            ->get()
            ->map(fn ($row) => [
                'name' => $row->name,
                'sold' => (int) $row->sold,
                'revenue' => (float) $row->revenue,
            ]);

        $reviewsQuery = Review::query()
            ->whereHas('product', fn ($query) => $query->where('seller_id', $seller->user_id));
        $reviewCount = (clone $reviewsQuery)->count();
        $averageRating = $reviewCount > 0 ? (float) (clone $reviewsQuery)->avg('rating') : null;
        $recentFeedback = (clone $reviewsQuery)->with('buyer')->latest()->limit(2)->get()->map(fn (Review $review) => [
            'comment' => (string) ($review->comment ?? ''),
            'buyer_name' => trim(($review->buyer?->first_name ?? '').' '.($review->buyer?->last_name ?? '')) ?: 'Buyer',
            'order_id' => null,
        ]);

        return view('seller.dashboard', [
            'newOrders' => (int) ($counts[Order::STATUS_TO_SHIP] ?? 0),
            'ordersToPrepare' => (int) (($counts[Order::STATUS_CONFIRMED] ?? 0) + ($counts[Order::STATUS_PREPARING] ?? 0)),
            'readyForPickup' => (int) ($counts[Order::STATUS_READY_FOR_PICKUP] ?? 0),
            'monthlyOrderCount' => $monthlyOrderCount,
            'recentOrders' => $recentOrders,
            'totalProducts' => $products()->count(),
            'activeProducts' => $products()->where('status', 'active')->count(),
            'lowStockCount' => $lowStockCount,
            'outOfStockCount' => $outOfStockCount,
            'lowStockProducts' => $lowStockProducts,
            'salesToday' => $salesToday,
            'salesMonth' => $salesMonth,
            'salesGrowthPct' => $salesGrowthPct,
            'weeklySales' => $weeklySales,
            'topProducts' => $topProducts,
            'reviewCount' => $reviewCount,
            'averageRating' => $averageRating,
            'recentFeedback' => $recentFeedback,
        ]);
    }

    /** ERP CONFIRMED stage: Seller explicitly accepts an eligible Order before preparation. */
    public function accept(Request $request, int $order): RedirectResponse
    {
        return DB::transaction(function () use ($request, $order) {
            $owned = $this->lockedEligibleOrder($request, $order, Order::STATUS_TO_SHIP);
            if (! $owned) {
                return $this->stale($order);
            }

            $owned->forceFill([
                'status' => Order::STATUS_CONFIRMED,
                'tracking_code' => $owned->tracking_code ?: $this->routing->trackingCode($owned),
            ])->save();

            return redirect()->route('seller.orders.show', $owned)
                ->with('status', 'Order accepted. You can now prepare and pack the parcel.');
        });
    }

    public function startPreparation(Request $request, int $order): RedirectResponse
    {
        return $this->transition($request, $order, Order::STATUS_CONFIRMED, Order::STATUS_PREPARING,
            'Preparation started. Pack the items, then Mark Ready & Request Pickup. Print the routed ShopHop waybill after the pickup request is created.');
    }

    /** Seller selects the Logistics company and creates the explicit pickup request. */
    public function markReady(Request $request, int $order): RedirectResponse
    {
        // Required only when the Buyer did not choose a courier at Checkout.
        $data = $request->validate([
            'logistics_partner_id' => ['nullable', 'integer'],
        ]);

        return DB::transaction(function () use ($request, $order, $data) {
            $owned = $this->lockedEligibleOrder($request, $order, Order::STATUS_PREPARING);
            if (! $owned) {
                return $this->stale($order);
            }

            $owned->loadMissing(['seller', 'buyer']);

            // The Buyer's choice wins; the Seller's form value is only a fallback for orders without one.
            $partnerId = (int) ($owned->logistics_partner_id ?: ($data['logistics_partner_id'] ?? 0));
            $partner = $partnerId > 0
                ? $this->routing->eligiblePartners($owned)->firstWhere('id', $partnerId)
                : null;

            if (! $partner) {
                throw ValidationException::withMessages([
                    'logistics_partner_id' => $owned->logistics_partner_id
                        ? 'The courier the Buyer chose can no longer serve this order. Contact ShopHop support.'
                        : 'Choose an approved Logistics / Sorting Center that covers this delivery destination.',
                ]);
            }

            $originCenter = $this->routing->originCenter($partner, $owned->seller);
            if (! $originCenter) {
                throw ValidationException::withMessages([
                    'logistics_partner_id' => 'This Logistics partner has no active Sorting Center available for pickup.',
                ]);
            }

            $trackingCode = $owned->tracking_code ?: $this->routing->trackingCode($owned);
            $owned->forceFill([
                'tracking_code' => $trackingCode,
                'status' => Order::STATUS_READY_FOR_PICKUP,
            ])->save();

            PickupRequest::updateOrCreate(
                ['order_id' => $owned->id],
                [
                    'logistics_partner_id' => $partner->id,
                    'origin_sorting_center_id' => $originCenter->id,
                    'status' => PickupRequest::REQUESTED,
                    'origin_province_code' => $owned->seller?->province_code,
                    'origin_province_name' => $owned->seller?->province_name,
                    'origin_municipality_code' => $owned->seller?->municipality_code,
                    'origin_municipality_name' => $owned->seller?->municipality_name,
                    'origin_barangay_code' => $owned->seller?->barangay_code,
                    'origin_barangay_name' => $owned->seller?->barangay_name,
                    'origin_street' => $owned->seller?->street_address,
                    'requested_at' => now(),
                    'assigned_at' => null,
                    'picked_up_at' => null,
                    'received_at' => null,
                ]
            );

            return redirect()->route('seller.orders.show', $owned)
                ->with('status', 'Pickup requested from '.$partner->company_name.'. Print/attach the shipping label before handoff.');
        });
    }

    public function shippingLabel(Request $request, int $order): View
    {
        $owned = $this->ownedOrder($request, $order);
        abort_unless($owned->isPaymentEligible() && $owned->pickupRequest && in_array($owned->status, [
            Order::STATUS_READY_FOR_PICKUP,
            Order::STATUS_TO_RECEIVE,
            Order::STATUS_COMPLETED,
        ], true), 404);

        if (! $owned->tracking_code) {
            $owned->forceFill(['tracking_code' => $this->routing->trackingCode($owned)])->save();
        }

        $owned->load(['seller', 'buyer', 'items.product', 'items.variant', 'logisticsPartner', 'pickupRequest.partner', 'pickupRequest.originSortingCenter']);

        return view('seller.orders.shipping-label', ['order' => $owned]);
    }

    private function transition(Request $request, int $orderId, string $expected, string $next, string $message): RedirectResponse
    {
        return DB::transaction(function () use ($request, $orderId, $expected, $next, $message) {
            $order = $this->lockedEligibleOrder($request, $orderId, $expected);
            if (! $order) {
                return $this->stale($orderId);
            }

            $order->update(['status' => $next]);

            return redirect()->route('seller.orders.show', $order)->with('status', $message);
        });
    }

    private function lockedEligibleOrder(Request $request, int $orderId, string $expected): ?Order
    {
        $seller = $this->seller($request);
        $order = $this->ordersFor($seller)->whereKey($orderId)->lockForUpdate()->firstOrFail();
        return $order->isPaymentEligible() && $order->status === $expected ? $order : null;
    }

    private function stale(int $orderId): RedirectResponse
    {
        return redirect()->route('seller.orders.show', $orderId)
            ->withErrors(['status' => 'This Order changed or is no longer eligible for that action. Refresh and review it.']);
    }

    private function ownedOrder(Request $request, int $orderId): Order
    {
        return $this->ordersFor($this->seller($request))->findOrFail($orderId);
    }

    private function ordersFor(Seller $seller)
    {
        return Order::where('seller_id', $seller->id);
    }

    private function seller(Request $request): Seller
    {
        return $request->user()->seller ?? abort(403, 'Seller profile unavailable.');
    }
}