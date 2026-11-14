<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->string('variant_label')->nullable();
            $table->string('variant_image_path')->nullable();
        });

        // Preserve the current human-readable choice on historical orders before
        // a seller can rename or archive its variant.
        DB::table('order_items')->whereNotNull('product_variant_id')->orderBy('id')
            ->chunkById(300, function ($items) {
                $variants = DB::table('product_variants')
                    ->whereIn('id', $items->pluck('product_variant_id')->unique())
                    ->get()->keyBy('id');
                $imageIds = $variants->pluck('image_id')->filter()->unique();
                $images = $imageIds->isEmpty() ? collect() : DB::table('product_images')
                    ->whereIn('id', $imageIds)->pluck('image_path', 'id');
                foreach ($items as $item) {
                    $variant = $variants->get($item->product_variant_id);
                    if (! $variant) continue;
                    $attributes = json_decode($variant->attributes ?? 'null', true);
                    $choices = [];
                    foreach (is_array($attributes) ? $attributes : [] as $type => $value) {
                        if (is_string($type) && ! in_array(strtolower($type),
                            ['id', 'product_id', 'product_variant_id', 'sku', 'price', 'stock', 'status', 'weight_kg', 'image_id', 'created_at', 'updated_at'], true) &&
                            (is_string($value) || is_numeric($value)) && trim((string) $value) !== '') {
                            $choices[] = ucfirst($type).': '.trim((string) $value);
                        }
                    }
                    DB::table('order_items')->where('id', $item->id)->update([
                        'variant_label' => $choices ? implode(' · ', $choices) : $variant->name,
                        'variant_image_path' => $images->get($variant->image_id),
                    ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['variant_label', 'variant_image_path']);
        });
    }
};
