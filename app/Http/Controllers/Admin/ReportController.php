<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Buyer\Order\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    private const ROLES = ['buyer', 'seller', 'logistics'];

    public function index(Request $request): View
    {
        [$from, $to, $range] = $this->resolveRange($request);

        // ---------- Orders / sales ----------
        $ordersInRange = fn () => DB::table('orders')->whereBetween('created_at', [$from, $to]);

        $statusRows = $ordersInRange()
            ->select('status', DB::raw('COUNT(*) as c'), DB::raw('COALESCE(SUM(total_amount),0) as s'))
            ->groupBy('status')
            ->orderByDesc('c')
            ->get();

        $totalOrders = (int) $statusRows->sum('c');
        $completed = $statusRows->firstWhere('status', Order::STATUS_COMPLETED);
        $cancelled = $statusRows->firstWhere('status', Order::STATUS_CANCELLED);
        $returned = $statusRows->firstWhere('status', Order::STATUS_RETURNED);

        $sales = [
            'total' => $totalOrders,
            'completed' => (int) ($completed->c ?? 0),
            'completed_value' => (float) ($completed->s ?? 0),
            'cancelled' => (int) ($cancelled->c ?? 0),
            'returned' => (int) ($returned->c ?? 0),
            'avg_order' => ($completed->c ?? 0) > 0 ? (float) $completed->s / (int) $completed->c : 0.0,
        ];

        $trend = $this->buildTrend($ordersInRange(), $from, $to);

        // ---------- Finance (completed orders only, same basis as Commission page) ----------
        $finance = DB::table('order_financials as f')
            ->join('orders as o', 'o.id', '=', 'f.order_id')
            ->where('o.status', Order::STATUS_COMPLETED)
            ->whereBetween('o.created_at', [$from, $to])
            ->selectRaw('COUNT(*) as n,
                COALESCE(SUM(f.merchandise),0) as merchandise,
                COALESCE(SUM(f.shipping_fee),0) as shipping_fee,
                COALESCE(SUM(f.commission),0) as commission,
                COALESCE(SUM(f.seller_earnings),0) as seller_earnings,
                COALESCE(SUM(f.logistics_earnings),0) as logistics_earnings,
                COALESCE(SUM(f.platform_net),0) as platform_net')
            ->first();

        // ---------- COD custody ----------
        $codByStatus = DB::table('cod_settlements')
            ->whereBetween('created_at', [$from, $to])
            ->select('status', DB::raw('COUNT(*) as c'),
                DB::raw('COALESCE(SUM(expected_amount),0) as expected'),
                DB::raw('COALESCE(SUM(collected_amount),0) as collected'))
            ->groupBy('status')
            ->get();

        $codReceived = DB::table('cod_settlements')
            ->whereBetween('admin_received_at', [$from, $to])
            ->selectRaw('COUNT(*) as n, COALESCE(SUM(admin_received_amount),0) as amt')
            ->first();

        // Snapshot (all-time), not tied to the date range: money still waiting for Admin.
        $codAwaiting = DB::table('cod_settlements')
            ->whereNotNull('admin_remitted_at')
            ->whereNull('admin_received_at')
            ->selectRaw('COUNT(*) as n, COALESCE(SUM(expected_amount),0) as amt')
            ->first();

        // ---------- Returns & refunds ----------
        $returnsByStatus = DB::table('return_cases')
            ->whereBetween('created_at', [$from, $to])
            ->select('status', DB::raw('COUNT(*) as c'))
            ->groupBy('status')
            ->get();

        $refunds = DB::table('return_cases')
            ->whereNotNull('refunded_at')
            ->whereBetween('refunded_at', [$from, $to])
            ->selectRaw('COUNT(*) as n, COALESCE(SUM(refund_amount),0) as amt')
            ->first();

        $refundPending = DB::table('return_cases')
            ->where('status', 'seller_received')
            ->whereNull('refunded_at')
            ->count();

        // ---------- Registrations (same eligible set as the registration queue) ----------
        $regRows = DB::table('users')
            ->whereIn('account_type', self::ROLES)
            ->whereNotNull('email_verified_at')
            ->whereBetween('created_at', [$from, $to])
            ->select('account_type', 'status', DB::raw('COUNT(*) as c'))
            ->groupBy('account_type', 'status')
            ->get();

        $registrations = [];
        foreach (self::ROLES as $role) {
            $row = ['pending' => 0, 'approved' => 0, 'rejected' => 0, 'other' => 0];
            foreach ($regRows->where('account_type', $role) as $r) {
                $key = array_key_exists($r->status, $row) ? $r->status : 'other';
                $row[$key] += (int) $r->c;
            }
            $row['total'] = array_sum($row);
            $registrations[$role] = $row;
        }

        // ---------- Disputes (guarded in case the migration has not been run yet) ----------
        $disputes = null;
        if (Schema::hasTable('disputes')) {
            $dRows = DB::table('disputes')
                ->whereBetween('created_at', [$from, $to])
                ->select('status', DB::raw('COUNT(*) as c'))
                ->groupBy('status')
                ->pluck('c', 'status');

            $resolved = DB::table('disputes')
                ->whereNotNull('resolved_at')
                ->whereBetween('resolved_at', [$from, $to])
                ->get(['created_at', 'resolved_at']);

            $avg = $resolved->avg(fn ($d) => Carbon::parse($d->created_at)->diffInHours(Carbon::parse($d->resolved_at)));

            $disputes = [
                'filed' => (int) $dRows->sum(),
                'by_status' => $dRows,
                'closed_in_range' => $resolved->count(),
                'avg_hours' => $avg === null ? null : round($avg, 1),
                'open_now' => DB::table('disputes')->whereNotIn('status', ['resolved', 'rejected'])->count(),
            ];
        }

        // ---------- Leaders ----------
        $topSellers = DB::table('orders as o')
            ->leftJoin('sellers as s', 's.id', '=', 'o.seller_id')
            ->where('o.status', Order::STATUS_COMPLETED)
            ->whereBetween('o.created_at', [$from, $to])
            ->selectRaw('o.seller_id, s.business_name, COUNT(*) as n, COALESCE(SUM(o.total_amount),0) as amt')
            ->groupBy('o.seller_id', 's.business_name')
            ->orderByDesc('amt')
            ->limit(5)
            ->get();

        $topProducts = DB::table('order_items as i')
            ->join('orders as o', 'o.id', '=', 'i.order_id')
            ->leftJoin('products as p', 'p.id', '=', 'i.product_id')
            ->where('o.status', Order::STATUS_COMPLETED)
            ->whereBetween('o.created_at', [$from, $to])
            ->selectRaw('i.product_id, p.name, SUM(i.quantity) as qty, COALESCE(SUM(i.quantity * i.price),0) as amt')
            ->groupBy('i.product_id', 'p.name')
            ->orderByDesc('qty')
            ->limit(5)
            ->get();

        return view('admin.reports', [
            'range' => $range,
            'from' => $from,
            'to' => $to,
            'sales' => $sales,
            'statusRows' => $statusRows,
            'trend' => $trend,
            'finance' => $finance,
            'codByStatus' => $codByStatus,
            'codReceived' => $codReceived,
            'codAwaiting' => $codAwaiting,
            'returnsByStatus' => $returnsByStatus,
            'refunds' => $refunds,
            'refundPending' => $refundPending,
            'registrations' => $registrations,
            'disputes' => $disputes,
            'topSellers' => $topSellers,
            'topProducts' => $topProducts,
        ]);
    }

    public function export(Request $request, string $section): StreamedResponse
    {
        [$from, $to] = $this->resolveRange($request);

        switch ($section) {
            case 'orders':
                $headers = ['Order ID', 'Placed At', 'Buyer ID', 'Seller ID', 'Status', 'Payment', 'Total Amount', 'Shipping Fee', 'Tracking Code'];
                $query = DB::table('orders')->whereBetween('created_at', [$from, $to])->orderBy('id')
                    ->select('id', 'created_at', 'buyer_id', 'seller_id', 'status', 'payment_method', 'total_amount', 'shipping_fee', 'tracking_code');
                break;

            case 'finance':
                $headers = ['Order ID', 'Placed At', 'Merchandise', 'Shipping Fee', 'Commission', 'Seller Earnings', 'Logistics Earnings', 'Platform Net', 'Settlement Hold'];
                $query = DB::table('order_financials as f')->join('orders as o', 'o.id', '=', 'f.order_id')
                    ->where('o.status', Order::STATUS_COMPLETED)->whereBetween('o.created_at', [$from, $to])->orderBy('f.id')
                    ->select('f.order_id', 'o.created_at', 'f.merchandise', 'f.shipping_fee', 'f.commission', 'f.seller_earnings', 'f.logistics_earnings', 'f.platform_net', 'f.settlement_hold');
                break;

            case 'cod':
                $headers = ['ID', 'Order ID', 'Status', 'Expected', 'Collected', 'Collected At', 'Remitted At', 'Admin Remitted At', 'Admin Received Amount', 'Admin Received At'];
                $query = DB::table('cod_settlements')->whereBetween('created_at', [$from, $to])->orderBy('id')
                    ->select('id', 'order_id', 'status', 'expected_amount', 'collected_amount', 'collected_at', 'remitted_at', 'admin_remitted_at', 'admin_received_amount', 'admin_received_at');
                break;

            case 'returns':
                $headers = ['ID', 'Order ID', 'Status', 'Reason', 'Refund Status', 'Refund Amount', 'Refunded At', 'Requested At'];
                $query = DB::table('return_cases')->whereBetween('created_at', [$from, $to])->orderBy('id')
                    ->select('id', 'order_id', 'status', 'reason', 'refund_status', 'refund_amount', 'refunded_at', 'created_at');
                break;

            case 'registrations':
                $headers = ['User ID', 'Email', 'Role', 'Status', 'Email Verified At', 'Registered At'];
                $query = DB::table('users')->whereIn('account_type', self::ROLES)->whereNotNull('email_verified_at')
                    ->whereBetween('created_at', [$from, $to])->orderBy('id')
                    ->select('id', 'email', 'account_type', 'status', 'email_verified_at', 'created_at');
                break;

            case 'disputes':
                abort_unless(Schema::hasTable('disputes'), 404);
                $headers = ['Case No', 'Type', 'Priority', 'Status', 'Subject', 'Order ID', 'Filed At', 'Resolved At', 'Outcome'];
                $query = DB::table('disputes')->whereBetween('created_at', [$from, $to])->orderBy('id')
                    ->select('case_no', 'type', 'priority', 'status', 'subject', 'order_id', 'created_at', 'resolved_at', 'resolution_outcome');
                break;

            default:
                abort(404);
        }

        $filename = sprintf('shophop-%s-%s_to_%s.csv', $section, $from->format('Ymd'), $to->format('Ymd'));

        return response()->streamDownload(function () use ($headers, $query) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM so Excel reads peso signs and accents correctly
            fputcsv($out, $headers);

            foreach ($query->cursor() as $row) {
                fputcsv($out, array_map([$this, 'csvCell'], (array) $row));
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** Neutralise spreadsheet formula injection in text cells. */
    private function csvCell(mixed $value): mixed
    {
        if (is_string($value) && $value !== '' && ! is_numeric($value)
            && in_array($value[0], ['=', '+', '-', '@', "\t", "\r"], true)) {
            return "'".$value;
        }

        return $value;
    }

    /** @return array{0: Carbon, 1: Carbon, 2: string} */
    private function resolveRange(Request $request): array
    {
        $range = (string) $request->get('range', '30d');
        $now = now();

        switch ($range) {
            case 'today':
                return [$now->copy()->startOfDay(), $now->copy()->endOfDay(), 'today'];
            case '7d':
                return [$now->copy()->subDays(6)->startOfDay(), $now->copy()->endOfDay(), '7d'];
            case 'month':
                return [$now->copy()->startOfMonth(), $now->copy()->endOfDay(), 'month'];
            case 'custom':
                try {
                    $from = Carbon::parse((string) $request->get('from'))->startOfDay();
                    $to = Carbon::parse((string) $request->get('to'))->endOfDay();
                    if ($from->lte($to) && $from->diffInDays($to) <= 366) {
                        return [$from, $to, 'custom'];
                    }
                } catch (\Throwable $e) {
                    // fall through to default
                }
                break;
        }

        return [$now->copy()->subDays(29)->startOfDay(), $now->copy()->endOfDay(), '30d'];
    }

    /**
     * Orders per day (or per month for long ranges), with empty periods filled in.
     * Grouping happens in PHP so it works on SQLite and MySQL alike.
     */
    private function buildTrend($ordersQuery, Carbon $from, Carbon $to): array
    {
        $daily = $ordersQuery
            ->selectRaw('DATE(created_at) as d, COUNT(*) as c, COALESCE(SUM(total_amount),0) as s')
            ->groupBy(DB::raw('DATE(created_at)'))
            ->get()
            ->keyBy('d');

        $monthly = $from->diffInDays($to) > 62;
        $points = [];

        if ($monthly) {
            $cursor = $from->copy()->startOfMonth();
            while ($cursor->lte($to)) {
                $points[$cursor->format('Y-m')] = ['label' => $cursor->format('M Y'), 'count' => 0, 'value' => 0.0];
                $cursor->addMonth();
            }
            foreach ($daily as $d => $row) {
                $key = substr((string) $d, 0, 7);
                if (isset($points[$key])) {
                    $points[$key]['count'] += (int) $row->c;
                    $points[$key]['value'] += (float) $row->s;
                }
            }
        } else {
            $cursor = $from->copy()->startOfDay();
            while ($cursor->lte($to)) {
                $key = $cursor->format('Y-m-d');
                $points[$key] = [
                    'label' => $cursor->format('M d'),
                    'count' => (int) ($daily[$key]->c ?? 0),
                    'value' => (float) ($daily[$key]->s ?? 0),
                ];
                $cursor->addDay();
            }
        }

        return array_values($points);
    }
}