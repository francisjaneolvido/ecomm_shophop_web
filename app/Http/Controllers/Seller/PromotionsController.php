<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Seller\Manage_inventory\Product;
use App\Models\Seller\Manage_inventory\Voucher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PromotionsController extends Controller
{
    /** Seller-owned promotional codes. Codes are currently product-page display only. */
    public function index(Request $request): View
    {
        $sellerId = $request->user()->id;
        $search = $request->query('q', '');
        $search = is_string($search) ? mb_substr(trim($search), 0, 100) : '';
        $status = $request->query('status', 'all');
        $status = in_array($status, ['all', 'active', 'scheduled', 'expired', 'paused', 'exhausted'], true) ? $status : 'all';
        $sort = $request->query('sort', 'newest');
        $sort = in_array($sort, ['newest', 'oldest', 'code', 'ending'], true) ? $sort : 'newest';
        $perPage = in_array((int) $request->query('per_page'), [25, 50], true) ? (int) $request->query('per_page') : 25;
        $now = now();

        $base = Voucher::query()->where('seller_id', $sellerId);
        $counts = [
            'total' => (clone $base)->count(),
            'active' => (clone $base)->where('status', 'active')
                ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now))
                ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now))
                ->where(fn ($q) => $q->whereNull('usage_limit')->orWhereColumn('used_count', '<', 'usage_limit'))->count(),
            'scheduled' => (clone $base)->where('status', 'active')->where('starts_at', '>', $now)->count(),
            'expired' => (clone $base)->where('status', 'active')->whereNotNull('ends_at')->where('ends_at', '<', $now)->count(),
            'paused' => (clone $base)->where('status', '!=', 'active')->count(),
        ];
        $discountedProducts = Product::query()->where('seller_id', $sellerId)
            ->where('status', 'active')->where('discount', '>', 0)->count();

        $query = (clone $base)->with(['products:id,name'])->withCount('products');
        if ($search !== '') {
            $query->where(fn ($q) => $q->where('code', 'like', "%{$search}%")
                ->orWhere('name', 'like', "%{$search}%"));
        }
        match ($status) {
            'active' => $query->where('status', 'active')
                ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now))
                ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now))
                ->where(fn ($q) => $q->whereNull('usage_limit')->orWhereColumn('used_count', '<', 'usage_limit')),
            'scheduled' => $query->where('status', 'active')->where('starts_at', '>', $now),
            'expired' => $query->where('status', 'active')->whereNotNull('ends_at')->where('ends_at', '<', $now),
            'paused' => $query->where('status', '!=', 'active'),
            'exhausted' => $query->where('status', 'active')->whereNotNull('usage_limit')
                ->whereColumn('used_count', '>=', 'usage_limit'),
            default => null,
        };
        match ($sort) {
            'oldest' => $query->orderBy('created_at')->orderBy('id'),
            'code' => $query->orderBy('code')->orderByDesc('id'),
            'ending' => $query->orderByRaw('CASE WHEN ends_at IS NULL THEN 1 ELSE 0 END')->orderBy('ends_at')->orderByDesc('id'),
            default => $query->orderByDesc('created_at')->orderByDesc('id'),
        };
        $vouchers = $query->paginate($perPage)->withQueryString();

        return view('seller.promotions', compact('vouchers', 'counts', 'status', 'search', 'sort', 'perPage', 'discountedProducts'));
    }

    /** Lightweight product lookup: no unbounded inventory payload in an edit modal. */
    public function products(Request $request): JsonResponse
    {
        $search = $request->query('q', '');
        $search = is_string($search) ? mb_substr(trim($search), 0, 80) : '';
        $products = Product::query()->where('seller_id', $request->user()->id)
            ->where('status', 'active')
            ->when($search !== '', fn ($q) => $q->where(fn ($inner) => $inner
                ->where('name', 'like', "%{$search}%")
                ->orWhere('sku', 'like', "%{$search}%")))
            ->orderBy('name')->limit(25)->get(['id', 'name', 'sku']);
        return response()->json(['products' => $products]);
    }
}
