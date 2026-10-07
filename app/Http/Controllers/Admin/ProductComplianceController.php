<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Seller\Manage_inventory\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductComplianceController extends Controller
{
    /**
     * Stored as keys in products.rejection_reason. The seller's inventory
     * page title-cases the key, so keep them readable (snake_case words).
     */
    public const REJECTION_REASONS = [
        'wrong_category' => 'Wrong category',
        'poor_images' => 'Unclear or missing images',
        'incomplete_description' => 'Incomplete or misleading description',
        'pricing_issue' => 'Pricing issue',
        'prohibited_item' => 'Prohibited or restricted item',
        'suspected_counterfeit' => 'Suspected counterfeit',
        'other' => 'Other',
    ];

    private const TAB_STATUS = [
        'pending' => Product::COMPLIANCE_PENDING,
        'approved' => Product::COMPLIANCE_APPROVED,
        'rejected' => Product::COMPLIANCE_REJECTED,
    ];

    /**
     * Product review queue. Archived products are left out: sellers hid them,
     * so there is nothing to review until they are restored.
     */
    public function index(Request $request): View
    {
        $filter = $request->query('filter', 'pending');
        $filter = array_key_exists($filter, self::TAB_STATUS) ? $filter : 'pending';

        $sort = $request->query('sort', 'newest');
        $search = trim((string) $request->query('search', ''));

        $base = Product::query()->where('status', 'active');

        $counts = [
            'pending' => (clone $base)->where('compliance_status', Product::COMPLIANCE_PENDING)->count(),
            'approved' => (clone $base)->where('compliance_status', Product::COMPLIANCE_APPROVED)->count(),
            'rejected' => (clone $base)->where('compliance_status', Product::COMPLIANCE_REJECTED)->count(),
        ];

        $query = (clone $base)
            ->where('compliance_status', self::TAB_STATUS[$filter])
            ->with(['seller.user', 'images', 'variants', 'reviewer']);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhereHas('seller', fn ($s) => $s->where('business_name', 'like', "%{$search}%"));
            });
        }

        match ($sort) {
            'oldest' => $query->orderByRaw('COALESCE(submitted_at, created_at) asc'),
            'az' => $query->orderBy('name'),
            'za' => $query->orderByDesc('name'),
            'price_low' => $query->orderBy('price'),
            'price_high' => $query->orderByDesc('price'),
            default => $query->orderByRaw('COALESCE(submitted_at, created_at) desc'),
        };

        $products = $query->paginate(10)->withQueryString();

        $productsForJs = $products->getCollection()
            ->map(fn (Product $product) => $this->toJsProduct($product))
            ->values();

        return view('admin.seller-compliance', [
            'products' => $products,
            'productsForJs' => $productsForJs,
            'counts' => $counts,
            'filter' => $filter,
            'sort' => $sort,
            'rejectionReasons' => self::REJECTION_REASONS,
        ]);
    }

    public function approve(Product $product): RedirectResponse
    {
        if (! $this->canTransition($product, [Product::COMPLIANCE_PENDING])) {
            return back()->withErrors(['moderation' => 'Only active products that are pending review can be approved.']);
        }

        $product->update([
            'compliance_status' => Product::COMPLIANCE_APPROVED,
            'rejection_reason' => null,
            'rejection_notes' => null,
            'reviewed_at' => now(),
            'reviewed_by' => Auth::id(),
        ]);

        return back()->with('status', "\"{$product->name}\" has been approved.");
    }

    /**
     * Reject a pending product, or take down an approved one.
     * The seller sees the reason on their inventory page and resubmits
     * by editing the product (which puts it back in the queue).
     */
    public function reject(Request $request, Product $product): RedirectResponse
    {
        if (! $this->canTransition($product, [Product::COMPLIANCE_PENDING, Product::COMPLIANCE_APPROVED])) {
            return back()->withErrors(['moderation' => 'Only active products that are pending review or approved can be rejected.']);
        }

        $validated = $request->validate([
            'reason' => ['required', Rule::in(array_keys(self::REJECTION_REASONS))],
            'notes' => [
                Rule::requiredIf($request->input('reason') === 'other'),
                'nullable',
                'string',
                'max:1000',
            ],
        ]);

        $product->update([
            'compliance_status' => Product::COMPLIANCE_REJECTED,
            'rejection_reason' => $validated['reason'],
            'rejection_notes' => $validated['notes'] ?? null,
            'reviewed_at' => now(),
            'reviewed_by' => Auth::id(),
        ]);

        return back()->with('status', "\"{$product->name}\" has been rejected.");
    }

    private function canTransition(Product $product, array $from): bool
    {
        return $product->status === 'active'
            && in_array($product->compliance_status, $from, true);
    }

    /**
     * Flat structure for the review modal's JavaScript.
     */
    private function toJsProduct(Product $product): array
    {
        $price = (float) $product->price;
        $discount = (int) $product->discount;
        $final = $discount > 0 ? round($price - ($price * $discount / 100), 2) : $price;

        $images = $product->images
            ->sortByDesc('is_primary')
            ->map(fn ($image) => asset('storage/' . ltrim($image->image_path, '/')))
            ->values()
            ->all();

        if (empty($images) && $product->image) {
            $images = [asset('storage/' . ltrim($product->image, '/'))];
        }

        $submitted = $product->submitted_at ?? $product->created_at;

        return [
            'id' => $product->id,
            'name' => $product->name,
            'sku' => $product->sku,
            'category' => $product->category,
            'description' => $product->description,
            'seller_name' => $product->seller?->business_name ?? 'Unknown seller',
            'seller_email' => $product->seller?->user?->email,
            'price' => number_format($price, 2),
            'discount' => $discount,
            'final_price' => number_format($final, 2),
            'stock' => (int) $product->stock,
            'has_variants' => (bool) $product->has_variants,
            'variants' => $product->variants->map(fn ($variant) => [
                'name' => $variant->name,
                'sku' => $variant->sku,
                'price' => $variant->price !== null ? number_format((float) $variant->price, 2) : null,
                'stock' => (int) $variant->stock,
                'status' => $variant->status,
            ])->values()->all(),
            'images' => $images,
            'status' => $product->compliance_status,
            'submitted_at' => $submitted ? $submitted->format('M d, Y g:i A') . ' · ' . $submitted->diffForHumans() : null,
            'reviewed_at' => $product->reviewed_at?->format('M d, Y g:i A'),
            'reviewed_by' => $product->reviewer?->display_name,
            'rejection_reason' => $product->rejection_reason
                ? (self::REJECTION_REASONS[$product->rejection_reason] ?? Str::headline($product->rejection_reason))
                : null,
            'rejection_notes' => $product->rejection_notes,
        ];
    }
}