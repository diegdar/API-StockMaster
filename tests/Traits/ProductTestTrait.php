<?php
declare(strict_types=1);

namespace Tests\Traits;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\RestockAlert;
use App\Models\StockMovement;
use Illuminate\Testing\Fluent\AssertableJson;

/**
 * Trait for product-related test helpers.
 * Consolidates product data generation, assertions, and data providers.
 */
trait ProductTestTrait
{
    use EntityCreationTrait;

    // ==========================================
    // Specialized Creation Methods
    // ==========================================

    /**
     * Create product with inventory for deletion tests.
     *
     * @return object{product: Product, warehouse: \App\Models\Warehouse}
     */
    protected function createProductWithInventoryForDeletion(): object
    {
        $entities = $this->createProductEntities();

        Inventory::factory()->create([
            'product_id' => $entities->product->id,
            'warehouse_id' => $entities->warehouse->id,
            'quantity' => 100,
        ]);

        return (object) [
            'product' => $entities->product,
            'warehouse' => $entities->warehouse,
        ];
    }

    /**
     * Create product with stock movement for deletion tests.
     *
     * @param int $userId
     * @param array<string, mixed> $stockMovementOverrides
     * @return object{product: Product, warehouse: \App\Models\Warehouse}
     */
    protected function createProductWithStockMovement(
        int $userId,
        array $stockMovementOverrides = []
    ): object {
        $entities = $this->createProductEntities();

        StockMovement::factory()->create(array_merge([
            'product_id' => $entities->product->id,
            'warehouse_id' => $entities->warehouse->id,
            'user_id' => $userId,
            'type' => 'in',
            'quantity' => 50,
        ], $stockMovementOverrides));

        return (object) [
            'product' => $entities->product,
            'warehouse' => $entities->warehouse,
        ];
    }

    /**
     * Create product with active restock alert for deletion tests.
     *
     * @param array<string, mixed> $alertOverrides
     * @return object{product: Product, warehouse: \App\Models\Warehouse}
     */
    protected function createProductWithRestockAlert(
        array $alertOverrides = []
    ): object {
        $entities = $this->createProductEntities();

        RestockAlert::factory()->create(array_merge([
            'product_id' => $entities->product->id,
            'warehouse_id' => $entities->warehouse->id,
            'is_active' => true,
            'threshold' => 10,
        ], $alertOverrides));

        return (object) [
            'product' => $entities->product,
            'warehouse' => $entities->warehouse,
        ];
    }

    // ==========================================
    // Assertion Methods
    // ==========================================

    /**
     * Assert product attributes match expected data.
     *
     * @param Product $product
     * @param array<string, mixed> $expectedData
     */
    protected function assertProductAttributes(Product $product, array $expectedData): void
    {
        foreach ($expectedData as $attribute => $value) {
            $this->assertEquals(
                $value,
                $product->{$attribute},
                "Failed asserting that product {$attribute} equals expected value"
            );
        }
    }

    // ==========================================
    // Data Providers
    // ==========================================

    /**
     * Data provider for valuation strategies.
     *
     * @return array<string, array{0: string}>
     */
    public static function valuationStrategyProvider(): array
    {
        return [
            'fifo strategy' => ['fifo'],
            'lifo strategy' => ['lifo'],
            'avg strategy' => ['avg'],
        ];
    }

    /**
     * Data provider for pagination limits.
     *
     * @return array<string, array{0: int}>
     */
    public static function paginationProvider(): array
    {
        return [
            '5 items per page' => [5],
            '10 items per page' => [10],
            '15 items per page' => [15],
        ];
    }

    /**
     * Data provider for product update scenarios.
     *
     * @return array<string, array{0: array<string, mixed>, 1: string, 2: mixed}>
     */
    public static function productUpdateProvider(): array
    {
        return [
            'update name only' => [
                ['name' => 'Updated Product Name'],
                'name',
                'Updated Product Name',
            ],
            'update price only' => [
                ['unit_price' => 250.00],
                'unit_price',
                250.00,
            ],
            'update multiple fields' => [
                ['name' => 'New Name', 'unit_price' => 199.99, 'description' => 'New description'],
                'name',
                'New Name',
            ],
        ];
    }

