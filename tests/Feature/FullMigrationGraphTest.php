<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class FullMigrationGraphTest extends TestCase
{
    use RefreshDatabase;

    public function test_clean_database_has_canonical_order_item_columns(): void
    {
        // The normal migration graph must build Commerce tables without a hidden selective migration list.
        foreach (['order_id', 'product_id', 'product_variant_id', 'quantity', 'price'] as $column) {
            $this->assertTrue(Schema::hasColumn('order_items', $column), $column);
        }
    }

    public function test_legacy_compatibility_migrations_preserve_existing_canonical_columns(): void
    {
        // Reapplying guarded compatibility logic simulates an upgrade whose canonical tables already exist.
        (require database_path('migrations/Buyer/2026_09_12_104354_fix_order_items_table_columns.php'))->up();
        (require database_path('migrations/Buyer/2026_09_12_104829_add_missing_columns_to_orders_table.php'))->up();
        foreach (['buyer_id', 'status', 'total_amount'] as $column) {
            $this->assertTrue(Schema::hasColumn('orders', $column), $column);
        }
        foreach (['order_id', 'product_id', 'product_variant_id', 'quantity', 'price'] as $column) {
            $this->assertTrue(Schema::hasColumn('order_items', $column), $column);
        }
    }
}
