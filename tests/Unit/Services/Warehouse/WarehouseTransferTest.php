<?php
declare(strict_types=1);

namespace Tests\Unit\Services\Warehouse;

use App\DTO\Warehouse\TransferStockDTO;
use App\Exceptions\InsufficientCapacityException;
use App\Exceptions\InsufficientStockException;
use App\Models\Inventory;
use App\Models\StockMovement;
use App\Services\WarehouseService;
use Tests\TestCase;
use Tests\Traits\WarehouseTestTrait;

class WarehouseTransferTest extends TestCase
{
    use WarehouseTestTrait;

    private WarehouseService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(WarehouseService::class);
    }

    public function test_transfer_between_warehouses_succeeds(): void
    {
        $setup = $this->setupTransferTest(100);

        $dto = new TransferStockDTO(
            productId: $setup->product->id,
            sourceWarehouseId: $setup->sourceWarehouse->id,
            destinationWarehouseId: $setup->destinationWarehouse->id,
            quantity: 30,
            description: 'Test transfer'
        );

        $result = $this->service->transferBetweenWarehouses($dto);

        $this->assertTransferResponse($result, $setup->product, $setup->sourceWarehouse, $setup->destinationWarehouse, 30);
        $this->assertInventoryQuantities($setup->product->id, $setup->sourceWarehouse->id, 70, $setup->destinationWarehouse->id, 30);
    }

    public function test_transfer_throws_insufficient_stock_exception(): void
    {
        $setup = $this->setupTransferTest(10);

        $dto = new TransferStockDTO(
            productId: $setup->product->id,
            sourceWarehouseId: $setup->sourceWarehouse->id,
            destinationWarehouseId: $setup->destinationWarehouse->id,
            quantity: 50,
            description: null
        );

        $this->expectException(InsufficientStockException::class);
        $this->expectExceptionMessage('Insufficient stock. Available: 10, Requested: 50');

        $this->service->transferBetweenWarehouses($dto);
    }

    public function test_transfer_throws_exception_when_no_inventory_exists(): void
    {
        $setup = $this->setupTransferTestWithoutInventory();

        $dto = new TransferStockDTO(
            productId: $setup->product->id,
            sourceWarehouseId: $setup->sourceWarehouse->id,
            destinationWarehouseId: $setup->destinationWarehouse->id,
            quantity: 10,
            description: null
        );

        $this->expectException(InsufficientStockException::class);
        $this->expectExceptionMessage('Insufficient stock. Available: 0, Requested: 10');

        $this->service->transferBetweenWarehouses($dto);
    }

    public function test_transfer_creates_correct_movement_descriptions(): void
    {
        $setup = $this->setupTransferTest(100, 'Main Warehouse', 'Secondary Warehouse');

        $dto = new TransferStockDTO(
            productId: $setup->product->id,
            sourceWarehouseId: $setup->sourceWarehouse->id,
            destinationWarehouseId: $setup->destinationWarehouse->id,
            quantity: 20,
            description: 'Routine stock transfer'
        );

        $result = $this->service->transferBetweenWarehouses($dto);

        $this->assertStringContainsString('Transfer to Secondary Warehouse', $result['movements']['out']['description']);
        $this->assertStringContainsString('Routine stock transfer', $result['movements']['out']['description']);
        $this->assertStringContainsString('Transfer from Main Warehouse', $result['movements']['in']['description']);
        $this->assertStringContainsString('Routine stock transfer', $result['movements']['in']['description']);
    }

    public function test_transfer_without_custom_description(): void
    {
        $setup = $this->setupTransferTest(100, 'Warehouse A', 'Warehouse B');

        $dto = new TransferStockDTO(
            productId: $setup->product->id,
            sourceWarehouseId: $setup->sourceWarehouse->id,
            destinationWarehouseId: $setup->destinationWarehouse->id,
            quantity: 20,
            description: null
        );

        $result = $this->service->transferBetweenWarehouses($dto);

        $this->assertEquals('Transfer to Warehouse B', $result['movements']['out']['description']);
        $this->assertEquals('Transfer from Warehouse A', $result['movements']['in']['description']);
    }

    public function test_transfer_is_wrapped_in_transaction(): void
    {
        $setup = $this->setupTransferTest(10);

        $dto = new TransferStockDTO(
            productId: $setup->product->id,
            sourceWarehouseId: $setup->sourceWarehouse->id,
            destinationWarehouseId: $setup->destinationWarehouse->id,
            quantity: 50,
            description: null
        );

        try {
            $this->service->transferBetweenWarehouses($dto);
        } catch (InsufficientStockException) {
            // Expected exception
        }

        $this->assertEquals(0, StockMovement::where('product_id', $setup->product->id)->count());
    }

    public function test_transfer_to_same_warehouse(): void
    {
        $setup = $this->setupSingleWarehouseTransfer(100);

        $dto = new TransferStockDTO(
            productId: $setup->product->id,
            sourceWarehouseId: $setup->warehouse->id,
            destinationWarehouseId: $setup->warehouse->id,
            quantity: 20,
            description: null
        );

        $result = $this->service->transferBetweenWarehouses($dto);

        $this->assertEquals($setup->warehouse->id, $result['source_warehouse']['id']);
        $this->assertEquals($setup->warehouse->id, $result['destination_warehouse']['id']);

        $inventory = Inventory::where('product_id', $setup->product->id)
            ->where('warehouse_id', $setup->warehouse->id)
            ->first();
        $this->assertEquals(100, $inventory->quantity);
    }

    public function test_transfer_with_exact_available_stock(): void
    {
        $setup = $this->setupTransferTest(50);

        $dto = new TransferStockDTO(
            productId: $setup->product->id,
            sourceWarehouseId: $setup->sourceWarehouse->id,
            destinationWarehouseId: $setup->destinationWarehouse->id,
            quantity: 50,
            description: null
        );

        $result = $this->service->transferBetweenWarehouses($dto);

        $this->assertEquals(50, $result['quantity']);
        $this->assertInventoryQuantities($setup->product->id, $setup->sourceWarehouse->id, 0, $setup->destinationWarehouse->id, 50);
    }

    public function test_transfer_throws_insufficient_capacity_exception(): void
    {
        $setup = $this->setupTransferWithCapacity(100, 20);

        $dto = new TransferStockDTO(
            productId: $setup->product->id,
            sourceWarehouseId: $setup->sourceWarehouse->id,
            destinationWarehouseId: $setup->destinationWarehouse->id,
            quantity: 50,
            description: null
        );

        $this->expectException(InsufficientCapacityException::class);
        $this->expectExceptionMessage('Insufficient capacity in destination warehouse. Available: 20, Requested: 50');

        $this->service->transferBetweenWarehouses($dto);
    }

    public function test_transfer_succeeds_with_null_capacity(): void
    {
        $setup = $this->setupTransferWithCapacity(100, null);

        $dto = new TransferStockDTO(
            productId: $setup->product->id,
            sourceWarehouseId: $setup->sourceWarehouse->id,
            destinationWarehouseId: $setup->destinationWarehouse->id,
            quantity: 50,
            description: null
        );

        $result = $this->service->transferBetweenWarehouses($dto);

        $this->assertEquals(50, $result['quantity']);
        $this->assertInventoryQuantities($setup->product->id, $setup->sourceWarehouse->id, 50, $setup->destinationWarehouse->id, 50);
    }

    public function test_transfer_succeeds_with_exact_capacity(): void
    {
        $setup = $this->setupTransferWithCapacity(100, 50);

        $dto = new TransferStockDTO(
            productId: $setup->product->id,
            sourceWarehouseId: $setup->sourceWarehouse->id,
            destinationWarehouseId: $setup->destinationWarehouse->id,
            quantity: 50,
            description: null
        );

        $result = $this->service->transferBetweenWarehouses($dto);

        $this->assertEquals(50, $result['quantity']);
        $this->assertInventoryQuantities($setup->product->id, $setup->sourceWarehouse->id, 50, $setup->destinationWarehouse->id, 50);
    }
}
