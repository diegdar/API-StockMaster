<?php
declare(strict_types=1);

namespace Tests\Unit\Repositories;

use App\Models\Warehouse;
use App\Repositories\Contracts\WarehouseRepositoryInterface;
use Tests\TestCase;
use Tests\Traits\EntityCreationTrait;

class WarehouseRepositoryTest extends TestCase
{
    use EntityCreationTrait;

    private WarehouseRepositoryInterface $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(WarehouseRepositoryInterface::class);
    }

    public function test_get_all_returns_paginated_warehouses(): void
    {
        $this->createWarehouses(20);

        $result = $this->repository->getAll(15);

        $this->assertEquals(15, $result->count());
        $this->assertEquals(20, $result->total());
    }

    public function test_create_warehouse(): void
    {
        $data = [
            'name' => 'New Warehouse',
            'location' => 'New Location',
            'capacity' => 50000,
            'is_active' => true,
        ];

        $result = $this->repository->create($data);

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

        $data = [
            'name' => 'Updated Name',
            'capacity' => 60000,
        ];

        $result = $this->repository->update($warehouse, $data);

        $this->assertEquals('Updated Name', $result->name);
        $this->assertEquals(60000, $result->capacity);
        $this->assertEquals('updated-name', $result->slug);
    }

    public function test_delete_warehouse(): void
    {
        $warehouse = Warehouse::factory()->create();

        $this->repository->delete($warehouse);

        $this->assertDatabaseMissing('warehouses', [
            'id' => $warehouse->id,
        ]);
    }

    public function test_get_warehouses_with_inventory_count(): void
    {
        $warehouses = $this->createWarehouses(2);
        $warehouse1 = $warehouses->first();
        $warehouse2 = $warehouses->last();

        $result1 = $this->createProductWithInventory($warehouse1->id);
        $this->createProductWithInventory($warehouse1->id);
        $this->createProductWithInventory($warehouse2->id);

        $result = $this->repository->getWarehousesWithInventoryCount();

        $this->assertCount(2, $result);
        $wh1 = $result->firstWhere('id', $warehouse1->id);
        $wh2 = $result->firstWhere('id', $warehouse2->id);
        $this->assertEquals(2, $wh1->inventories_count);
        $this->assertEquals(1, $wh2->inventories_count);
    }

    public function test_find_by_id_returns_warehouse(): void
    {
        $warehouse = Warehouse::factory()->create([
            'name' => 'Test Warehouse',
            'location' => 'Test Location',
        ]);

        $result = $this->repository->findById($warehouse->id);

        $this->assertNotNull($result);
        $this->assertEquals($warehouse->id, $result->id);
        $this->assertEquals('Test Warehouse', $result->name);
    }

    public function test_find_by_id_returns_null_when_not_found(): void
    {
        $result = $this->repository->findById(99999);

        $this->assertNull($result);
    }

    public function test_get_available_capacity(): void
    {
        $warehouse = Warehouse::factory()->create(['capacity' => 1000]);
        $result = $this->createProductWithInventory($warehouse->id, 300);

        $availableCapacity = $this->repository->getAvailableCapacity($warehouse->id);

        $this->assertEquals(700, $availableCapacity);
    }

    public function test_get_available_capacity_returns_null_when_no_capacity_limit(): void
    {
        $warehouse = Warehouse::factory()->create(['capacity' => null]);

        $result = $this->repository->getAvailableCapacity($warehouse->id);

        $this->assertNull($result);
    }

    public function test_get_available_capacity_returns_null_when_warehouse_not_found(): void
    {
        $result = $this->repository->getAvailableCapacity(99999);

        $this->assertNull($result);
    }
}
