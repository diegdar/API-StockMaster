<?php
declare(strict_types=1);

namespace Tests\Unit\Repositories\Traits;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;

trait ProductTestTrait
{
    /**
     * Get default product data for creation.
     *
     * @param int $categoryId
     * @param int $supplierId
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    protected function getProductData(int $categoryId, int $supplierId, array $overrides = []): array
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
            'min_stock_level' => 10,
        ], $overrides);
    }

    /**
     * Data provider for valuation strategies.
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

    /**
     * Create product entities (category and supplier) and return them with IDs.
     *
     * @return object{category: Category, supplier: Supplier, categoryId: int, supplierId: int}
     */
    protected function createProductEntities(): object
    {
        $category = Category::factory()->create();
        $supplier = Supplier::factory()->create();

        return (object) [
            'category' => $category,
            'supplier' => $supplier,
            'categoryId' => $category->id,
            'supplierId' => $supplier->id,
        ];
    }

    /**
     * Create multiple products with specific count for testing pagination.
     *
     * @param int $categoryId
     * @param int $supplierId
     * @param int $count
     * @return void
     */
    protected function createProductsForPagination(int $categoryId, int $supplierId, int $count): void
    {
        Product::factory()->count($count)->create([
            'category_id' => $categoryId,
            'supplier_id' => $supplierId,
        ]);
    }
}
