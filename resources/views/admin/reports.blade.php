@extends('admin.layout')

@section('title', 'Reports')

@section('content')
    @php
        $peso = fn ($n) => '&#8369;'.number_format((float) $n, 2);
        $exportQuery = request()->query();
        $exportQuery = array_merge($exportQuery, [
            'range' => $range,
            'from' => $from->format('Y-m-d'),
            'to' => $to->format('Y-m-d'),
        ]);
        $maxCount = max(1, collect($trend)->max('count'));
        $statusLabel = fn ($s) => ucwords(str_replace(['_', '-'], ' ', (string) $s));
    @endphp

    <section id="adminReports" aria-labelledby="reportsTitle" class="space-y-6">

        {{-- HEADER + RANGE FILTER --}}
        <div class="flex flex-col xl:flex-row xl:items-end xl:justify-between gap-4">
            <div>
                <h1 id="reportsTitle" class="text-2xl font-bold text-navy">Reports</h1>
                <p class="text-sm text-slate-500 mt-1">
                    Live figures from the ShopHop database &middot;
                    {{ $from->format('M d, Y') }} &ndash; {{ $to->format('M d, Y') }}
                </p>
            </div>

            <form method="GET" class="flex flex-col sm:flex-row sm:items-center gap-2" id="rangeForm">
                <select name="range" id="rangeSelect"
                    class="text-sm rounded-xl border border-slate-200 px-3 py-2.5 bg-white text-slate-600 font-medium">
                    <option value="today" @selected($range === 'today')>Today</option>
                    <option value="7d" @selected($range === '7d')>Last 7 days</option>
                    <option value="30d" @selected($range === '30d')>Last 30 days</option>
                    <option value="month" @selected($range === 'month')>This month</option>
                    <option value="custom" @selected($range === 'custom')>Custom range</option>
                </select>
                <div id="customDates" class="{{ $range === 'custom' ? 'flex' : 'hidden' }} items-center gap-2">
                    <input type="date" name="from" value="{{ $from->format('Y-m-d') }}"
                        class="text-sm rounded-xl border border-slate-200 px-3 py-2 bg-white text-slate-600">
                    <span class="text-xs text-slate-400">to</span>
                    <input type="date" name="to" value="{{ $to->format('Y-m-d') }}"
                        class="text-sm rounded-xl border border-slate-200 px-3 py-2 bg-white text-slate-600">
                </div>
                <button type="submit" class="sh-btn h-10 px-4 rounded-xl text-xs font-semibold text-white bg-navy hover:opacity-90 transition">Apply</button>
            </form>
        </div>

        {{-- HEADLINE CARDS --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
            <div class="ops-panel bg-white border border-slate-200 rounded-2xl p-4">
                <p class="text-xs text-slate-400 font-medium">Orders placed</p>
                <p class="text-2xl font-bold text-navy mt-1">{{ number_format($sales['total']) }}</p>
                <p class="text-[11px] text-slate-400 mt-1">{{ $sales['completed'] }} completed &middot; {{ $sales['cancelled'] }} cancelled &middot; {{ $sales['returned'] }} returned</p>
            </div>
            <div class="ops-panel bg-white border border-slate-200 rounded-2xl p-4">
                <p class="text-xs text-slate-400 font-medium">Completed order value</p>
                <p class="text-2xl font-bold text-navy mt-1">{!! $peso($sales['completed_value']) !!}</p>
                <p class="text-[11px] text-slate-400 mt-1">Avg. {!! $peso($sales['avg_order']) !!} per order</p>
            </div>
            <div class="ops-panel bg-white border border-slate-200 rounded-2xl p-4">
                <p class="text-xs text-slate-400 font-medium">ShopHop commission</p>
                <p class="text-2xl font-bold text-navy mt-1">{!! $peso($finance->commission) !!}</p>
                <p class="text-[11px] text-slate-400 mt-1">Platform net {!! $peso($finance->platform_net) !!}</p>
            </div>
            <div class="ops-panel bg-white border border-slate-200 rounded-2xl p-4">
                <p class="text-xs text-slate-400 font-medium">COD received by ShopHop</p>
                <p class="text-2xl font-bold text-navy mt-1">{!! $peso($codReceived->amt) !!}</p>
                <p class="text-[11px] text-slate-400 mt-1">{{ $codReceived->n }} order(s) confirmed by Admin</p>
            </div>
        </div>

        {{-- TREND --}}
        <div class="ops-panel bg-white border border-slate-200 rounded-2xl p-5">
            <div class="flex items-center justify-between gap-3 mb-4">
                <div>
                    <h2 class="font-semibold text-navy text-sm">Orders over time</h2>
                    <p class="text-[11px] text-slate-400 mt-0.5">Number of orders placed per {{ count($trend) > 0 && str_contains($trend[0]['label'], ' ') && strlen($trend[0]['label']) > 6 ? 'month' : 'day' }}.</p>
                </div>
                <a href="{{ route('admin.reports.export', ['section' => 'orders'] + $exportQuery) }}"
                   class="text-xs font-semibold text-mint-dark hover:underline">Export CSV</a>
            </div>
            @if ($sales['total'] === 0)
                <p class="text-sm text-slate-400 py-8 text-center">No orders in this period.</p>
            @else
                <div class="overflow-x-auto">
                    <div class="flex items-end gap-1 h-40 min-w-max">
                        @foreach ($trend as $p)
                            <div class="flex flex-col items-center justify-end h-full w-6 group" title="{{ $p['label'] }}: {{ $p['count'] }} order(s), {{ number_format($p['value'], 2) }}">
                                <div class="w-4 rounded-t bg-mint-dark/80 group-hover:bg-mint-dark transition"
                                     style="height: {{ $p['count'] > 0 ? max(4, round($p['count'] / $maxCount * 100)) : 0 }}%"></div>
                            </div>
                        @endforeach
                    </div>
                    <div class="flex justify-between text-[10px] text-slate-400 mt-2 min-w-max">
                        <span>{{ $trend[0]['label'] }}</span>
                        <span>{{ $trend[count($trend) - 1]['label'] }}</span>
                    </div>
                </div>
            @endif
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">

            {{-- ORDER STATUS --}}
            <div class="ops-panel bg-white border border-slate-200 rounded-2xl overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100">
                    <h2 class="font-semibold text-navy text-sm">Orders by status</h2>
                </div>
                <table class="w-full text-sm">
                    <thead><tr class="text-[11px] uppercase tracking-wide text-slate-400 text-left">
                        <th class="px-5 py-2 font-semibold">Status</th>
                        <th class="px-5 py-2 font-semibold text-right">Orders</th>
                        <th class="px-5 py-2 font-semibold text-right">Value</th>
                    </tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($statusRows as $r)
                            <tr>
                                <td class="px-5 py-2.5 text-navy">{{ $statusLabel($r->status) }}</td>
                                <td class="px-5 py-2.5 text-right text-slate-600">{{ number_format($r->c) }}</td>
                                <td class="px-5 py-2.5 text-right text-slate-600">{!! $peso($r->s) !!}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-5 py-8 text-center text-slate-400 text-sm">No orders in this period.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- FINANCE --}}
            <div class="ops-panel bg-white border border-slate-200 rounded-2xl overflow-hidden">
                <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
                    <div>
                        <h2 class="font-semibold text-navy text-sm">Finance breakdown</h2>
                        <p class="text-[11px] text-slate-400 mt-0.5">Completed orders only &middot; {{ $finance->n }} order(s)</p>
                    </div>
                    <a href="{{ route('admin.reports.export', ['section' => 'finance'] + $exportQuery) }}"
                       class="text-xs font-semibold text-mint-dark hover:underline">Export CSV</a>
                </div>
                <dl class="divide-y divide-slate-100 text-sm">
                    @foreach ([
                        'Merchandise' => $finance->merchandise,
                        'Shipping fees' => $finance->shipping_fee,
                        'ShopHop commission' => $finance->commission,
                        'Seller earnings' => $finance->seller_earnings,
                        'Logistics earnings' => $finance->logistics_earnings,
                        'Platform net' => $finance->platform_net,
                    ] as $label => $amount)
                        <div class="flex items-center justify-between px-5 py-2.5">
                            <dt class="text-slate-500">{{ $label }}</dt>
                            <dd class="font-semibold text-navy">{!! $peso($amount) !!}</dd>
                        </div>
                    @endforeach
                </dl>
            </div>

            {{-- COD --}}
            <div class="ops-panel bg-white border border-slate-200 rounded-2xl overflow-hidden">
                <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
                    <div>
                        <h2 class="font-semibold text-navy text-sm">COD custody</h2>
                        <p class="text-[11px] text-slate-400 mt-0.5">Settlements created in this period</p>
                    </div>
                    <a href="{{ route('admin.reports.export', ['section' => 'cod'] + $exportQuery) }}"
                       class="text-xs font-semibold text-mint-dark hover:underline">Export CSV</a>
                </div>
                <table class="w-full text-sm">
                    <thead><tr class="text-[11px] uppercase tracking-wide text-slate-400 text-left">
                        <th class="px-5 py-2 font-semibold">Status</th>
                        <th class="px-5 py-2 font-semibold text-right">Count</th>
                        <th class="px-5 py-2 font-semibold text-right">Expected</th>
                        <th class="px-5 py-2 font-semibold text-right">Collected</th>
                    </tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($codByStatus as $r)
                            <tr>
                                <td class="px-5 py-2.5 text-navy">{{ $statusLabel($r->status) }}</td>
                                <td class="px-5 py-2.5 text-right text-slate-600">{{ number_format($r->c) }}</td>
                                <td class="px-5 py-2.5 text-right text-slate-600">{!! $peso($r->expected) !!}</td>
                                <td class="px-5 py-2.5 text-right text-slate-600">{!! $peso($r->collected) !!}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-5 py-8 text-center text-slate-400 text-sm">No COD settlements in this period.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="px-5 py-3 bg-amber-100/40 border-t border-slate-100 text-xs text-slate-600">
                    Right now (not date-filtered): <strong>{{ $codAwaiting->n }}</strong> remittance(s) worth
                    <strong>{!! $peso($codAwaiting->amt) !!}</strong> are waiting for Admin confirmation.
                </div>
            </div>

            {{-- RETURNS --}}
            <div class="ops-panel bg-white border border-slate-200 rounded-2xl overflow-hidden">
                <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
                    <div>
                        <h2 class="font-semibold text-navy text-sm">Returns &amp; refunds</h2>
                        <p class="text-[11px] text-slate-400 mt-0.5">Return cases created in this period</p>
                    </div>
                    <a href="{{ route('admin.reports.export', ['section' => 'returns'] + $exportQuery) }}"
                       class="text-xs font-semibold text-mint-dark hover:underline">Export CSV</a>
                </div>
                <table class="w-full text-sm">
                    <thead><tr class="text-[11px] uppercase tracking-wide text-slate-400 text-left">
                        <th class="px-5 py-2 font-semibold">Status</th>
                        <th class="px-5 py-2 font-semibold text-right">Cases</th>
                    </tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($returnsByStatus as $r)
                            <tr>
                                <td class="px-5 py-2.5 text-navy">{{ $statusLabel($r->status) }}</td>
                                <td class="px-5 py-2.5 text-right text-slate-600">{{ number_format($r->c) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="px-5 py-8 text-center text-slate-400 text-sm">No return cases in this period.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="px-5 py-3 border-t border-slate-100 text-xs text-slate-600 space-y-1">
                    <p>Refunds paid in this period: <strong>{{ $refunds->n }}</strong> totaling <strong>{!! $peso($refunds->amt) !!}</strong></p>
                    <p>Awaiting refund right now: <strong>{{ $refundPending }}</strong> case(s)</p>
                </div>
            </div>

            {{-- REGISTRATIONS --}}
            <div class="ops-panel bg-white border border-slate-200 rounded-2xl overflow-hidden">
                <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
                    <div>
                        <h2 class="font-semibold text-navy text-sm">New registrations</h2>
                        <p class="text-[11px] text-slate-400 mt-0.5">Email-verified applicants only (same as the review queue)</p>
                    </div>
                    <a href="{{ route('admin.reports.export', ['section' => 'registrations'] + $exportQuery) }}"
                       class="text-xs font-semibold text-mint-dark hover:underline">Export CSV</a>
                </div>
                <table class="w-full text-sm">
                    <thead><tr class="text-[11px] uppercase tracking-wide text-slate-400 text-left">
                        <th class="px-5 py-2 font-semibold">Role</th>
                        <th class="px-5 py-2 font-semibold text-right">Pending</th>
                        <th class="px-5 py-2 font-semibold text-right">Approved</th>
                        <th class="px-5 py-2 font-semibold text-right">Rejected</th>
                        <th class="px-5 py-2 font-semibold text-right">Total</th>
                    </tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($registrations as $role => $row)
                            <tr>
                                <td class="px-5 py-2.5 text-navy">{{ ucfirst($role) }}</td>
                                <td class="px-5 py-2.5 text-right text-slate-600">{{ $row['pending'] }}</td>
                                <td class="px-5 py-2.5 text-right text-slate-600">{{ $row['approved'] }}</td>
                                <td class="px-5 py-2.5 text-right text-slate-600">{{ $row['rejected'] }}</td>
                                <td class="px-5 py-2.5 text-right font-semibold text-navy">{{ $row['total'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- DISPUTES --}}
            <div class="ops-panel bg-white border border-slate-200 rounded-2xl overflow-hidden">
                <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
                    <div>
                        <h2 class="font-semibold text-navy text-sm">Complaints &amp; disputes</h2>
                        <p class="text-[11px] text-slate-400 mt-0.5">Cases filed in this period</p>
                    </div>
                    @if ($disputes)
                        <a href="{{ route('admin.reports.export', ['section' => 'disputes'] + $exportQuery) }}"
                           class="text-xs font-semibold text-mint-dark hover:underline">Export CSV</a>
                    @endif
                </div>
                @if ($disputes === null)
                    <p class="px-5 py-8 text-center text-sm text-slate-400">The disputes table does not exist yet. Run <code>php artisan migrate</code>.</p>
                @else
                    <dl class="divide-y divide-slate-100 text-sm">
                        <div class="flex items-center justify-between px-5 py-2.5"><dt class="text-slate-500">Filed</dt><dd class="font-semibold text-navy">{{ $disputes['filed'] }}</dd></div>
                        @foreach ($disputes['by_status'] as $status => $count)
                            <div class="flex items-center justify-between px-5 py-2.5"><dt class="text-slate-500 pl-3">{{ $statusLabel($status) }}</dt><dd class="text-slate-600">{{ $count }}</dd></div>
                        @endforeach
                        <div class="flex items-center justify-between px-5 py-2.5"><dt class="text-slate-500">Closed in this period</dt><dd class="font-semibold text-navy">{{ $disputes['closed_in_range'] }}</dd></div>
                        <div class="flex items-center justify-between px-5 py-2.5"><dt class="text-slate-500">Avg. resolution time</dt><dd class="font-semibold text-navy">{{ $disputes['avg_hours'] === null ? 'N/A' : $disputes['avg_hours'].' h' }}</dd></div>
                        <div class="flex items-center justify-between px-5 py-2.5"><dt class="text-slate-500">Open right now</dt><dd class="font-semibold text-navy">{{ $disputes['open_now'] }}</dd></div>
                    </dl>
                @endif
            </div>

            {{-- TOP SELLERS --}}
            <div class="ops-panel bg-white border border-slate-200 rounded-2xl overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100">
                    <h2 class="font-semibold text-navy text-sm">Top sellers</h2>
                    <p class="text-[11px] text-slate-400 mt-0.5">By completed order value</p>
                </div>
                <table class="w-full text-sm">
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($topSellers as $s)
                            <tr>
                                <td class="px-5 py-2.5 text-navy">{{ $s->business_name ?: 'Seller #'.$s->seller_id }}</td>
                                <td class="px-5 py-2.5 text-right text-slate-500">{{ $s->n }} order(s)</td>
                                <td class="px-5 py-2.5 text-right font-semibold text-navy">{!! $peso($s->amt) !!}</td>
                            </tr>
                        @empty
                            <tr><td class="px-5 py-8 text-center text-slate-400 text-sm">No completed orders in this period.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- TOP PRODUCTS --}}
            <div class="ops-panel bg-white border border-slate-200 rounded-2xl overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100">
                    <h2 class="font-semibold text-navy text-sm">Top products</h2>
                    <p class="text-[11px] text-slate-400 mt-0.5">By units sold in completed orders</p>
                </div>
                <table class="w-full text-sm">
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($topProducts as $p)
                            <tr>
                                <td class="px-5 py-2.5 text-navy truncate max-w-[16rem]">{{ $p->name ?: 'Product #'.$p->product_id }}</td>
                                <td class="px-5 py-2.5 text-right text-slate-500">{{ number_format($p->qty) }} sold</td>
                                <td class="px-5 py-2.5 text-right font-semibold text-navy">{!! $peso($p->amt) !!}</td>
                            </tr>
                        @empty
                            <tr><td class="px-5 py-8 text-center text-slate-400 text-sm">No completed orders in this period.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>

        <p class="text-[11px] text-slate-400">
            Order, finance, and leader figures use the order&rsquo;s placed date. COD received and refunds use the date they were confirmed or paid.
            Closing a dispute or showing a report does not move any money.
        </p>
    </section>

    <script>
        (function () {
            const select = document.getElementById('rangeSelect');
            const custom = document.getElementById('customDates');
            select.addEventListener('change', function () {
                if (select.value === 'custom') {
                    custom.classList.remove('hidden');
                    custom.classList.add('flex');
                } else {
                    document.getElementById('rangeForm').submit();
                }
            });
        })();
    </script>
@endsection