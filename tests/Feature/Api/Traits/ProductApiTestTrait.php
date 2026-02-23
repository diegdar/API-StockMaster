<?php
declare(strict_types=1);

namespace Tests\Feature\Api\Traits;

use App\Models\Category;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\RestockAlert;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\Warehouse;
use Illuminate\Testing\Fluent\AssertableJson;

trait ProductApiTestTrait
{
    /**
     * Creates a product with a category, supplier, and warehouse.
     *
     * @return object{product: Product, category: Category, supplier: Supplier, warehouse: Warehouse}
     */
    protected function createProductEntities(): object
    {
        $category = Category::factory()->create();
        $supplier = Supplier::factory()->create();
        $warehouse = Warehouse::factory()->create();

        $product = Product::factory()->create([
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
            'unit_price' => 100.00,
            'unit_cost' => 50.00,
        ]);

        return (object) [
            'product' => $product,
            'category' => $category,
            'supplier' => $supplier,
            'warehouse' => $warehouse,
        ];
    }

    /**
     * Get valid product data for store/update requests.
     *
     * @param int $categoryId
     * @param int $supplierId
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    protected function getProductRequestData(int $categoryId, int $supplierId, array $overrides = []): array
    {
        return array_merge([
            'name' => 'Test Product',
            'sku' => 'TEST-SKU-' . uniqid(),
            'description' => 'Test description',
            'unit_price' => 100.00,
            'unit_cost' => 50.00,
            'category_id' => $categoryId,
            'supplier_id' => $supplierId,
            'valuation_strategy' => 'fifo',
        ], $overrides);
    }

    /**
     * Create product with inventory for deletion tests.
     *
     * @return object{product: Product, warehouse: Warehouse}
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
     * @return object{product: Product, warehouse: Warehouse}
     */
    protected function createProductWithStockMovement(int $userId): object
    {
        $entities = $this->createProductEntities();

        StockMovement::factory()->create([
            'product_id' => $entities->product->id,
            'warehouse_id' => $entities->warehouse->id,
            'user_id' => $userId,
            'type' => 'in',
            'quantity' => 50,
        ]);

        return (object) [
            'product' => $entities->product,
            'warehouse' => $entities->warehouse,
        ];
    }

    /**
     * Create product with active restock alert for deletion tests.
     *
     * @return object{product: Product, warehouse: Warehouse}
     */
    protected function createProductWithRestockAlert(): object
    {
        $entities = $this->createProductEntities();

        RestockAlert::factory()->create([
            'product_id' => $entities->product->id,
            'warehouse_id' => $entities->warehouse->id,
            'is_active' => true,
            'threshold' => 10,
        ]);

        return (object) [
            'product' => $entities->product,
            'warehouse' => $entities->warehouse,
        ];
    }

    /**
     * Data provider for role-based field visibility tests.
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

    /**
     * Assert product JSON structure for given fields.
     *
     * @param AssertableJson $json
     * @param array<int, string> $fields
     */
    protected function assertProductJsonStructure(AssertableJson $json, array $fields): void
    {
        $json->has('data', fn (AssertableJson $json) => $json->hasAll($fields));
    }
}
