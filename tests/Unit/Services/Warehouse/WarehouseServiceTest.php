<?php
declare(strict_types=1);

namespace Tests\Unit\Services\Warehouse;

use App\DTO\Warehouse\CreateWarehouseDTO;
use App\DTO\Warehouse\UpdateWarehouseDTO;
use App\Exceptions\DeletionException;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Warehouse;
use App\Services\WarehouseService;
use Tests\TestCase;

class WarehouseServiceTest extends TestCase
{
    private WarehouseService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(WarehouseService::class);
    }

    public function test_warehouse_service_can_be_resolved(): void
    {
        $service = app(WarehouseService::class);

        $this->assertInstanceOf(WarehouseService::class, $service);
    }

    public function test_delete_warehouse_without_inventory_succeeds(): void
    {
        $warehouse = Warehouse::factory()->create();

        $this->service->delete($warehouse);

        $this->assertDatabaseMissing('warehouses', ['id' => $warehouse->id]);
    }

    public function test_delete_warehouse_with_inventory_throws_exception(): void
    {
        $warehouse = Warehouse::factory()->create();
        $product = Product::factory()->create();
        Inventory::factory()->create([
            'warehouse_id' => $warehouse->id,
            'product_id' => $product->id,
        ]);

        $this->expectException(DeletionException::class);
        $this->expectExceptionMessage('Cannot delete warehouse with existing inventories');

        $this->service->delete($warehouse);
    }

    public function test_get_all_returns_paginated_warehouses(): void
    {
        Warehouse::factory()->count(20)->create();

        $result = $this->service->getAll(15);

        $this->assertEquals(15, $result->count());
        $this->assertEquals(20, $result->total());
    }

    public function test_create_warehouse(): void
    {
        $dto = new CreateWarehouseDTO(
            name: 'New Warehouse',
            location: 'New Location',
            capacity: 50000,
            isActive: true
        );

        $result = $this->service->create($dto);

        $this->assertDatabaseHas('warehouses', [
            'name' => 'New Warehouse',
            'location' => 'New Location',
        ]);
        $this->assertEquals('New Warehouse', $result->name);
        $this->assertEquals('new-warehouse', $result->slug);
    }

    public function test_update_warehouse(): void
    {
        $warehouse = Warehouse::factory()->create([
            'name' => 'Old Name',
            'capacity' => 30000,
        ]);

        $dto = new UpdateWarehouseDTO(
            name: 'Updated Name',
            capacity: 60000
        );

        $result = $this->service->update($warehouse, $dto);

        $this->assertEquals('Updated Name', $result->name);
        $this->assertEquals(60000, $result->capacity);
        $this->assertEquals('updated-name', $result->slug);
    }

    public function test_get_warehouses_with_inventory_count(): void
    {
        $warehouse1 = Warehouse::factory()->create();
        $warehouse2 = Warehouse::factory()->create();
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();
        $product3 = Product::factory()->create();

        Inventory::factory()->create([
            'warehouse_id' => $warehouse1->id,
            'product_id' => $product1->id,
        ]);
        Inventory::factory()->create([
            'warehouse_id' => $warehouse1->id,
            'product_id' => $product2->id,
        ]);
        Inventory::factory()->create([
            'warehouse_id' => $warehouse2->id,
            'product_id' => $product3->id,
        ]);

        $result = $this->service->getWarehousesWithInventoryCount();

        $this->assertCount(2, $result);
        $wh1 = $result->firstWhere('id', $warehouse1->id);
        $wh2 = $result->firstWhere('id', $warehouse2->id);
        $this->assertEquals(2, $wh1->inventories_count);
        $this->assertEquals(1, $wh2->inventories_count);
    }
}
