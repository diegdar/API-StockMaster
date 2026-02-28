<?php
declare(strict_types=1);

namespace Tests\Unit\DTO\Supplier;

use App\DTO\Supplier\CreateSupplierDTO;
use Tests\TestCase;

class CreateSupplierDTOTest extends TestCase
{
    /**
     * Test that CreateSupplierDTO correctly transforms data.
     */
    public function test_create_supplier_dto(): void
    {
        $data = [
            'name' => 'Proveedor Principal',
            'contact_email' => 'contacto@proveedor.com',
            'phone' => '+34999999999',
            'address' => 'Calle Principal 123',
        ];

        $dto = CreateSupplierDTO::fromArray($data);

        $this->assertEquals('Proveedor Principal', $dto->name);
        $this->assertEquals('contacto@proveedor.com', $dto->contactEmail);
        $this->assertEquals('+34999999999', $dto->phone);
        $this->assertEquals('Calle Principal 123', $dto->address);
    }

    /**
     * Test that CreateSupplierDTO handles optional fields.
     */
    public function test_create_supplier_dto_with_optional_fields(): void
    {
        $data = [
            'name' => 'Proveedor Minimal',
            'contact_email' => null,
            'phone' => null,
            'address' => null,
        ];

        $dto = CreateSupplierDTO::fromArray($data);

        $this->assertEquals('Proveedor Minimal', $dto->name);
        $this->assertNull($dto->contactEmail);
        $this->assertNull($dto->phone);
        $this->assertNull($dto->address);
    }

    /**
     * Test that CreateSupplierDTO can be created from request.
     */
    public function test_create_supplier_dto_from_request(): void
    {
        $request = new \Illuminate\Http\Request([
            'name' => 'Proveedor desde Request',
            'contact_email' => 'test@test.com',
            'phone' => '+34888888888',
            'address' => 'Dirección de prueba',
        ]);

        $dto = CreateSupplierDTO::fromArray($request->all());

        $this->assertEquals('Proveedor desde Request', $dto->name);
        $this->assertEquals('test@test.com', $dto->contactEmail);
    }

    /**
     * Test that CreateSupplierDTO converts to array.
     */
    public function test_create_supplier_dto_to_array(): void
    {
        $data = [
            'name' => 'Proveedor Test',
            'contact_email' => 'test@test.com',
            'phone' => '+34777777777',
            'address' => 'Test Address',
        ];

        $dto = CreateSupplierDTO::fromArray($data);
        $array = $dto->toArray();

        $this->assertIsArray($array);
        $this->assertEquals('Proveedor Test', $array['name']);
        $this->assertEquals('test@test.com', $array['contact_email']);
    }
}
