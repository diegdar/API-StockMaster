<?php
declare(strict_types=1);

namespace Tests\Unit\Resources;

use App\Http\Resources\WarehouseResource;
use App\Models\Warehouse;

use Tests\TestCase;

class WarehouseResourceTest extends TestCase
{
    /**
     * Test WarehouseResource returns correct structure.
     */
    public function test_warehouse_resource_returns_correct_structure(): void
    {
        $warehouse = Warehouse::factory()->create([
            'name' => 'Almacén Central',
            'location' => 'Madrid, España',
            'capacity' => 50000,
            'is_active' => true,
        ]);

        $resource = new WarehouseResource($warehouse);
        $data = $resource->toArray(request());

        $this->assertEquals('Almacén Central', $data['name']);
        $this->assertEquals('almacen-central', $data['slug']);
        $this->assertEquals('Madrid, España', $data['location']);
        $this->assertEquals(50000, $data['capacity']);
        $this->assertTrue($data['is_active']);
    }

    /**
     * Test WarehouseResource does not expose id field.
     */
    public function test_warehouse_resource_does_not_expose_id(): void
    {
        $warehouse = Warehouse::factory()->create();

        $resource = new WarehouseResource($warehouse);
        $data = $resource->toArray(request());

        $this->assertArrayNotHasKey('id', $data);
    }

    /**
     * Test WarehouseResource handles null capacity.
     */
    public function test_warehouse_resource_handles_null_capacity(): void
    {
        $warehouse = Warehouse::factory()->create([
            'name' => 'Almacén Sin Capacidad',
            'capacity' => null,
        ]);

        $resource = new WarehouseResource($warehouse);
        $data = $resource->toArray(request());

        $this->assertNull($data['capacity']);
    }

    /**
     * Test WarehouseResource includes inventories_count when loaded.
     */
    public function test_warehouse_resource_includes_inventories_count_when_loaded(): void
    {
        $warehouse = Warehouse::factory()->create();
        $warehouse->loadCount('inventories');

        $resource = new WarehouseResource($warehouse);
        $data = $resource->toArray(request());

        $this->assertArrayHasKey('inventories_count', $data);
    }

    /**
     * Test WarehouseResource does not include inventories_count when not loaded.
     */
    public function test_warehouse_resource_does_not_include_inventories_count_when_not_loaded(): void
    {
        $warehouse = Warehouse::factory()->create();

        $resource = new WarehouseResource($warehouse);

        // Use response()->json() to properly serialize the resource
        $response = response()->json($resource);
        $data = json_decode($response->getContent(), true);

        $this->assertArrayNotHasKey('inventories_count', $data);
    }

    /**
     * Test WarehouseResource with is_active false.
     */
    public function test_warehouse_resource_with_is_active_false(): void
    {
        $warehouse = Warehouse::factory()->create([
            'is_active' => false,
        ]);

        $resource = new WarehouseResource($warehouse);
        $data = $resource->toArray(request());

        $this->assertFalse($data['is_active']);
    }
}
