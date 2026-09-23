<?php

namespace Tests\Feature;

use App\Models\Seller\Manage_inventory\Product;
use App\Models\User;
use LogicException;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ProductsSchemaContractTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropAllTables();

        // Run only the Product dependency chain; full migrate:fresh has an unrelated duplicate order_items.order_id step.
        $this->runMigrations([
            '0001_01_01_000000_create_users_table.php',
            '2026_08_31_000001_add_account_type_and_status_to_users_table.php',
            '0001_01_01_000003_create_products_table.php',
            'Seller/Manage_inventory/2025_01_15_000001_add_inventory_fields_to_products_table.php',
            'Seller/Manage_inventory/2025_01_15_000002_create_product_images_table.php',
            'Seller/Manage_inventory/2025_01_15_000003_create_product_variants_table.php',
            'Seller/Manage_inventory/2025_01_15_000004_create_vouchers_table.php',
            'Seller/Manage_inventory/2025_01_15_000005_create_voucher_product_table.php',
        ]);
    }

    public function test_fresh_migrations_provide_the_canonical_products_contract(): void
    {
        $this->assertCanonicalProductsTable();
    }

    public function test_authenticated_buyer_can_view_category_detail_after_fresh_migration(): void
    {
        $buyer = $this->approvedUser('buyer');

        Product::query()->create([
            'seller_id' => $buyer->id,
            'name' => 'Fresh schema headphones',
            'category' => 'Electronics and Gadgets',
            'price' => 1999.50,
            'stock' => 3,
        ]);

        $this->actingAs($buyer)
            ->get(route('buyer.category.show', ['category' => 'electronics-and-gadgets']))
            ->assertOk()
            ->assertViewHas('products', fn (array $products): bool => $products[0]['name'] === 'Fresh schema headphones');
    }

    public function test_authenticated_seller_can_view_inventory_after_fresh_migration(): void
    {
        $seller = $this->approvedUser('seller');

        Product::query()->create([
            'seller_id' => $seller->id,
            'name' => 'Fresh schema inventory item',
            'category' => 'Electronics & Gadgets',
            'price' => 599.00,
            'stock' => 7,
        ]);

        $this->actingAs($seller)
            ->get(route('seller.inventory'))
            ->assertOk()
            ->assertViewHas('products', fn ($products): bool => $products->contains('name', 'Fresh schema inventory item'));
    }

    public function test_forward_contract_migration_creates_a_missing_products_table(): void
    {
        Schema::drop('products');

        $this->runMigrations([
            '2026_09_16_000000_ensure_products_schema_contract.php',
        ]);

        $this->assertCanonicalProductsTable();
    }

    public function test_forward_contract_migration_preserves_a_compatible_existing_products_table(): void
    {
        $seller = $this->approvedUser('seller');

        $product = Product::query()->create([
            'seller_id' => $seller->id,
            'name' => 'Existing compatible product',
            'category' => 'Electronics & Gadgets',
            'price' => 999.00,
            'stock' => 1,
        ]);

        $this->runMigrations([
            '2026_09_16_000000_ensure_products_schema_contract.php',
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Existing compatible product',
        ]);
    }

    public function test_forward_contract_migration_rejects_an_incompatible_existing_products_table(): void
    {
        Schema::drop('products');

        Schema::create('products', function ($table): void {
            $table->id();
        });

        $this->expectException(LogicException::class);

        $this->runMigrations([
            '2026_09_16_000000_ensure_products_schema_contract.php',
        ]);
    }

    private function approvedUser(string $accountType): User
    {
        $user = new User();

        $user->forceFill([
            'email' => $accountType . '@products-schema.test',
            'password' => 'not-used-by-this-test',
            'account_type' => $accountType,
            'status' => 'approved',
            'email_verified_at' => now(),
        ])->save();

        return $user;
    }

    private function runMigrations(array $paths): void
    {
        foreach ($paths as $path) {
            (require database_path('migrations/' . $path))->up();
        }
    }

    private function assertCanonicalProductsTable(): void
    {
        $this->assertTrue(Schema::hasTable('products'));

        foreach ([
            'id',
            'seller_id',
            'name',
            'sku',
            'category',
            'price',
            'discount',
            'stock',
            'low_stock_threshold',
            'has_variants',
            'description',
            'image',
            'status',
            'created_at',
            'updated_at',
        ] as $column) {
            $this->assertTrue(Schema::hasColumn('products', $column));
        }

        $columns = collect(Schema::getColumns('products'))->keyBy('name');

        $this->assertFalse($columns['seller_id']['nullable']);
        $this->assertFalse($columns['name']['nullable']);
        $this->assertTrue($columns['sku']['nullable']);
        $this->assertFalse($columns['category']['nullable']);
        $this->assertFalse($columns['price']['nullable']);
        $this->assertTrue($columns['description']['nullable']);
        $this->assertTrue($columns['image']['nullable']);
        $this->assertSame('0', trim((string) $columns['discount']['default'], "'\""));
        $this->assertSame('0', trim((string) $columns['stock']['default'], "'\""));
        $this->assertSame('0', trim((string) $columns['has_variants']['default'], "'\""));
        $this->assertSame('active', trim((string) $columns['status']['default'], "'\""));
        $this->assertTrue(Schema::hasIndex('products', 'products_sku_unique'));
        $this->assertTrue(Schema::hasIndex('products', ['id'], 'primary'));
        $this->assertTrue(collect(Schema::getForeignKeys('products'))->contains(
            fn (array $foreignKey): bool => $foreignKey['columns'] === ['seller_id']
                && $foreignKey['foreign_table'] === 'users'
                && $foreignKey['foreign_columns'] === ['id']
                && strtolower($foreignKey['on_delete']) === 'cascade'
        ));
    }
}
