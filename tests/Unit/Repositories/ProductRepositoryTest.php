<?php
declare(strict_types=1);

namespace Tests\Unit\Repositories;

use App\Models\Product;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Models\Inventory;
use App\Repositories\ProductRepository;

use Tests\TestCase;

class ProductRepositoryTest extends TestCase
{

    private ProductRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new ProductRepository();
    }

    public function test_get_all_returns_paginated_products(): void
    {
        $category = Category::factory()->create();
        $supplier = Supplier::factory()->create();

        Product::factory()->count(20)->create([
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
        ]);

        $result = $this->repository->getAll(5);

        $this->assertCount(5, $result->items());
        $this->assertTrue($result->hasPages());
    }

    public function test_find_by_id_returns_product_with_relations(): void
    {
        $category = Category::factory()->create();
        $supplier = Supplier::factory()->create();

        $product = Product::factory()->create([
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
        ]);

        $result = $this->repository->findById($product->id);

        $this->assertNotNull($result);
        $this->assertEquals($product->id, $result->id);
    }

    public function test_find_by_id_returns_null_when_not_found(): void
    {
        $result = $this->repository->findById(9999);

        $this->assertNull($result);
    }

    public function test_find_by_sku_returns_product(): void
    {
        $category = Category::factory()->create();
        $supplier = Supplier::factory()->create();

        $product = Product::factory()->create([
            'sku' => 'UNIQUE-SKU-123',
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
        ]);

        $result = $this->repository->findBySku('UNIQUE-SKU-123');

        $this->assertNotNull($result);
        $this->assertEquals($product->id, $result->id);
    }

    public function test_find_by_sku_returns_null_when_not_found(): void
    {
        $result = $this->repository->findBySku('NON-EXISTENT-SKU');

        $this->assertNull($result);
    }

    public function test_create_product(): void
    {
        $category = Category::factory()->create();
        $supplier = Supplier::factory()->create();

        $data = [
            'name' => 'New Product',
            'sku' => 'NEW-SKU-001',
            'description' => 'New description',
            'unit_price' => 100.00,
            'unit_cost' => 50.00,
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
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
        $category = Category::factory()->create();
        $supplier = Supplier::factory()->create();

        $product = Product::factory()->create([
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
        ]);

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
        $category = Category::factory()->create();
        $supplier = Supplier::factory()->create();

        $product = Product::factory()->create([
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
        ]);

        $result = $this->repository->delete($product);

        $this->assertTrue($result);
        $this->assertNull(Product::find($product->id));
    }

    public function test_get_products_by_supplier(): void
    {
        $category = Category::factory()->create();
        $supplier1 = Supplier::factory()->create();
        $supplier2 = Supplier::factory()->create();

        Product::factory()->count(3)->create([
            'category_id' => $category->id,
            'supplier_id' => $supplier1->id,
        ]);

        Product::factory()->count(2)->create([
            'category_id' => $category->id,
            'supplier_id' => $supplier2->id,
        ]);

        $result = $this->repository->getProductsBySupplier($supplier1->id);

        $this->assertCount(3, $result);
    }

    public function test_get_products_by_category(): void
    {
        $category1 = Category::factory()->create();
        $category2 = Category::factory()->create();
        $supplier = Supplier::factory()->create();

        Product::factory()->count(3)->create([
            'category_id' => $category1->id,
            'supplier_id' => $supplier->id,
        ]);

        Product::factory()->count(2)->create([
            'category_id' => $category2->id,
            'supplier_id' => $supplier->id,
        ]);

        $result = $this->repository->getProductsByCategory($category1->id);

        $this->assertCount(3, $result);
    }

    public function test_get_products_by_warehouse(): void
    {
        $category = Category::factory()->create();
        $supplier = Supplier::factory()->create();
        $warehouse1 = Warehouse::factory()->create();
        $warehouse2 = Warehouse::factory()->create();

        $product1 = Product::factory()->create([
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
        ]);

        $product2 = Product::factory()->create([
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
        ]);

        Inventory::factory()->create([
            'product_id' => $product1->id,
            'warehouse_id' => $warehouse1->id,
            'quantity' => 100,
        ]);

        Inventory::factory()->create([
            'product_id' => $product2->id,
            'warehouse_id' => $warehouse2->id,
            'quantity' => 50,
        ]);

        $result = $this->repository->getProductsByWarehouse($warehouse1->id);

        $this->assertCount(1, $result);
        $this->assertEquals($product1->id, $result->first()->id);
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
        $category = Category::factory()->create();
        $supplier = Supplier::factory()->create();

        $data = [
            'name' => "Product with $strategy",
            'sku' => 'SKU-' . strtoupper($strategy) . '-' . uniqid(),
            'description' => 'Test',
            'unit_price' => 100.00,
            'unit_cost' => 50.00,
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
            'valuation_strategy' => $strategy,
            'min_stock_level' => 5,
        ];

        $result = $this->repository->create($data);

        $this->assertEquals($strategy, $result->valuation_strategy);
    }
}
