<?php
declare(strict_types=1);

namespace Tests\Unit\DTO\Supplier;

use App\DTO\Supplier\UpdateSupplierDTO;
use Tests\TestCase;

class UpdateSupplierDTOTest extends TestCase
{
    /**
     * Test that UpdateSupplierDTO correctly transforms partial data.
     */
    public function test_update_supplier_dto_partial_update(): void
    {
        $data = [
            'name' => 'Proveedor Actualizado',
        ];

        $dto = UpdateSupplierDTO::fromArray($data);

        $this->assertEquals('Proveedor Actualizado', $dto->name);
        $this->assertNull($dto->contactEmail);
        $this->assertNull($dto->phone);
        $this->assertNull($dto->address);
        $this->assertNull($dto->isActive);
    }

    /**
     * Test that UpdateSupplierDTO handles all fields.
     */
    public function test_update_supplier_dto_full_update(): void
    {
        $data = [
            'name' => 'Proveedor Completo',
            'contact_email' => 'nuevo@proveedor.com',
            'phone' => '+34999999999',
            'address' => 'Nueva Dirección',
            'is_active' => false,
        ];

        $dto = UpdateSupplierDTO::fromArray($data);

        $this->assertEquals('Proveedor Completo', $dto->name);
        $this->assertEquals('nuevo@proveedor.com', $dto->contactEmail);
        $this->assertEquals('+34999999999', $dto->phone);
        $this->assertEquals('Nueva Dirección', $dto->address);
        $this->assertFalse($dto->isActive);
    }

    /**
     * Test that UpdateSupplierDTO handles empty array.
     */
    public function test_update_supplier_dto_empty_data(): void
    {
        $data = [];

        $dto = UpdateSupplierDTO::fromArray($data);

        $this->assertNull($dto->name);
        $this->assertNull($dto->contactEmail);
        $this->assertNull($dto->phone);
        $this->assertNull($dto->address);
        $this->assertNull($dto->isActive);
    }

    /**
     * Test that UpdateSupplierDTO converts to array.
     */
    public function test_update_supplier_dto_to_array(): void
    {
        $data = [
            'name' => 'Proveedor Test',
            'is_active' => true,
        ];

        $dto = UpdateSupplierDTO::fromArray($data);
        $array = $dto->toArray();

        $this->assertIsArray($array);
        $this->assertEquals('Proveedor Test', $array['name']);
        $this->assertTrue($array['is_active']);
    }
}