    /**
     * Data provider for product creation with different fields.
     *
     * @return array<string, array{0: array<string, mixed>}>
     */
    public static function productCreationProvider(): array
    {
        return [
            'basic product' => [
                ['name' => 'Basic Product', 'min_stock_level' => 5],
            ],
            'product with high price' => [
                ['name' => 'Premium Product', 'unit_price' => 999.99, 'unit_cost' => 500.00],
            ],
            'product with custom sku' => [
                ['name' => 'Custom SKU Product', 'sku' => 'CUSTOM-SKU-001'],
            ],
        ];
    }

    /**
     * Data provider for role-based field visibility tests.
     *
     * @return array<string, array{role: string, visibleFields: array<int, string>, hiddenFields: array<int, string>, assertFragment: array<string, mixed>}>
     */
    public static function roleFieldVisibilityProvider(): array
    {
        return [
            'Admin sees all fields including sensitive data' => [
                'role' => 'Admin',
                'visibleFields' => [
                    'id', 'name', 'sku', 'description', 'category_id',
                    'created_at', 'updated_at', 'unit_price', 'supplier_id',
                    'valuation_strategy', 'unit_cost', 'margin', 'margin_percentage',
                ],
                'hiddenFields' => [],
                'assertFragment' => ['unit_cost' => 50.00, 'margin' => 50.00],
            ],
            'Worker sees operational fields but not sensitive financial data' => [
                'role' => 'Worker',
                'visibleFields' => [
                    'id', 'name', 'sku', 'description', 'category_id',
                    'created_at', 'updated_at', 'unit_price', 'supplier_id',
                    'valuation_strategy',
                ],
                'hiddenFields' => ['unit_cost', 'margin', 'margin_percentage'],
                'assertFragment' => ['unit_price' => 100.00],
            ],
            'Viewer sees only public fields' => [
                'role' => 'Viewer',
                'visibleFields' => [
                    'id', 'name', 'sku', 'description', 'category_id',
                    'created_at', 'updated_at',
                ],
                'hiddenFields' => ['unit_price', 'unit_cost', 'margin', 'margin_percentage'],
                'assertFragment' => [],
            ],
        ];
    }

    /**
     * Data provider for product deletion restriction tests.
     *
     * @return array<string, array{expectedMessage: string}>
     */
    public static function productDeletionRestrictionProvider(): array
    {
        return [
            'cannot delete product with inventory' => [
                'expectedMessage' => 'Cannot delete product because it has inventory records. Adjust inventory to zero first.',
            ],
            'cannot delete product with stock movements' => [
                'expectedMessage' => 'Cannot delete product because it has inventory records. Adjust inventory to zero first.',
            ],
            'cannot delete product with active alerts' => [
                'expectedMessage' => 'Cannot delete product because it has active restock alerts. Resolve alerts first.',
            ],
        ];
    }

    /**
     * Data provider for product permissions tests.
     *
     * @return array<string, array{0: string, 1: string, 2: string, 3: int}>
     */
    public static function productPermissionsProvider(): array
    {
        return [
            'Admin can list products' => ['Admin', 'GET', 'products.index', 200],
            'Admin can show product' => ['Admin', 'GET', 'products.show', 200],
            'Admin can create product' => ['Admin', 'POST', 'products.store', 201],
            'Admin can update product' => ['Admin', 'PUT', 'products.update', 200],
            'Admin can delete product' => ['Admin', 'DELETE', 'products.destroy', 200],
            'Worker can list products' => ['Worker', 'GET', 'products.index', 200],
            'Worker can show product' => ['Worker', 'GET', 'products.show', 200],
            'Worker cannot create product' => ['Worker', 'POST', 'products.store', 403],
            'Worker cannot update product' => ['Worker', 'PUT', 'products.update', 403],
            'Worker cannot delete product' => ['Worker', 'DELETE', 'products.destroy', 403],
            'Viewer can list products' => ['Viewer', 'GET', 'products.index', 200],
            'Viewer can show product' => ['Viewer', 'GET', 'products.show', 200],
            'Viewer cannot create product' => ['Viewer', 'POST', 'products.store', 403],
            'Viewer cannot update product' => ['Viewer', 'PUT', 'products.update', 403],
            'Viewer cannot delete product' => ['Viewer', 'DELETE', 'products.destroy', 403],
        ];
    }
}
