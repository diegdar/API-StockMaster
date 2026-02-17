<?php
declare(strict_types=1);

namespace Tests\Unit\Repositories;

use App\Models\Warehouse;
use App\Repositories\Contracts\WarehouseRepositoryInterface;

use Tests\TestCase;
use App\Models\Product;
use App\Models\Inventory;

class WarehouseRepositoryTest extends TestCase
{

    private WarehouseRepositoryInterface $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(WarehouseRepositoryInterface::class);
    }

    /**
     * Test getAll returns paginated warehouses.
     */
    public function test_get_all_returns_paginated_warehouses(): void
    {
        Warehouse::factory()->count(20)->create();

        $result = $this->repository->getAll(15);

        $this->assertEquals(15, $result->count());
        $this->assertEquals(20, $result->total());
    }

    /**
     * Test create warehouse.
     */
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

    /**
     * Test update warehouse.
     */
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

    /**
     * Test delete warehouse.
     */
    public function test_delete_warehouse(): void
    {
        $warehouse = Warehouse::factory()->create();

        $this->repository->delete($warehouse);

        $this->assertDatabaseMissing('warehouses', [
            'id' => $warehouse->id,
        ]);
    }

    /**
     * Test getWarehousesWithInventoryCount returns warehouses with inventory count.
     */
    public function test_get_warehouses_with_inventory_count(): void
    {
        $warehouse1 = Warehouse::factory()->create();
        $warehouse2 = Warehouse::factory()->create();

        // Create products and inventories for testing
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

        $result = $this->repository->getWarehousesWithInventoryCount();

        $this->assertCount(2, $result);
        $wh1 = $result->firstWhere('id', $warehouse1->id);
        $wh2 = $result->firstWhere('id', $warehouse2->id);
        $this->assertEquals(2, $wh1->inventories_count);
        $this->assertEquals(1, $wh2->inventories_count);
    }
}
