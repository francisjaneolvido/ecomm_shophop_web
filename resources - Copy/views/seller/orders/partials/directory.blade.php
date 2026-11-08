{{-- Shared Seller operations directory: scoped, filtered and paginated by OrderController. --}}
@push('styles')
<link rel="stylesheet" href="{{ asset('css/shophop-seller-order-directory.css') }}?v=20261104a">
@endpush
@php
    $routeName = 'seller.orders.' . $section;
    $detailQuery = array_filter([
        'from' => $section, 'q' => $search, 'status' => $status,
        'sort' => $sort, 'per_page' => $perPage, 'page' => request()->query('page'),
    ], fn ($value) => is_scalar($value) && (string) $value !== '');
    $emptyMessages = [
        'notifications' => 'No matching orders. Try adjusting the filters or search terms.',
        'prepare' => 'No confirmed or preparing orders match these filters.',
        'courier' => 'No pickup or handover records match these filters.',
        'confirm' => 'No delivery records match these filters.',
    ];
@endphp
<section class="so-directory" id="sellerOrderDirectory">
    <header class="so-page-heading">
        <div class="so-page-title">
            <span class="so-eyebrow">Seller / Order Management</span>
            <h1>{{ $config['title'] }}</h1>
            <p>{{ $config['description'] }}</p>
        </div>
        <span class="so-header-chip"><x-lucide-package-check aria-hidden="true" /> Seller orders</span>
    </header>

    <nav class="so-workflow-nav" aria-label="Seller order workflow">
        <a class="{{ $section === 'notifications' ? 'is-active' : '' }}" href="{{ route('seller.orders.notifications') }}">All / New Orders</a>
        <a class="{{ $section === 'prepare' ? 'is-active' : '' }}" href="{{ route('seller.orders.prepare') }}">Prepare Orders</a>
        <a class="{{ $section === 'courier' ? 'is-active' : '' }}" href="{{ route('seller.orders.courier') }}">Courier Handover</a>
        <a class="{{ $section === 'confirm' ? 'is-active' : '' }}" href="{{ route('seller.orders.confirm') }}">Delivery Tracking</a>
    </nav>

    <div class="so-metrics" aria-label="Order status overview">
        @foreach([
            ['New orders', 'new', 'bell'],
            ['To prepare', 'preparing', 'package'],
            ['Ready for pickup', 'pickup', 'truck'],
            ['Shipping', 'shipping', 'route'],
            ['Completed', 'completed', 'check-circle'],
        ] as [$metricLabel, $metricKey, $metricIcon])
            <div class="so-metric">
                <span>{{ $metricLabel }}</span>
                <strong>{{ number_format($stats[$metricKey]) }}</strong>
            </div>
        @endforeach
    </div>

    @if ($section === 'confirm')
        <div class="so-notice" role="note">
            <x-lucide-info aria-hidden="true" />
            <span><strong>Delivery confirmation is unavailable to Sellers.</strong> The Rider records delivery and proof; the Buyer confirms receipt. This page is for monitoring only.</span>
        </div>
    @endif

    <section class="so-panel" aria-labelledby="sellerOrderListTitle">
        <div class="so-panel-heading">
            <div>
                <h2 id="sellerOrderListTitle">{{ $config['title'] }} Directory</h2>
                <p>Search, filter and review your orders without expanding large cards.</p>
            </div>
            <span class="so-count">{{ number_format($orders->total()) }} matching</span>
        </div>
        <nav class="so-filter-tabs" aria-label="Filter orders by status">
            @foreach ($tabs as $key => $label)
                <a href="{{ route($routeName, array_merge(request()->except(['status','page']), ['status' => $key])) }}"
                   class="{{ $status === $key ? 'is-active' : '' }}" @if($status === $key) aria-current="page" @endif>{{ $label }}</a>
            @endforeach
        </nav>
        <form method="GET" action="{{ route($routeName) }}" class="so-filter-form" role="search">
            <input type="hidden" name="status" value="{{ $status }}">
            <label class="so-field so-search"><span>Order ID, buyer or tracking</span>
                <input name="q" type="search" maxlength="100" value="{{ $search }}" placeholder="SHP-000123, buyer or tracking code" autocomplete="off">
            </label>
            <label class="so-field so-sort"><span>Sort by</span>
                <select name="sort">
                    <option value="newest" @selected($sort === 'newest')>Newest first</option>
                    <option value="oldest" @selected($sort === 'oldest')>Oldest first</option>
                    <option value="oldest_pending" @selected($sort === 'oldest_pending')>Oldest pending first</option>
                    <option value="amount_high" @selected($sort === 'amount_high')>Highest amount</option>
                    <option value="amount_low" @selected($sort === 'amount_low')>Lowest amount</option>
                </select>
            </label>
            <label class="so-field so-per-page"><span>Per page</span>
                <select name="per_page"><option value="25" @selected($perPage === 25)>25</option><option value="50" @selected($perPage === 50)>50</option></select>
            </label>
            <div class="so-filter-actions">
                <button type="submit" class="so-button so-button-primary">Apply</button>
                <a class="so-button so-button-secondary" href="{{ route($routeName) }}">Reset</a>
            </div>
        </form>

        <div class="so-table-scroller" role="region" aria-label="Seller orders table" tabindex="0">
            <table class="so-table">
                <thead><tr>
                    <th scope="col">Order / Buyer</th>
                    <th scope="col">Products</th>
                    <th scope="col">Placed</th>
                    <th scope="col">Total</th>
                    <th scope="col">Fulfillment</th>
                    <th scope="col">Courier / Tracking</th>
                    <th scope="col" class="so-actions-col">Actions</th>
                </tr></thead>
                <tbody>
                @forelse ($orders as $order)
                    @php
                        $buyerName = trim(($order->buyer?->first_name ?? '') . ' ' . ($order->buyer?->last_name ?? '')) ?: 'Buyer';
                        $delivery = $order->delivery;
                        $stageLabel = match (true) {
                            $order->status === \App\Models\Buyer\Order\Order::STATUS_COMPLETED => 'Buyer Confirmed',
                            $order->status === \App\Models\Buyer\Order\Order::STATUS_RETURNED => 'Returned',
                            $order->status === \App\Models\Buyer\Order\Order::STATUS_CANCELLED => 'Cancelled',
                            $delivery?->status === \App\Models\Logistics\Delivery::DELIVERED => 'Delivered - Awaiting Buyer',
                            $delivery?->status === \App\Models\Logistics\Delivery::DELIVERY_FAILED => 'Delivery Failed',
                            $order->status === \App\Models\Buyer\Order\Order::STATUS_TO_RECEIVE => 'Shipping / ' . ucwords(str_replace('_', ' ', $delivery?->status ?? 'In Transit')),
                            $order->status === \App\Models\Buyer\Order\Order::STATUS_READY_FOR_PICKUP => 'Ready / ' . ucwords(str_replace('_', ' ', $order->pickupRequest?->status ?? 'Awaiting Rider')),
                            $order->status === \App\Models\Buyer\Order\Order::STATUS_TO_SHIP => 'Placed',
                            $order->status === \App\Models\Buyer\Order\Order::STATUS_CONFIRMED => 'Confirmed',
                            $order->status === \App\Models\Buyer\Order\Order::STATUS_PREPARING => 'Preparing',
                            default => ucwords(str_replace(['-', '_'], ' ', $order->status)),
                        };
                        $stageTone = match (true) {
                            $order->status === \App\Models\Buyer\Order\Order::STATUS_COMPLETED => 'success',
                            $order->status === \App\Models\Buyer\Order\Order::STATUS_RETURNED || $order->status === \App\Models\Buyer\Order\Order::STATUS_CANCELLED || $delivery?->status === \App\Models\Logistics\Delivery::DELIVERY_FAILED => 'danger',
                            $order->status === \App\Models\Buyer\Order\Order::STATUS_TO_SHIP || $order->status === \App\Models\Buyer\Order\Order::STATUS_CONFIRMED => 'warning',
                            $order->status === \App\Models\Buyer\Order\Order::STATUS_PREPARING || $order->status === \App\Models\Buyer\Order\Order::STATUS_READY_FOR_PICKUP => 'info',
                            default => 'neutral',
                        };
                        $previewId = 'so-order-' . $order->id;
                    @endphp
                    <tr>
                        <td>
                            <a class="so-order-id" href="{{ route('seller.orders.show', array_merge(['order' => $order->id], $detailQuery)) }}">SHP-{{ str_pad((string)$order->id, 6, '0', STR_PAD_LEFT) }}</a>
                            <span class="so-muted">{{ $buyerName }}</span>
                        </td>
                        <td>
                            <div class="so-product-list">
                                @foreach ($order->items->take(2) as $item)
                                    <span title="{{ $item->product?->name ?? 'Product unavailable' }}">{{ $item->product?->name ?? 'Product unavailable' }} <small>× {{ $item->quantity }} · ₱{{ number_format((float)$item->price, 2) }}</small></span>
                                @endforeach
                                @if ($order->items->count() > 2)<small class="so-muted">+{{ $order->items->count() - 2 }} more item types</small>@endif
                            </div>
                        </td>
                        <td><time datetime="{{ $order->created_at->toDateString() }}">{{ $order->created_at->format('M j, Y') }}</time><span class="so-muted">{{ $order->created_at->format('g:i A') }}</span></td>
                        <td class="so-money">₱{{ number_format((float)$order->total_amount, 2) }}</td>
                        <td><span class="so-status so-status-{{ $stageTone }}">{{ $stageLabel }}</span>
                            @if ($delivery?->delivered_at)<span class="so-muted">{{ $delivery->delivered_at->format('M j, Y') }}</span>@endif
                        </td>
                        <td><span class="so-courier">{{ $order->pickupRequest?->partner?->company_name ?? $order->logisticsPartner?->company_name ?? 'Not yet assigned' }}</span>
                            <span class="so-muted so-tracking">{{ $order->tracking_code ?: 'No tracking yet' }}</span>
                        </td>
                        <td class="so-actions-col">
                            <div class="so-row-actions">
                                <button type="button" class="so-button so-button-secondary so-button-small" data-so-preview="{{ $previewId }}">View</button>
                                <details class="so-actions-menu">
                                    <summary aria-label="More actions for Order SHP-{{ $order->id }}">⋯</summary>
                                    <div class="so-actions-popover">
                                        <a href="{{ route('seller.orders.show', array_merge(['order' => $order->id], $detailQuery)) }}">Full order & actions</a>
                                        @if ($order->status === \App\Models\Buyer\Order\Order::STATUS_READY_FOR_PICKUP && $order->pickupRequest)
                                            <a href="{{ route('seller.orders.shipping-label', $order) }}" target="_blank" rel="noopener">Print waybill</a>
                                        @endif
                                        @if ($delivery?->proof_path)
                                            <a href="{{ route('delivery.proof', $delivery) }}" target="_blank" rel="noopener">View delivery proof</a>
                                        @endif
                                    </div>
                                </details>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="so-empty"><x-lucide-inbox aria-hidden="true" /><strong>No matching orders</strong><p>{{ $emptyMessages[$section] }}</p></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <footer class="so-table-footer">
            <p>Showing {{ $orders->firstItem() ?? 0 }}–{{ $orders->lastItem() ?? 0 }} of {{ number_format($orders->total()) }} records</p>
            <div class="so-pagination">{{ $orders->onEachSide(1)->links() }}</div>
        </footer>
    </section>

    {{-- Native templates avoid inline JSON and keep only one accessible dialog in the DOM. --}}
    @foreach($orders as $order)
        @php
            $buyerName = trim(($order->buyer?->first_name ?? '') . ' ' . ($order->buyer?->last_name ?? '')) ?: 'Buyer';
            $delivery = $order->delivery;
        @endphp
        <template id="so-order-{{ $order->id }}">
            <div class="so-preview-summary">
                <div><span class="so-eyebrow">ORDER DETAILS</span><h3>SHP-{{ str_pad((string)$order->id, 6, '0', STR_PAD_LEFT) }}</h3><p>Placed {{ $order->created_at->format('M j, Y g:i A') }}</p></div>
                <span class="so-status so-status-{{ $order->status === \App\Models\Buyer\Order\Order::STATUS_COMPLETED ? 'success' : 'info' }}">{{ $order->status === \App\Models\Buyer\Order\Order::STATUS_TO_RECEIVE ? 'In Delivery' : ($order->status === \App\Models\Buyer\Order\Order::STATUS_READY_FOR_PICKUP ? 'Ready for Pickup' : $order->statusLabel()) }}</span>
            </div>
            <div class="so-preview-grid">
                <section class="so-preview-box"><h4>Customer & Delivery</h4><dl>
                    <div><dt>Buyer</dt><dd>{{ $buyerName }}</dd></div>
                    <div><dt>Recipient</dt><dd>{{ $order->delivery_name ?: 'Not provided' }}</dd></div>
                    <div><dt>Address</dt><dd>{{ $order->delivery_address ?: 'Not provided' }}</dd></div>
                </dl></section>
                <section class="so-preview-box"><h4>Payment & Courier</h4><dl>
                    <div><dt>Payment</dt><dd>{{ $order->payment_method === 'cod' ? 'Cash on Delivery' : 'Historical / unavailable method' }}</dd></div>
                    <div><dt>Logistics</dt><dd>{{ $order->pickupRequest?->partner?->company_name ?? $order->logisticsPartner?->company_name ?? 'Not assigned' }}</dd></div>
                    <div><dt>Tracking</dt><dd>{{ $order->tracking_code ?: 'Pending' }}</dd></div>
                    @if ($delivery?->delivered_at)<div><dt>Rider delivered</dt><dd>{{ $delivery->delivered_at->format('M j, Y g:i A') }}</dd></div>@endif
                    <div><dt>Buyer receipt</dt><dd>{{ $order->status === \App\Models\Buyer\Order\Order::STATUS_COMPLETED ? 'Confirmed' : 'Not yet confirmed' }}</dd></div>
                </dl></section>
            </div>
            <section class="so-preview-box so-preview-items"><h4>Purchased Items</h4>
                @foreach($order->items as $item)
                    <div class="so-preview-line"><span>{{ $item->product?->name ?? 'Product unavailable' }} · {{ $item->variantLabel() }} · Qty {{ $item->quantity }}</span><strong>₱{{ number_format((float)$item->price, 2) }}</strong></div>
                @endforeach
                <div class="so-preview-line so-preview-total"><span>Recorded order total</span><strong>₱{{ number_format((float)$order->total_amount, 2) }}</strong></div>
            </section>
            @if ($order->status === \App\Models\Buyer\Order\Order::STATUS_TO_SHIP && $order->isPaymentEligible())
                <p class="so-preview-hint">Next step: review the complete order before accepting it.</p>
            @elseif ($order->status === \App\Models\Buyer\Order\Order::STATUS_CONFIRMED)
                <p class="so-preview-hint">Next step: start preparing this confirmed order.</p>
            @elseif ($order->status === \App\Models\Buyer\Order\Order::STATUS_PREPARING)
                <p class="so-preview-hint">Next step: review packing, courier eligibility, then request pickup.</p>
            @elseif ($order->status === \App\Models\Buyer\Order\Order::STATUS_TO_RECEIVE)
                <p class="so-preview-hint">Delivery actions belong to the Rider and Buyer; Seller may monitor proof and timeline.</p>
            @endif
            <div class="so-preview-links">
                <a class="so-button so-button-primary" href="{{ route('seller.orders.show', array_merge(['order' => $order->id], $detailQuery)) }}">Open full order & actions</a>
                @if($delivery?->proof_path)<a class="so-button so-button-secondary" href="{{ route('delivery.proof', $delivery) }}" target="_blank" rel="noopener">View proof</a>@endif
                @if($order->status === \App\Models\Buyer\Order\Order::STATUS_READY_FOR_PICKUP && $order->pickupRequest)
                    <a class="so-button so-button-secondary" href="{{ route('seller.orders.shipping-label', $order) }}" target="_blank" rel="noopener">Print waybill</a>
                @endif
            </div>
        </template>
    @endforeach

    <dialog class="so-dialog" id="sellerOrderDialog" aria-labelledby="sellerOrderDialogLabel">
        <div class="so-dialog-inner">
            <header class="so-dialog-header">
                <div><span class="so-eyebrow">SHOPHOP SELLER</span><h2 id="sellerOrderDialogLabel">Order Overview</h2></div>
                <button type="button" class="so-dialog-close" data-so-close aria-label="Close order details"><x-lucide-x /></button>
            </header>
            <div class="so-dialog-body" id="sellerOrderDialogBody"></div>
            <footer class="so-dialog-footer"><button type="button" class="so-button so-button-secondary" data-so-close>Close</button></footer>
        </div>
    </dialog>
</section>
@push('scripts')
<script defer src="{{ asset('js/shophop-seller-order-directory.js') }}?v=20261104a"></script>
@endpush
