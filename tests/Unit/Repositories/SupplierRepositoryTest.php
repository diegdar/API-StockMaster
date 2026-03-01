<?php
declare(strict_types=1);

namespace Tests\Unit\Repositories;

use App\Models\Supplier;
use App\Repositories\SupplierRepository;
use Tests\TestCase;
use Tests\Traits\SupplierTestTrait;

class SupplierRepositoryTest extends TestCase
{
    use SupplierTestTrait;

    private SupplierRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new SupplierRepository();
    }

    public function test_get_all_returns_paginated_suppliers(): void
    {
        $this->createSuppliers(20);

        $result = $this->repository->getAll(5);

        $this->assertCount(5, $result->items());
        $this->assertTrue($result->hasPages());
    }

    public function test_get_suppliers_with_product_count(): void
    {
        $result = $this->createSupplierWithProducts(2);
        $supplier = $result['supplier'];

        $result = $this->repository->getSuppliersWithProductCount();

        $this->assertNotEmpty($result);
        $this->assertArrayHasKey('products_count', $result->first()->toArray());
    }

    public function test_create_supplier(): void
    {
        $data = [
            'name' => 'New Supplier',
            'contact_email' => 'test@supplier.com',
            'phone' => '+34999999999',
            'address' => 'Test Address',
        ];

        $result = $this->repository->create($data);

        $this->assertInstanceOf(Supplier::class, $result);
        $this->assertEquals('New Supplier', $result->name);
        $this->assertEquals('test@supplier.com', $result->contact_email);
    }

    public function test_update_supplier(): void
    {
        $supplier = $this->createSupplier([
            'name' => 'Old Name',
            'contact_email' => 'old@supplier.com',
        ]);

        $data = [
            'name' => 'Updated Name',
            'contact_email' => 'new@supplier.com',
        ];

        $result = $this->repository->update($supplier, $data);

        $this->assertEquals('Updated Name', $result->name);
        $this->assertEquals('new@supplier.com', $result->contact_email);
    }

    public function test_delete_supplier(): void
    {
        $supplier = $this->createSupplier();

        $this->repository->delete($supplier);

        $this->assertNull(Supplier::find($supplier->id));
    }

    public function test_activate_supplier(): void
    {
        $supplier = $this->createSupplier(['is_active' => false]);

        $result = $this->repository->activate($supplier);

        $this->assertTrue($result->is_active);
    }

    public function test_deactivate_supplier(): void
    {
        $supplier = $this->createSupplier(['is_active' => true]);

        $result = $this->repository->deactivate($supplier);

        $this->assertFalse($result->is_active);
    }
}
