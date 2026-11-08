<?php

namespace App\Http\Controllers;

use App\Models\Seller\Manage_inventory\Product;
use Illuminate\Support\Facades\Schema;

class CatalogRevisionController extends Controller
{
    public function __invoke()
    {
        $snapshot = [];
        if (Schema::hasTable('products')) {
            $query = Product::query()->publiclyDiscoverable()->orderBy('id');
            foreach (['images' => 'product_images', 'variants' => 'product_variants', 'reviews' => 'reviews'] as $relation => $table) {
                if (Schema::hasTable($table)) $query->with([$relation => fn ($q) => $q->orderBy('id')]);
            }
            // Hash persisted attributes, including same-second edits and deletions.
            // Return only the digest, never internal product or reviewer records.
            $snapshot = $query->get()->map(fn ($product) => [
                $product->getAttributes(),
                $product->getRelations(),
            ])->all();
        }
        return response()->json(['revision' => hash('sha256', json_encode($snapshot, JSON_THROW_ON_ERROR))])
            ->header('Cache-Control', 'no-store, private');
    }
}
