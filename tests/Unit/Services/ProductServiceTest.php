<?php
declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Exceptions\DeletionException;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\RestockAlert;
use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Services\ProductService;
use Illuminate\Pagination\LengthAwarePaginator;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;
use Tests\Traits\ProductTestTrait;

class ProductServiceTest extends TestCase
{
    use ProductTestTrait;

    private ProductService $service;
    private ProductRepositoryInterface|MockInterface $repositoryMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repositoryMock = Mockery::mock(ProductRepositoryInterface::class);
        $this->service = new ProductService($this->repositoryMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * @dataProvider paginationProvider
     */
    public function test_get_all_returns_paginated_products(int $perPage): void
    {
        $paginator = new LengthAwarePaginator([], 0, $perPage);
        $this->repositoryMock->shouldReceive('getAll')->with($perPage)->andReturn($paginator);

        $result = $this->service->getAll($perPage);

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertEquals($perPage, $result->perPage());
    }

    /**
     * @dataProvider productCreationProvider
     */
    public function test_create_product(array $overrides): void
    {
        $entities = $this->createProductEntities();
        $data = $this->getProductData($entities->category->id, $entities->supplier->id, $overrides);
        $product = Product::factory()->make($data);

        $this->repositoryMock->shouldReceive('create')->with($data)->andReturn($product);

        $result = $this->service->create($data);

        $this->assertInstanceOf(Product::class, $result);
    }

    /**
     * @dataProvider productUpdateProvider
     */
    public function test_update_product(array $data, string $checkField, mixed $expectedValue): void
    {
        $product = $this->createProduct();
        $updatedProduct = clone $product;
        $updatedProduct->{$checkField} = $expectedValue;

        $this->repositoryMock->shouldReceive('update')->with($product, $data)->andReturn($updatedProduct);

        $result = $this->service->update($product, $data);

        $this->assertEquals($expectedValue, $result->{$checkField});
    }

    public function test_delete_product_without_relations(): void
    {
        $product = $this->createProduct();

        $this->repositoryMock->shouldReceive('delete')->with($product)->once();

        $this->service->delete($product);

        $this->assertTrue(true);
    }

    public function test_delete_throws_exception_with_inventory(): void
    {
        $warehouse = Warehouse::factory()->create();
        $product = $this->createProduct();
        Inventory::factory()->create([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 100,
        ]);

        $this->expectException(DeletionException::class);
        $this->expectExceptionMessage('Cannot delete product because it has inventory records. Adjust inventory to zero first.');

        $this->service->delete($product);
    }

    public function test_delete_throws_exception_with_stock_movements(): void
    {
        $warehouse = Warehouse::factory()->create();
        $user = $this->createTestEntities()->user;
        $product = $this->createProduct();

        // Create stock movement without triggering observer (which would create inventory)
        StockMovement::withoutEvents(function () use ($product, $warehouse, $user) {
            StockMovement::factory()->create([
                'product_id' => $product->id,
                'warehouse_id' => $warehouse->id,
                'user_id' => $user->id,
                'type' => 'in',
                'quantity' => 50,
            ]);
        });

        $this->expectException(DeletionException::class);
        $this->expectExceptionMessage('Cannot delete product because it has stock movement records. This data is required for auditing.');

        $this->service->delete($product);
    }

    public function test_delete_throws_exception_with_active_alerts(): void
    {
        $warehouse = Warehouse::factory()->create();
        $product = $this->createProduct();
        RestockAlert::factory()->create([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'is_active' => true,
            'threshold' => 10,
        ]);

        $this->expectException(DeletionException::class);
        $this->expectExceptionMessage('Cannot delete product because it has active restock alerts. Resolve alerts first.');

        $this->service->delete($product);
    }

    public function test_get_products_by_warehouse(): void
    {
        $warehouse = Warehouse::factory()->create();
        $products = collect([$this->createProduct()]);

        $this->repositoryMock->shouldReceive('getProductsByWarehouse')->with($warehouse)->andReturn($products);

        $result = $this->service->getProductsByWarehouse($warehouse);

        $this->assertCount(1, $result);
    }
}
