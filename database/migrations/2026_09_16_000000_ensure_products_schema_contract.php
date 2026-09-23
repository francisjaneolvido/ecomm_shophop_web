<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const COLUMN_CONTRACT = [
        'id' => ['types' => ['bigint', 'integer'], 'nullable' => false],
        'seller_id' => ['types' => ['bigint', 'integer'], 'nullable' => false],
        'name' => ['types' => ['varchar'], 'nullable' => false],
        'sku' => ['types' => ['varchar'], 'nullable' => true],
        'category' => ['types' => ['varchar'], 'nullable' => false],
        'price' => ['types' => ['decimal', 'numeric'], 'nullable' => false],
        'discount' => ['types' => ['tinyint', 'integer'], 'nullable' => false, 'default' => '0'],
        'stock' => ['types' => ['int', 'integer'], 'nullable' => false, 'default' => '0'],
        'low_stock_threshold' => ['types' => ['int', 'integer'], 'nullable' => true],
        'has_variants' => ['types' => ['tinyint', 'boolean', 'integer'], 'nullable' => false, 'default' => '0'],
        'description' => ['types' => ['text'], 'nullable' => true],
        'image' => ['types' => ['varchar'], 'nullable' => true],
        'status' => ['types' => ['enum', 'varchar'], 'nullable' => false, 'default' => 'active'],
        'created_at' => ['types' => ['timestamp', 'datetime'], 'nullable' => true],
        'updated_at' => ['types' => ['timestamp', 'datetime'], 'nullable' => true],
    ];

    public function up(): void
    {
        if (! Schema::hasTable('products')) {
            $this->createProductsTable();

            return;
        }

        // Existing tables may have predated the inventory extension; repair safe additions, then reject incompatible structure.
        $this->restoreSkippedInventoryColumns();

        $problems = $this->contractProblems();

        if ($problems !== []) {
            throw new \LogicException(
                'Existing products table is incompatible with the canonical ShopHop contract: '
                . implode('; ', $problems)
            );
        }
    }

    public function down(): void
    {
        // This repair can validate a long-lived production table, so rollback must not drop it.
    }

    private function createProductsTable(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->string('name');
            $table->string('sku')->nullable()->unique();
            $table->string('category');
            $table->decimal('price', 10, 2);
            $table->unsignedTinyInteger('discount')->default(0);
            $table->unsignedInteger('stock')->default(0);
            $table->unsignedInteger('low_stock_threshold')->nullable();
            $table->boolean('has_variants')->default(false);
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->enum('status', ['active', 'archived'])->default('active');
            $table->timestamps();
        });
    }

    private function restoreSkippedInventoryColumns(): void
    {
        $missingSku = ! Schema::hasColumn('products', 'sku');
        $missingLowStockThreshold = ! Schema::hasColumn('products', 'low_stock_threshold');
        $missingHasVariants = ! Schema::hasColumn('products', 'has_variants');

        if ($missingSku || $missingLowStockThreshold || $missingHasVariants) {
            // The historical inventory extension could run before products existed; these additions are safe for live rows.
            Schema::table('products', function (Blueprint $table) use ($missingSku, $missingLowStockThreshold, $missingHasVariants) {
                if ($missingSku) {
                    $table->string('sku')->nullable()->unique();
                }

                if ($missingLowStockThreshold) {
                    $table->unsignedInteger('low_stock_threshold')->nullable();
                }

                if ($missingHasVariants) {
                    $table->boolean('has_variants')->default(false);
                }
            });
        }

        if (! Schema::hasIndex('products', 'products_sku_unique')) {
            $duplicates = DB::table('products')
                ->whereNotNull('sku')
                ->select('sku')
                ->groupBy('sku')
                ->havingRaw('COUNT(*) > 1')
                ->pluck('sku');

            if ($duplicates->isNotEmpty()) {
                throw new \LogicException('Existing products table has duplicate non-null SKU values.');
            }

            Schema::table('products', function (Blueprint $table) {
                $table->unique('sku');
            });
        }
    }

    private function contractProblems(): array
    {
        $columns = collect(Schema::getColumns('products'))->keyBy('name');
        $problems = [];

        foreach (self::COLUMN_CONTRACT as $name => $contract) {
            $column = $columns->get($name);

            if ($column === null) {
                $problems[] = 'missing column ' . $name;

                continue;
            }

            $type = strtolower($column['type_name']);

            if (! in_array($type, $contract['types'], true)) {
                $problems[] = $name . ' has type ' . $type;
            }

            if ($column['nullable'] !== $contract['nullable']) {
                $problems[] = $name . ' has incompatible nullability';
            }

            if (array_key_exists('default', $contract)
                && $this->normalisedDefault($column['default']) !== $contract['default']) {
                $problems[] = $name . ' has incompatible default';
            }
        }

        if (! Schema::hasIndex('products', ['id'], 'primary')) {
            $problems[] = 'missing primary key on id';
        }

        if (! Schema::hasIndex('products', 'products_sku_unique')) {
            $problems[] = 'missing unique index products_sku_unique';
        }

        if (! $this->hasSellerForeignKey()) {
            $problems[] = 'missing seller_id foreign key to users.id with cascade delete';
        }

        return $problems;
    }

    private function normalisedDefault(mixed $default): ?string
    {
        if ($default === null) {
            return null;
        }

        return trim((string) $default, "'\"");
    }

    private function hasSellerForeignKey(): bool
    {
        return collect(Schema::getForeignKeys('products'))->contains(
            fn (array $foreignKey): bool => $foreignKey['columns'] === ['seller_id']
                && $foreignKey['foreign_table'] === 'users'
                && $foreignKey['foreign_columns'] === ['id']
                && strtolower($foreignKey['on_delete']) === 'cascade'
        );
    }
};
