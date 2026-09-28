<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Seller\Manage_inventory\Product;
use Illuminate\Http\Request;

class ProductComplianceController extends Controller
{
    /**
     * GET /admin/product-compliance
     */
    public function index(Request $request)
    {
        $filter = $request->get('filter', 'pending_review');
        $search = $request->get('search');
        $sort = $request->get('sort', 'newest');

        $counts = [
            'pending_review' => Product::where('compliance_status', 'pending_review')->count(),
            'approved'       => Product::where('compliance_status', 'approved')->count(),
            'rejected'       => Product::where('compliance_status', 'rejected')->count(),
        ];

        $query = Product::with(['seller', 'images'])
            ->where('compliance_status', $filter);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('seller', function ($sq) use ($search) {
                      $sq->where('business_name', 'like', "%{$search}%");
                  });
            });
        }

        $query = match ($sort) {
            'oldest' => $query->orderBy('submitted_at', 'asc'),
            'az'     => $query->orderBy('name', 'asc'),
            'za'     => $query->orderBy('name', 'desc'),
            default  => $query->orderBy('submitted_at', 'desc'),
        };

        $products = $query->paginate(10)->withQueryString();

        return view('admin.product-compliance', [
            'products' => $products,
            'counts'   => $counts,
            'filter'   => $filter,
            'sort'     => $sort,
        ]);
    }

    /**
     * GET /admin/product-compliance/{product} (AJAX — para sa details modal)
     */
    public function show(Product $product)
    {
        $product->load(['seller', 'images', 'variants']);

        return response()->json($product);
    }

    /**
     * PATCH /admin/product-compliance/{product}/approve
     */
    public function approve(Product $product)
    {
        $product->update([
            'compliance_status' => 'approved',
            'reviewed_at' => now(),
            'reviewed_by' => auth()->id(),
            'rejection_reason' => null,
            'rejection_notes' => null,
        ]);

        return back()->with('status', "{$product->name} has been approved and is now live.");
    }

    /**
     * PATCH /admin/product-compliance/{product}/reject
     */
    public function reject(Request $request, Product $product)
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:255',
            'rejection_notes'  => 'nullable|string',
        ]);

        $product->update([
            'compliance_status' => 'rejected',
            'reviewed_at' => now(),
            'reviewed_by' => auth()->id(),
            'rejection_reason' => $validated['rejection_reason'],
            'rejection_notes' => $validated['rejection_notes'] ?? null,
        ]);

        return back()->with('status', "{$product->name} has been rejected.");
    }
}