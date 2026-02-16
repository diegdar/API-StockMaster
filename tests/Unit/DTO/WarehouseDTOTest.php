<?php
declare(strict_types=1);

namespace Tests\Unit\DTO;

use App\DTO\Warehouse\CreateWarehouseDTO;
use App\DTO\Warehouse\UpdateWarehouseDTO;
use Tests\TestCase;

class WarehouseDTOTest extends TestCase
{
    // ==========================================
    // CreateWarehouseDTO Tests
    // ==========================================

    /**
     * Test CreateWarehouseDTO can be created with required fields.
     */
    public function test_create_warehouse_dto_can_be_created_with_required_fields(): void
    {
        $dto = new CreateWarehouseDTO(
            name: 'Almacén Central',
            location: 'Madrid, España'
        );

        $this->assertEquals('Almacén Central', $dto->name);
        $this->assertEquals('Madrid, España', $dto->location);
        $this->assertNull($dto->capacity);
        $this->assertTrue($dto->isActive);
    }

    /**
     * Test CreateWarehouseDTO can be created with all fields.
     */
    public function test_create_warehouse_dto_can_be_created_with_all_fields(): void
    {
        $dto = new CreateWarehouseDTO(
            name: 'Almacén Central',
            location: 'Madrid, España',
            capacity: 50000,
            isActive: false
        );

        $this->assertEquals('Almacén Central', $dto->name);
        $this->assertEquals('Madrid, España', $dto->location);
        $this->assertEquals(50000, $dto->capacity);
        $this->assertFalse($dto->isActive);
    }

    /**
     * Test CreateWarehouseDTO fromArray method.
     */
    public function test_create_warehouse_dto_from_array(): void
    {
        $dto = CreateWarehouseDTO::fromArray([
            'name' => 'Almacén Norte',
            'location' => 'Bilbao, España',
            'capacity' => 35000,
            'is_active' => true,
        ]);

        $this->assertEquals('Almacén Norte', $dto->name);
        $this->assertEquals('Bilbao, España', $dto->location);
        $this->assertEquals(35000, $dto->capacity);
        $this->assertTrue($dto->isActive);
    }

    /**
     * Test CreateWarehouseDTO toArray method.
     */
    public function test_create_warehouse_dto_to_array(): void
    {
        $dto = new CreateWarehouseDTO(
            name: 'Almacén Central',
            location: 'Madrid, España',
            capacity: 50000,
            isActive: true
        );

        $array = $dto->toArray();

        $this->assertEquals([
            'name' => 'Almacén Central',
            'location' => 'Madrid, España',
            'capacity' => 50000,
            'is_active' => true,
        ], $array);
    }

    /**
     * Test CreateWarehouseDTO toArray filters null values.
     */
    public function test_create_warehouse_dto_to_array_filters_null(): void
    {
        $dto = new CreateWarehouseDTO(
            name: 'Almacén Central',
            location: 'Madrid, España'
        );

        $array = $dto->toArray();

        $this->assertArrayNotHasKey('capacity', $array);
        $this->assertEquals([
            'name' => 'Almacén Central',
            'location' => 'Madrid, España',
            'is_active' => true,
        ], $array);
    }

    // ==========================================
    // UpdateWarehouseDTO Tests
    // ==========================================

    /**
     * Test UpdateWarehouseDTO can be created with partial fields.
     */
    public function test_update_warehouse_dto_can_be_created_with_partial_fields(): void
    {
        $dto = new UpdateWarehouseDTO(
            name: 'Almacén Actualizado'
        );

        $this->assertEquals('Almacén Actualizado', $dto->name);
        $this->assertNull($dto->location);
        $this->assertNull($dto->capacity);
        $this->assertNull($dto->isActive);
    }

    /**
     * Test UpdateWarehouseDTO fromArray method.
     */
    public function test_update_warehouse_dto_from_array(): void
    {
        $dto = UpdateWarehouseDTO::fromArray([
            'name' => 'Nuevo Nombre',
            'is_active' => false,
        ]);

        $this->assertEquals('Nuevo Nombre', $dto->name);
        $this->assertNull($dto->location);
        $this->assertNull($dto->capacity);
        $this->assertFalse($dto->isActive);
    }

    /**
     * Test UpdateWarehouseDTO toArray method filters null values.
     */
    public function test_update_warehouse_dto_to_array_filters_null(): void
    {
        $dto = new UpdateWarehouseDTO(
            name: 'Nuevo Nombre',
            location: null,
            capacity: 60000,
            isActive: null
        );

        $array = $dto->toArray();

        $this->assertEquals([
            'name' => 'Nuevo Nombre',
            'capacity' => 60000,
        ], $array);
    }

    /**
     * Test UpdateWarehouseDTO with all fields.
     */
    public function test_update_warehouse_dto_with_all_fields(): void
    {
        $dto = new UpdateWarehouseDTO(
            name: 'Almacén Actualizado',
            location: 'Nueva Ubicación',
            capacity: 75000,
            isActive: false
        );

        $array = $dto->toArray();

        $this->assertEquals([
            'name' => 'Almacén Actualizado',
            'location' => 'Nueva Ubicación',
            'capacity' => 75000,
            'is_active' => false,
        ], $array);
    }
}
