<?php
declare(strict_types=1);

namespace Tests\Unit\Repositories;

use App\Models\StockMovement;
use App\Repositories\Contracts\StockMovementRepositoryInterface;
use Tests\TestCase;
use Tests\Unit\Repositories\Traits\RepositoryTestTrait;

class StockMovementRepositoryTest extends TestCase
{
    use RepositoryTestTrait;

    private StockMovementRepositoryInterface $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(StockMovementRepositoryInterface::class);
    }

    public function test_create_stock_movement(): void
    {
        $entities = $this->createTestEntities();

        $data = [
            'product_id' => $entities->product->id,
            'warehouse_id' => $entities->warehouse->id,
            'type' => 'in',
            'quantity' => 100,
            'description' => 'Initial stock',
            'user_id' => $entities->user->id,
        ];

        $result = $this->repository->create($data);

        $this->assertStockMovementExists(
            $entities->product->id,
            $entities->warehouse->id,
            'in',
            100
        );
        $this->assertEquals('in', $result->type);
        $this->assertEquals(100, $result->quantity);
    }

    public function test_create_out_movement(): void
    {
        $entities = $this->createTestEntities();

        $result = $this->repository->createOutMovement(
            $entities->product->id,
            $entities->warehouse->id,
            50,
            'Stock removal',
            $entities->user->id
        );

        $this->assertStockMovementExists(
            $entities->product->id,
            $entities->warehouse->id,
            'out',
            50,
            'Stock removal'
        );
        $this->assertEquals('out', $result->type);
    }

    public function test_create_in_movement(): void
    {
        $entities = $this->createTestEntities();

        $result = $this->repository->createInMovement(
            $entities->product->id,
            $entities->warehouse->id,
            75,
            'Stock addition',
            $entities->user->id
        );

        $this->assertStockMovementExists(
            $entities->product->id,
            $entities->warehouse->id,
            'in',
            75,
            'Stock addition'
        );
        $this->assertEquals('in', $result->type);
    }

    public function test_get_by_product_and_warehouse(): void
    {
        $entities = $this->createTestEntities();

        StockMovement::factory()->count(3)->create([
            'product_id' => $entities->product->id,
            'warehouse_id' => $entities->warehouse->id,
            'user_id' => $entities->user->id,
        ]);

        $result = $this->repository->getByProductAndWarehouse(
            $entities->product->id,
            $entities->warehouse->id
        );

        $this->assertEquals(3, $result->count());
    }

    public function test_get_by_product_and_warehouse_returns_empty_collection(): void
    {
        $result = $this->repository->getByProductAndWarehouse(999, 999);

        $this->assertEquals(0, $result->count());
    }

    public function test_get_stock_quantity(): void
    {
        $entities = $this->createTestEntities();

        $this->createInventory(
            $entities->product->id,
            $entities->warehouse->id,
            150
        );

        $result = $this->repository->getStockQuantity(
            $entities->product->id,
            $entities->warehouse->id
        );

        $this->assertEquals(150, $result);
    }

    public function test_get_stock_quantity_returns_zero_when_no_inventory(): void
    {
        $result = $this->repository->getStockQuantity(999, 999);

        $this->assertEquals(0, $result);
    }

    public function test_create_transfer_movements(): void
    {
        $entities = $this->createTestEntities();
        $warehouses = $this->createTransferWarehouses();

        $result = $this->repository->createTransferMovements(
            $entities->product->id,
            $warehouses->sourceWarehouse->id,
            $warehouses->destinationWarehouse->id,
            25,
            'Transfer out',
            'Transfer in',
            $entities->user->id
        );

        $this->assertArrayHasKey('out', $result);
        $this->assertArrayHasKey('in', $result);
        $this->assertEquals('out', $result['out']->type);
        $this->assertEquals('in', $result['in']->type);
        $this->assertEquals($warehouses->sourceWarehouse->id, $result['out']->warehouse_id);
        $this->assertEquals($warehouses->destinationWarehouse->id, $result['in']->warehouse_id);
        $this->assertEquals(25, $result['out']->quantity);
        $this->assertEquals(25, $result['in']->quantity);

        $this->assertStockMovementExists(
            $entities->product->id,
            $warehouses->sourceWarehouse->id,
            'out',
            25
        );

        $this->assertStockMovementExists(
            $entities->product->id,
            $warehouses->destinationWarehouse->id,
            'in',
            25
        );
    }
}
