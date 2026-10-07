<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Buyer\Order\Order;
use App\Models\Seller;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    private const COMMISSION_RATE = 10.0;

    public function index(Request $request): View
    {
        return view('seller.reports', $this->build($request));
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $data = $this->build($request);
        $filename = 'shophop-seller-report-'.$data['dateFrom'].'-to-'.$data['dateTo'].'.csv';

        return response()->streamDownload(function () use ($data) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['ShopHop Seller Report', $data['periodLabel']]);
            fputcsv($out, ['Gross completed sales', number_format($data['totalSales'], 2, '.', '')]);
            fputcsv($out, ['Orders placed', $data['totalOrders']]);
            fputcsv($out, ['Completed orders', $data['completedOrders']]);
            fputcsv($out, ['Failed delivery reports', $data['failedDeliveryReports']]);
            fputcsv($out, ['Platform commission rate', self::COMMISSION_RATE.'%']);
            fputcsv($out, []);
            fputcsv($out, ['Order', 'Date', 'Buyer', 'Items', 'Gross', 'Commission', 'Net', 'Status']);
            foreach ($data['recentTransactions'] as $row) {
                fputcsv($out, [
                    $row['order_id'], $row['date'], $row['buyer'], $row['items'],
                    number_format($row['gross'], 2, '.', ''),
                    number_format($row['commission'], 2, '.', ''),
                    number_format($row['net'], 2, '.', ''), $row['status'],
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function build(Request $request): array
    {
        $seller = $this->seller($request);
        [$from, $to] = $this->range($request);
        $days = $from->diffInDays($to) + 1;
        $previousTo = $from->copy()->subDay()->endOfDay();
        $previousFrom = $previousTo->copy()->subDays($days - 1)->startOfDay();

        $orders = $this->orders($seller, $from, $to);
        $previousOrdersCollection = $this->orders($seller, $previousFrom, $previousTo);
        $completed = $orders->where('status', Order::STATUS_COMPLETED);
        $previousCompleted = $previousOrdersCollection->where('status', Order::STATUS_COMPLETED);

        $totalSales = (float) $completed->sum(fn (Order $order) => (float) $order->total_amount);
        $previousSales = (float) $previousCompleted->sum(fn (Order $order) => (float) $order->total_amount);
        $totalOrders = $orders->count();
        $previousOrders = $previousOrdersCollection->count();
        $completedOrders = $completed->count();
        $cancelledOrders = $orders->where('status', Order::STATUS_CANCELLED)->count();
        $failedDeliveryReports = $orders->sum(
            fn (Order $order) => $order->delivery?->failureReports?->count() ?? 0
        );

        $dailySales = collect(CarbonPeriod::create($from->copy()->startOfDay(), $to->copy()->startOfDay()))->map(function ($date) use ($completed) {
            $dayOrders = $completed->filter(fn (Order $order) => $order->created_at->isSameDay($date));
            return [
                'label' => $date->format('D'),
                'date' => $date->format('M j'),
                'amount' => (float) $dayOrders->sum(fn (Order $order) => (float) $order->total_amount),
                'orders' => $dayOrders->count(),
            ];
        })->values();

        $completedItems = $completed->flatMap(fn (Order $order) => $order->items->map(fn ($item) => ['order' => $order, 'item' => $item]));
        $topProducts = $completedItems->groupBy(fn ($row) => $row['item']->product_id)->map(function (Collection $rows) use ($totalSales) {
            $first = $rows->first()['item'];
            $revenue = (float) $rows->sum(fn ($row) => (float) $row['item']->price * (int) $row['item']->quantity);
            return [
                'name' => $first->product?->name ?? 'Unavailable product',
                'variant' => 'All variants',
                'sku' => $first->product?->sku ?? '—',
                'sold' => (int) $rows->sum(fn ($row) => (int) $row['item']->quantity),
                'orders' => $rows->pluck('order.id')->unique()->count(),
                'revenue' => $revenue,
                'share' => $totalSales > 0 ? ($revenue / $totalSales) * 100 : 0,
                'image' => $first->product?->image ? asset('storage/'.ltrim($first->product->image, '/')) : asset('images/placeholder-product.jpg'),
            ];
        })->sortByDesc('revenue')->take(8)->values();

        $categorySales = $completedItems->groupBy(fn ($row) => $row['item']->product?->category ?: 'Uncategorized')->map(function (Collection $rows, string $category) {
            return [
                'category' => $category,
                'revenue' => (float) $rows->sum(fn ($row) => (float) $row['item']->price * (int) $row['item']->quantity),
                'orders' => $rows->pluck('order.id')->unique()->count(),
            ];
        })->sortByDesc('revenue')->values();

        $paymentBreakdown = $completed->groupBy('payment_method')->map(function (Collection $rows, string $method) {
            return [
                'method' => $method === 'cod' ? 'Cash on Delivery' : 'Online Payment',
                'orders' => $rows->count(),
                'amount' => (float) $rows->sum(fn (Order $order) => (float) $order->total_amount),
            ];
        })->values();

        $recentTransactions = $orders->sortByDesc('created_at')->take(20)->map(function (Order $order) {
            $isCompleted = $order->status === Order::STATUS_COMPLETED;
            $gross = (float) $order->total_amount;
            $commission = $isCompleted ? $gross * (self::COMMISSION_RATE / 100) : 0.0;
            return [
                'order_id' => 'SHP-'.str_pad((string) $order->id, 6, '0', STR_PAD_LEFT),
                'date' => $order->created_at->format('M j, Y · g:i A'),
                'buyer' => trim(($order->buyer?->first_name ?? '').' '.($order->buyer?->last_name ?? '')) ?: 'Buyer',
                'items' => (int) $order->items->sum('quantity'),
                'gross' => $gross,
                'commission' => $commission,
                'net' => $gross - $commission,
                'status' => $order->statusLabel(),
            ];
        })->values();

        return [
            'dateFrom' => $from->format('Y-m-d'),
            'dateTo' => $to->format('Y-m-d'),
            'periodLabel' => $from->format('M j, Y').' – '.$to->format('M j, Y'),
            'previousPeriodLabel' => $previousFrom->format('M j').' – '.$previousTo->format('M j, Y'),
            'totalSales' => $totalSales,
            'previousSales' => $previousSales,
            'totalOrders' => $totalOrders,
            'previousOrders' => $previousOrders,
            'completedOrders' => $completedOrders,
            'cancelledOrders' => $cancelledOrders,
            'failedDeliveryReports' => $failedDeliveryReports,
            'commissionRate' => self::COMMISSION_RATE,
            'dailySales' => $dailySales,
            'topProducts' => $topProducts,
            'categorySales' => $categorySales,
            'orderStatusBreakdown' => collect([
                ['status' => 'Completed', 'count' => $completedOrders, 'class' => 'bg-teal', 'text' => 'text-teal-dark'],
                ['status' => 'Cancelled', 'count' => $cancelledOrders, 'class' => 'bg-coral', 'text' => 'text-coral'],
                ['status' => 'Delivery Failure Reports', 'count' => $failedDeliveryReports, 'class' => 'bg-red-400', 'text' => 'text-red-500'],
            ]),
            'paymentBreakdown' => $paymentBreakdown,
            'recentTransactions' => $recentTransactions,
        ];
    }

    private function orders(Seller $seller, Carbon $from, Carbon $to): Collection
    {
        return Order::with(['buyer', 'items.product', 'delivery.failureReports'])
            ->where('seller_id', $seller->id)
            ->whereBetween('created_at', [$from, $to])
            ->get();
    }

    private function range(Request $request): array
    {
        $validated = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
        ]);
        $from = isset($validated['date_from']) ? Carbon::parse($validated['date_from'])->startOfDay() : now()->subDays(6)->startOfDay();
        $to = isset($validated['date_to']) ? Carbon::parse($validated['date_to'])->endOfDay() : now()->endOfDay();
        if ($from->gt($to)) {
            throw ValidationException::withMessages(['date_from' => 'The report start date must be on or before the end date.']);
        }
        if ($from->diffInDays($to) > 366) {
            throw ValidationException::withMessages(['date_from' => 'Choose a report range of 366 days or less.']);
        }
        return [$from, $to];
    }

    private function seller(Request $request): Seller
    {
        return $request->user()->seller ?? abort(403, 'Seller profile unavailable.');
    }
}
