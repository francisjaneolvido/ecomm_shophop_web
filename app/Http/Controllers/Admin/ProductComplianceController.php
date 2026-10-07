<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Seller\Manage_inventory\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

// Merchandise decisions stay independent of archive, stock, and payment state.
class ProductComplianceController extends Controller
{
    // Server and forms share the teammate workflow's supported rejection vocabulary.
    public const REJECTION_REASONS = [
        'wrong_category' => 'Wrong Category', 'prohibited_product' => 'Prohibited Product',
        'inappropriate_content' => 'Inappropriate Content', 'misleading_info' => 'Misleading Product Information',
        'poor_image_quality' => 'Poor Image Quality', 'incomplete_details' => 'Incomplete Details', 'other' => 'Other',
    ];

    public function index(Request $request)
    {
        // Validated filters and stable tie-breaking keep pagination truthful across review queues.
        $data = $request->validate(['filter' => ['nullable', Rule::in(['pending_review', 'approved', 'rejected'])],
            'sort' => ['nullable', Rule::in(['newest', 'oldest', 'az', 'za'])], 'search' => ['nullable', 'string', 'max:255']]);
        $filter = $data['filter'] ?? 'pending_review';
        $sort = $data['sort'] ?? 'newest';
        $search = trim($data['search'] ?? '');
        $counts = array_fill_keys(['pending_review', 'approved', 'rejected'], 0);
        foreach (Product::selectRaw('compliance_status, COUNT(*) AS aggregate')->groupBy('compliance_status')->get() as $row) {
            $counts[$row->compliance_status] = (int) $row->aggregate;
        }
        $query = Product::with('seller')->where('compliance_status', $filter);
        if ($search !== '') {
            // Search stays on canonical Product fields and Seller business identity, not invented display data.
            $query->where(fn ($q) => $q->where('name', 'like', '%'.$search.'%')->orWhere('sku', 'like', '%'.$search.'%')
                ->orWhereHas('seller', fn ($s) => $s->where('business_name', 'like', '%'.$search.'%')));
        }
        $column = in_array($sort, ['az', 'za'], true) ? 'name' : 'submitted_at';
        $direction = in_array($sort, ['oldest', 'az'], true) ? 'asc' : 'desc';
        $products = $query->orderBy($column, $direction)->orderBy('id', $direction)->paginate(10)->withQueryString();
        return view('admin.product-compliance.index', compact('products', 'counts', 'filter', 'sort', 'search'));
    }

    public function show(Product $product)
    {
        // A native detail page inspects persisted images and variants without making review depend on JavaScript.
        $product->load(['seller', 'images', 'variants']);
        $reasons = self::REJECTION_REASONS;
        return view('admin.product-compliance.show', compact('product', 'reasons'));
    }

    public function approve(Request $request, Product $product)
    {
        // Serialize the decision with Seller edits and Checkout's canonical Product lock.
        DB::transaction(function () use ($request, $product) {
            Product::whereKey($product->id)->lockForUpdate()->firstOrFail()->update([
                'compliance_status' => 'approved', 'reviewed_at' => now(), 'reviewed_by' => $request->user()->id,
                'rejection_reason' => null, 'rejection_notes' => null,
            ]);
        });
        return back()->with('status', 'Product approved. Buyer visibility also requires active status and available stock.');
    }

    public function reject(Request $request, Product $product)
    {
        // UI choices cannot authorize arbitrary reason strings or unbounded notes.
        $data = $request->validate(['rejection_reason' => ['required', Rule::in(array_keys(self::REJECTION_REASONS))],
            'rejection_notes' => ['nullable', 'string', 'max:2000']]);
        DB::transaction(function () use ($request, $product, $data) {
            // The locked review changes compliance only; purchased Orders retain their existing authority.
            Product::whereKey($product->id)->lockForUpdate()->firstOrFail()->update([
                'compliance_status' => 'rejected', 'reviewed_at' => now(), 'reviewed_by' => $request->user()->id,
                'rejection_reason' => $data['rejection_reason'], 'rejection_notes' => $data['rejection_notes'] ?? null,
            ]);
        });
        return back()->with('status', 'Product rejected. The Seller can correct and resubmit it for review.');
    }
}
