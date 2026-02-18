<?php
declare(strict_types=1);

namespace Tests\Unit\Services\Warehouse;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\Warehouse;
use App\Services\WarehouseService;
use Tests\TestCase;

class WarehouseCapacityTest extends TestCase
{
    private WarehouseService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(WarehouseService::class);
    }

    public function test_get_warehouse_capacity_returns_correct_data(): void
    {
        $warehouse = Warehouse::factory()->create(['capacity' => 100000]);
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();

        Inventory::factory()->create([
            'warehouse_id' => $warehouse->id,
            'product_id' => $product1->id,
            'quantity' => 30000,
        ]);
        Inventory::factory()->create([
            'warehouse_id' => $warehouse->id,
            'product_id' => $product2->id,
            'quantity' => 20000,
        ]);

        $capacity = $this->service->getWarehouseCapacity($warehouse);

        $this->assertEquals(100000, $capacity['total_capacity']);
        $this->assertEquals(50000, $capacity['used_capacity']);
        $this->assertEquals(50000, $capacity['available_capacity']);
        $this->assertEquals(50.0, $capacity['utilization_percentage']);
    }

    public function test_get_warehouse_capacity_with_null_capacity(): void
    {
        $warehouse = Warehouse::factory()->create(['capacity' => null]);

        $capacity = $this->service->getWarehouseCapacity($warehouse);

        $this->assertNull($capacity['total_capacity']);
        $this->assertEquals(0, $capacity['used_capacity']);
        $this->assertNull($capacity['available_capacity']);
        $this->assertNull($capacity['utilization_percentage']);
    }

    public function test_get_warehouse_capacity_with_zero_inventory(): void
    {
        $warehouse = Warehouse::factory()->create(['capacity' => 50000]);

        $capacity = $this->service->getWarehouseCapacity($warehouse);

        $this->assertEquals(50000, $capacity['total_capacity']);
        $this->assertEquals(0, $capacity['used_capacity']);
        $this->assertEquals(50000, $capacity['available_capacity']);
        $this->assertEquals(0.0, $capacity['utilization_percentage']);
    }

    public function test_get_warehouse_capacity_full_utilization(): void
    {
        $warehouse = Warehouse::factory()->create(['capacity' => 10000]);
        $product = Product::factory()->create();

        Inventory::factory()->create([
            'warehouse_id' => $warehouse->id,
            'product_id' => $product->id,
            'quantity' => 10000,
        ]);

        $capacity = $this->service->getWarehouseCapacity($warehouse);

        $this->assertEquals(10000, $capacity['used_capacity']);
        $this->assertEquals(0, $capacity['available_capacity']);
        $this->assertEquals(100.0, $capacity['utilization_percentage']);
    }

    public function test_get_warehouses_with_capacity(): void
    {
        $warehouse1 = Warehouse::factory()->create(['name' => 'WH1', 'capacity' => 10000]);
        $warehouse2 = Warehouse::factory()->create(['name' => 'WH2', 'capacity' => 20000]);
        $product = Product::factory()->create();

        Inventory::factory()->create([
            'warehouse_id' => $warehouse1->id,
            'product_id' => $product->id,
            'quantity' => 5000,
        ]);

        $result = $this->service->getWarehousesWithCapacity();

        $this->assertCount(2, $result);
        $wh1 = $result->firstWhere('id', $warehouse1->id);
        $this->assertEquals(10000, $wh1->total_capacity);
        $this->assertEquals(5000, $wh1->used_capacity);
        $this->assertEquals(50.0, $wh1->utilization_percentage);
    }
}
