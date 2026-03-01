<?php
declare(strict_types=1);

namespace Tests\Unit\Services;

use App\DTO\Supplier\CreateSupplierDTO;
use App\DTO\Supplier\UpdateSupplierDTO;
use App\Exceptions\DeletionException;
use App\Models\Supplier;
use App\Repositories\Contracts\SupplierRepositoryInterface;
use App\Services\SupplierService;
use Tests\TestCase;
use Tests\Traits\SupplierTestTrait;

class SupplierServiceTest extends TestCase
{
    use SupplierTestTrait;

    private SupplierService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new SupplierService(
            app(SupplierRepositoryInterface::class)
        );
    }

    public function test_get_all_suppliers(): void
    {
        $this->createSuppliers(10);

        $result = $this->service->getAllSuppliers(5);

        $this->assertCount(5, $result->items());
    }

    public function test_create_supplier(): void
    {
        $dto = new CreateSupplierDTO(
            name: 'Test Supplier',
            contactEmail: 'test@supplier.com',
            phone: '+34999999999',
            address: 'Test Address'
        );

        $result = $this->service->createSupplier($dto);

        $this->assertInstanceOf(Supplier::class, $result);
        $this->assertEquals('Test Supplier', $result->name);
    }

    public function test_update_supplier(): void
    {
        $supplier = $this->createSupplier();

        $dto = new UpdateSupplierDTO(
            name: 'Updated Supplier',
            contactEmail: 'updated@supplier.com'
        );

        $result = $this->service->updateSupplier($supplier, $dto);

        $this->assertEquals('Updated Supplier', $result->name);
        $this->assertEquals('updated@supplier.com', $result->contact_email);
    }

    public function test_delete_supplier(): void
    {
        $supplier = $this->createSupplier();

        $this->service->deleteSupplier($supplier);

        $this->assertNull(Supplier::find($supplier->id));
    }

    public function test_delete_supplier_with_products_throws_exception(): void
    {
        $result = $this->createSupplierWithProducts(1);
        $supplier = $result['supplier'];

        $this->expectException(DeletionException::class);

        $this->service->deleteSupplier($supplier);
    }

    public function test_activate_supplier(): void
    {
        $supplier = $this->createSupplier(['is_active' => false]);

        $result = $this->service->activateSupplier($supplier);

        $this->assertTrue($result->is_active);
    }

    public function test_deactivate_supplier(): void
    {
        $supplier = $this->createSupplier(['is_active' => true]);

        $result = $this->service->deactivateSupplier($supplier);

        $this->assertFalse($result->is_active);
    }

    public function test_get_supplier_performance(): void
    {
        $result = $this->createSupplierWithProducts(2);
        $supplier = $result['supplier'];

        $perf = $this->service->getSupplierPerformance($supplier);

        $this->assertArrayHasKey('supplier_id', $perf);
        $this->assertArrayHasKey('supplier_name', $perf);
        $this->assertArrayHasKey('total_products', $perf);
        $this->assertEquals(2, $perf['total_products']);
    }
}
