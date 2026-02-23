<?php
declare(strict_types=1);

namespace Tests\Traits;

use App\Models\Category;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\RestockAlert;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\Warehouse;

trait ProductTestTrait
{
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
     * Create product entities (product, category, supplier, and warehouse) with overrides.
     *
     * @param array<string, mixed> $productOverrides
     * @param array<string, mixed> $supplierOverrides
     * @param array<string, mixed> $categoryOverrides
     * @param array<string, mixed> $warehouseOverrides
     * @return object{product: Product, category: Category, supplier: Supplier, warehouse: Warehouse}
     */
    protected function createProductEntities(
        array $productOverrides = [],
        array $supplierOverrides = [],
        array $categoryOverrides = [],
        array $warehouseOverrides = []
    ): object {
        $category = Category::factory()->create($categoryOverrides);
        $supplier = Supplier::factory()->create($supplierOverrides);
        $warehouse = Warehouse::factory()->create($warehouseOverrides);

        $product = Product::factory()->create(array_merge([
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
            'unit_price' => 100.00,
            'unit_cost' => 50.00,
        ], $productOverrides));

        return (object) [
            'product' => $product,
            'category' => $category,
            'supplier' => $supplier,
            'warehouse' => $warehouse,
        ];
    }

    /**
     * Create product with inventory for deletion tests.
     *
     * @return object{product: Product, warehouse: Warehouse}
     */
    protected function createProductWithInventory(): object
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
    protected function createProductWithStockMovement(
        int $userId,
        array $stockMovementOverrides = []
    ): object
    {
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
     * @return object{product: Product, warehouse: Warehouse}
     */
    protected function createProductWithRestockAlert(
        array $stockMovementOverrides = []
    ): object
    {
        $entities = $this->createProductEntities();

        RestockAlert::factory()->create(array_merge([
            'product_id' => $entities->product->id,
            'warehouse_id' => $entities->warehouse->id,
            'is_active' => true,
            'threshold' => 10,
        ], $stockMovementOverrides));

        return (object) [
            'product' => $entities->product,
            'warehouse' => $entities->warehouse,
        ];
    }
}
