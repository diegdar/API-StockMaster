<?php
declare(strict_types=1);

namespace Tests\Unit\Repositories;

use App\Models\Product;
use App\Repositories\ProductRepository;
use Tests\TestCase;
use Tests\Unit\Repositories\Traits\RepositoryTestTrait;

class ProductRepositoryTest extends TestCase
{
    use RepositoryTestTrait;

    private ProductRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new ProductRepository();
    }

    public function test_get_all_returns_paginated_products(): void
    {
        $entities = $this->createCategoryAndSupplier();

        Product::factory()->count(20)->create([
            'category_id' => $entities->category->id,
            'supplier_id' => $entities->supplier->id,
        ]);

        $result = $this->repository->getAll(5);

        $this->assertCount(5, $result->items());
        $this->assertTrue($result->hasPages());
    }

    public function test_find_by_id_returns_product_with_relations(): void
    {
        $product = $this->createProduct();

        $result = $this->repository->findById($product->id);

        $this->assertNotNull($result);
        $this->assertEquals($product->id, $result->id);
    }

    public function test_find_by_id_returns_null_when_not_found(): void
    {
        $result = $this->repository->findById(9999);

        $this->assertNull($result);
    }

    public function test_create_product(): void
    {
        $entities = $this->createCategoryAndSupplier();

        $data = [
            'name' => 'New Product',
            'sku' => 'NEW-SKU-001',
            'description' => 'New description',
            'unit_price' => 100.00,
            'unit_cost' => 50.00,
            'category_id' => $entities->category->id,
            'supplier_id' => $entities->supplier->id,
            'valuation_strategy' => 'fifo',
            'min_stock_level' => 10,
        ];

        $result = $this->repository->create($data);

        $this->assertInstanceOf(Product::class, $result);
        $this->assertEquals('New Product', $result->name);
        $this->assertEquals('NEW-SKU-001', $result->sku);
    }

    public function test_update_product(): void
    {
        $product = $this->createProduct();

        $data = [
            'name' => 'Updated Name',
            'unit_price' => 200.00,
        ];

        $result = $this->repository->update($product, $data);

        $this->assertEquals('Updated Name', $result->name);
        $this->assertEquals(200.00, $result->unit_price);
    }

    public function test_delete_product(): void
    {
        $product = $this->createProduct();

        $result = $this->repository->delete($product);

        $this->assertTrue($result);
        $this->assertNull(Product::find($product->id));
    }

    public function test_get_products_by_supplier(): void
    {
        $suppliers = $this->createSuppliers(2);
        $supplier1 = $suppliers->first();
        $supplier2 = $suppliers->last();

        $this->createProductsForSupplier($supplier1->id, 3);
        $this->createProductsForSupplier($supplier2->id, 2);

        $result = $this->repository->getProductsBySupplier($supplier1->id);

        $this->assertCount(3, $result);
    }

    public function test_get_products_by_category(): void
    {
        $categories = $this->createCategories(2);
        $category1 = $categories->first();
        $category2 = $categories->last();

        $this->createProductsForCategory($category1->id, 3);
        $this->createProductsForCategory($category2->id, 2);

        $result = $this->repository->getProductsByCategory($category1->id);

        $this->assertCount(3, $result);
    }

    public function test_get_products_by_warehouse(): void
    {
        $warehouses = $this->createWarehouses(2);
        $warehouse1 = $warehouses->first();
        $warehouse2 = $warehouses->last();

        $result1 = $this->createProductWithInventory($warehouse1->id, 100);
        $this->createProductWithInventory($warehouse2->id, 50);

        $result = $this->repository->getProductsByWarehouse($warehouse1->id);

        $this->assertCount(1, $result);
        $this->assertEquals($result1->product->id, $result->first()->id);
    }

    public static function productDataProvider(): array
    {
        return [
            'product with fifo' => ['fifo'],
            'product with lifo' => ['lifo'],
            'product with avg' => ['avg'],
        ];
    }

    /**
     * @dataProvider productDataProvider
     */
    public function test_create_with_different_valuation_strategies(string $strategy): void
    {
        $entities = $this->createCategoryAndSupplier();

        $data = [
            'name' => "Product with $strategy",
            'sku' => 'SKU-' . strtoupper($strategy) . '-' . uniqid(),
            'description' => 'Test',
            'unit_price' => 100.00,
            'unit_cost' => 50.00,
            'category_id' => $entities->category->id,
            'supplier_id' => $entities->supplier->id,
            'valuation_strategy' => $strategy,
            'min_stock_level' => 5,
        ];

        $result = $this->repository->create($data);

        $this->assertEquals($strategy, $result->valuation_strategy);
    }
}
