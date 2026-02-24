<?php
declare(strict_types=1);

namespace Tests\Unit\Repositories;

use App\Models\Product;
use App\Repositories\ProductRepository;
use Tests\TestCase;
use Tests\Traits\ProductTestTrait;

class ProductRepositoryTest extends TestCase
{
    use ProductTestTrait;

    private ProductRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new ProductRepository();
    }

    /**
     * @dataProvider paginationProvider
     */
    public function test_get_all_returns_paginated_products(int $perPage): void
    {
        $entities = $this->createCategoryAndSupplier();
        $this->createProductsForPagination($entities->category->id, $entities->supplier->id, 20);

        $result = $this->repository->getAll($perPage);

        $this->assertCount($perPage, $result->items());
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

    /**
     * @dataProvider productCreationProvider
     */
    public function test_create_product(array $overrides): void
    {
        $entities = $this->createProductEntities();
        $data = $this->getProductData($entities->category->id, $entities->supplier->id, $overrides);

        $result = $this->repository->create($data);

        $this->assertInstanceOf(Product::class, $result);
        $this->assertProductAttributes($result, $overrides);
    }

    /**
     * @dataProvider productUpdateProvider
     */
    public function test_update_product(array $data, string $checkField, mixed $expectedValue): void
    {
        $product = $this->createProduct();

        $result = $this->repository->update($product, $data);

        $this->assertEquals($expectedValue, $result->{$checkField});
    }

    public function test_delete_product(): void
    {
        $product = $this->createProduct();

        $result = $this->repository->delete($product);

        $this->assertTrue($result);
        $this->assertNull(Product::find($product->id));
    }

    public function test_get_products_by_supplier_returns_correct_products(): void
    {
        $suppliers = $this->createSuppliers(2);
        $this->createProductsForSupplier($suppliers->first()->id, 3);
        $this->createProductsForSupplier($suppliers->last()->id, 2);

        $result = $this->repository->getProductsBySupplier($suppliers->first());

        $this->assertCount(3, $result);
        $result->each(fn ($product) => $this->assertEquals(
            $suppliers->first()->id,
            $product->supplier_id
        ));
    }

    public function test_get_products_by_supplier_returns_empty_collection_when_no_products(): void
    {
        $supplier = $this->createSupplier();

        $result = $this->repository->getProductsBySupplier($supplier);

        $this->assertCount(0, $result);
    }

    public function test_get_products_by_supplier_loads_category_and_inventories_relations(): void
    {
        $supplier = $this->createSupplier();
        $warehouse = $this->createWarehouse();
        $products = $this->createProductsForSupplier($supplier->id, 2);
        $products->each(fn ($product) => $this->createInventory($product->id, $warehouse->id, 50));

        $result = $this->repository->getProductsBySupplier($supplier);

        $this->assertTrue($result->first()->relationLoaded('category'));
        $this->assertTrue($result->first()->relationLoaded('inventories'));
    }

    public function test_get_products_by_category_returns_correct_products(): void
    {
        $categories = $this->createCategories(2);
        $this->createProductsForCategory($categories->first()->id, 3);
        $this->createProductsForCategory($categories->last()->id, 2);

        $result = $this->repository->getProductsByCategory($categories->first());

        $this->assertCount(3, $result);
        $result->each(fn ($product) => $this->assertEquals(
            $categories->first()->id,
            $product->category_id
        ));
    }

    public function test_get_products_by_category_returns_empty_collection_when_no_products(): void
    {
        $category = $this->createCategory();

        $result = $this->repository->getProductsByCategory($category);

        $this->assertCount(0, $result);
    }

    public function test_get_products_by_category_loads_supplier_and_inventories_relations(): void
    {
        $category = $this->createCategory();
        $warehouse = $this->createWarehouse();
        $products = $this->createProductsForCategory($category->id, 2);
        $products->each(fn ($product) => $this->createInventory($product->id, $warehouse->id, 50));

        $result = $this->repository->getProductsByCategory($category);

        $this->assertTrue($result->first()->relationLoaded('supplier'));
        $this->assertTrue($result->first()->relationLoaded('inventories'));
    }

    public function test_get_products_by_warehouse(): void
    {
        $warehouses = $this->createWarehouses(2);
        $expected = $this->createProductWithInventory($warehouses->first()->id, 100);
        $this->createProductWithInventory($warehouses->last()->id, 50);

        $result = $this->repository->getProductsByWarehouse($warehouses->first());

        $this->assertCount(1, $result);
        $this->assertEquals($expected->product->id, $result->first()->id);
    }

    /**
     * @dataProvider valuationStrategyProvider
     */
    public function test_create_with_different_valuation_strategies(string $strategy): void
    {
        $entities = $this->createProductEntities();
        $data = $this->getProductData(
            $entities->category->id,
            $entities->supplier->id,
            [
                'name' => "Product with $strategy",
                'sku' => 'SKU-' . strtoupper($strategy) . '-' . uniqid(),
                'valuation_strategy' => $strategy,
            ]
        );

        $result = $this->repository->create($data);

        $this->assertEquals($strategy, $result->valuation_strategy);
    }
}
