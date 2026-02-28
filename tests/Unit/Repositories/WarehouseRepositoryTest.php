<?php
declare(strict_types=1);

namespace Tests\Unit\Repositories;

use App\Repositories\Contracts\WarehouseRepositoryInterface;
use Tests\TestCase;
use Tests\Traits\EntityCreationTrait;
use Illuminate\Database\QueryException;

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

        $this->assertDatabaseHas('warehouses', ['name' => 'New Warehouse']);
        $this->assertEquals('new-warehouse', $result->slug);
    }

    public function test_update_warehouse_name(): void
    {
        $warehouse = $this->createWarehouse(['name' => 'Old Name']);

        $result = $this->repository->update($warehouse, ['name' => 'Updated Name']);

        $this->assertEquals('Updated Name', $result->name);
        $this->assertEquals('updated-name', $result->slug);
    }

    public function test_update_warehouse_capacity(): void
    {
        $warehouse = $this->createWarehouse(['capacity' => 30000]);

        $result = $this->repository->update($warehouse, ['capacity' => 60000]);

        $this->assertEquals(60000, $result->capacity);
    }

    public function test_delete_warehouse(): void
    {
        $warehouse = $this->createWarehouse();

        $this->repository->delete($warehouse);

        $this->assertDatabaseMissing('warehouses', ['id' => $warehouse->id]);
    }

    public function test_get_warehouses_with_inventory_count(): void
    {
        $warehouses = $this->createWarehouses(2);
        $wh1 = $warehouses->first();
        $wh2 = $warehouses->last();

        $this->createProductWithInventory($wh1->id);
        $this->createProductWithInventory($wh1->id);
        $this->createProductWithInventory($wh2->id);

        $result = $this->repository->getWarehousesWithInventoryCount();

        $this->assertEquals(2, $result->firstWhere('id', $wh1->id)->inventories_count);
        $this->assertEquals(1, $result->firstWhere('id', $wh2->id)->inventories_count);
    }

    public function test_find_warehouse_by_id(): void
    {
        $warehouse = $this->createWarehouse(['name' => 'Test Warehouse']);

        $result = $this->repository->findById($warehouse->id);

        $this->assertEquals($warehouse->id, $result?->id);
        $this->assertEquals('Test Warehouse', $result?->name);
    }

    public function test_find_warehouse_by_id_returns_null_when_not_found(): void
    {
        $result = $this->repository->findById(99999);

        $this->assertNull($result);
    }

    public function test_get_used_capacity_with_inventory(): void
    {
        $warehouse = $this->createWarehouse(['capacity' => 1000]);
        $this->createProductWithInventory($warehouse->id, 300);

        $usedCapacity = $this->repository->getUsedCapacity($warehouse);

        $this->assertEquals(300, $usedCapacity);
    }

    public function test_get_used_capacity_without_inventory(): void
    {
        $warehouse = $this->createWarehouse(['capacity' => 1000]);

        $usedCapacity = $this->repository->getUsedCapacity($warehouse);

        $this->assertEquals(0, $usedCapacity);
    }

    public function test_get_used_capacity_with_null_capacity_warehouse(): void
    {
        $warehouse = $this->createWarehouse(['capacity' => null]);
        $this->createProductWithInventory($warehouse->id, 300);

        $usedCapacity = $this->repository->getUsedCapacity($warehouse);

        $this->assertEquals(300, $usedCapacity);
    }

    /**
     * @dataProvider warehouseCreateValidationProvider
     */
    public function test_create_warehouse_validation(array $data, string $expectedException): void
    {
        if ($expectedException !== '') {
            $this->expectException($expectedException);
        }

        $result = $this->repository->create($data);

        $this->assertNotNull($result);
    }

    public static function warehouseCreateValidationProvider(): array
    {
        return [
            'valid data' => [
                ['name' => 'Test WH', 'location' => 'Test Loc', 'capacity' => 1000, 'is_active' => true],
                '',
            ],
            'missing name throws QueryException' => [
                ['location' => 'Test Loc', 'capacity' => 1000],
                QueryException::class,
            ],
            'missing location throws QueryException' => [
                ['name' => 'Test WH', 'capacity' => 1000],
                QueryException::class,
            ],
        ];
    }

    /**
     * @dataProvider warehouseCapacityValidationProvider
     */
    public function test_warehouse_capacity_validation(int $capacity, int $expectedCapacity): void
    {
        $warehouse = $this->createWarehouse();

        $result = $this->repository->update($warehouse, ['capacity' => $capacity]);

        $this->assertEquals($expectedCapacity, $result->capacity);
    }

    public static function warehouseCapacityValidationProvider(): array
    {
        return [
            'positive capacity' => [5000, 5000],
            'zero capacity' => [0, 0],
        ];
    }
}
