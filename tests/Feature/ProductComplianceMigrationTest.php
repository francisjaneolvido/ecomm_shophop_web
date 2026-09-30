<?php

namespace Tests\Feature;

use App\Models\Seller\Manage_inventory\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
// Controlled database events schedule a new-row insertion inside the public migration boundary.
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

// Clean and upgraded catalogues must distinguish compatibility approval from a human review.
class ProductComplianceMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_clean_graph_has_pending_default_and_user_reviewer_foreign_key(): void
    {
        // New merchandise must await review even when inserted outside the Seller form.
        $this->assertTrue(Schema::hasColumn('products', 'compliance_status'));
        $columns = collect(Schema::getColumns('products'))->keyBy('name');
        $this->assertSame('pending_review', trim($columns['compliance_status']['default'], "'\""));
        $this->assertTrue(collect(Schema::getForeignKeys('products'))->contains(
            fn ($key) => $key['columns'] === ['reviewed_by'] && $key['foreign_table'] === 'users'
                && $key['foreign_columns'] === ['id']
        ));
    }

    public function test_forward_upgrade_grandfathers_existing_rows_once_without_human_review(): void
    {
        // Rebuild the pre-compliance shape on this isolated connection, then upgrade its existing catalogue.
        $migration = require database_path('migrations/Seller/Manage_inventory/2026_09_30_000001_add_product_compliance.php');
        $migration->down();
        $seller = User::forceCreate(['email' => 'migration@test.example', 'password' => 'unused']);
        foreach (['active', 'archived'] as $status) {
            Product::create(['seller_id' => $seller->id, 'name' => $status, 'category' => 'Home',
                'price' => 100, 'stock' => 3, 'status' => $status]);
        }
        $migration->up();
        foreach (Product::all() as $product) {
            $this->assertSame('approved', $product->compliance_status);
            $this->assertNull($product->reviewed_by);
            $this->assertNull($product->reviewed_at);
            $this->assertNull($product->submitted_at);
        }
        $new = Product::create(['seller_id' => $seller->id, 'name' => 'New', 'category' => 'Home',
            'price' => 100, 'stock' => 3]);
        $this->assertSame('pending_review', $new->fresh()->compliance_status);
        $migration->up();
        $this->assertSame('pending_review', $new->fresh()->compliance_status);
        $this->assertSame(3, Product::count());
        $this->assertSame('archived', Product::where('name', 'archived')->sole()->status);
    }

    public function test_products_created_during_rollout_do_not_receive_compatibility_approval(): void
    {
        // Simulate an insert after the approval column appears but before the forward migration finishes backfilling.
        $migration = require database_path('migrations/Seller/Manage_inventory/2026_09_30_000001_add_product_compliance.php');
        $migration->down();
        $seller = User::forceCreate(['email' => 'rollout@test.example', 'password' => 'unused']);
        $data = ['seller_id' => $seller->id, 'name' => 'Existing rollout product', 'category' => 'Home', 'price' => 100, 'stock' => 3];
        $existing = Product::create($data);
        $incoming = null;
        $inserted = false;
        DB::listen(function ($query) use (&$incoming, &$inserted, $data): void {
            // The event is only a deterministic scheduling hook; assertions inspect persisted approval independently.
            if (! $inserted && str_starts_with(strtolower($query->sql), 'alter table') && str_contains($query->sql, 'compliance_status')) {
                $inserted = true;
                $incoming = Product::create(array_replace($data, ['name' => 'New during rollout']));
            }
        });
        $migration->up();
        $this->assertNotNull($incoming);
        $this->assertSame('approved', $existing->fresh()->compliance_status);
        $this->assertSame('pending_review', $incoming->fresh()->compliance_status);
        $this->assertNull($incoming->fresh()->reviewed_by);
    }
}
