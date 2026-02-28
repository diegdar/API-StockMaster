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

class SupplierServiceTest extends TestCase
{
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
        Supplier::factory()->count(10)->create();

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
        $supplier = Supplier::factory()->create();

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
        $supplier = Supplier::factory()->create();

        $this->service->deleteSupplier($supplier);

        $this->assertNull(Supplier::find($supplier->id));
    }

    public function test_delete_supplier_with_products_throws_exception(): void
    {
        $supplier = Supplier::factory()->create();
        $category = \App\Models\Category::factory()->create();

        $supplier->products()->createMany([
            [
                'name' => 'Product 1',
                'sku' => 'SKU-001',
                'description' => 'Test product',
                'unit_price' => 10.00,
                'unit_cost' => 5.00,
                'category_id' => $category->id,
            ],
        ]);

        $this->expectException(DeletionException::class);

        $this->service->deleteSupplier($supplier);
    }

    public function test_activate_supplier(): void
    {
        $supplier = Supplier::factory()->create(['is_active' => false]);

        $result = $this->service->activateSupplier($supplier);

        $this->assertTrue($result->is_active);
    }

    public function test_deactivate_supplier(): void
    {
        $supplier = Supplier::factory()->create(['is_active' => true]);

        $result = $this->service->deactivateSupplier($supplier);

        $this->assertFalse($result->is_active);
    }

    public function test_get_supplier_performance(): void
    {
        $supplier = Supplier::factory()->create();
        $category = \App\Models\Category::factory()->create();

        $supplier->products()->createMany([
            [
                'name' => 'Product 1',
                'sku' => 'SKU-001',
                'description' => 'Test product 1',
                'unit_price' => 100.00,
                'unit_cost' => 50.00,
                'category_id' => $category->id,
            ],
            [
                'name' => 'Product 2',
                'sku' => 'SKU-002',
                'description' => 'Test product 2',
                'unit_price' => 200.00,
                'unit_cost' => 100.00,
                'category_id' => $category->id,
            ],
        ]);

        $result = $this->service->getSupplierPerformance($supplier);

        $this->assertArrayHasKey('supplier_id', $result);
        $this->assertArrayHasKey('supplier_name', $result);
        $this->assertArrayHasKey('total_products', $result);
        $this->assertEquals(2, $result['total_products']);
    }
}
