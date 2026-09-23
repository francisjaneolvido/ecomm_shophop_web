<?php

namespace App\Http\Controllers;

use App\Models\Seller\Manage_inventory\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class PublicSearchController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $products = null;

        if ($search !== '') {
            $needle = '%' . strtolower($search) . '%';

            // Public Search must expose only the same available catalogue as buyer discovery.
            $products = Product::query()
                ->publiclyDiscoverable()
                ->where(function (Builder $query) use ($needle): void {
                    $query->whereRaw('LOWER(name) LIKE ?', [$needle])
                        ->orWhereRaw('LOWER(category) LIKE ?', [$needle])
                        ->orWhereRaw('LOWER(description) LIKE ?', [$needle]);
                })
                ->orderBy('name')
                ->paginate(24)
                ->withQueryString();
        }

        return view('search.index', compact('search', 'products'));
    }
}
