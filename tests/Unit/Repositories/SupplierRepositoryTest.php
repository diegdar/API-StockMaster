<?php
declare(strict_types=1);

namespace Tests\Unit\Repositories;

use App\Models\Supplier;
use App\Repositories\SupplierRepository;
use Tests\TestCase;

class SupplierRepositoryTest extends TestCase
{
    private SupplierRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new SupplierRepository();
    }

    public function test_get_all_returns_paginated_suppliers(): void
    {
        Supplier::factory()->count(20)->create();

        $result = $this->repository->getAll(5);

        $this->assertCount(5, $result->items());
        $this->assertTrue($result->hasPages());
    }

    public function test_get_suppliers_with_product_count(): void
    {
        $supplier = Supplier::factory()->create();
        $category = \App\Models\Category::factory()->create();

        $supplier->products()->createMany([
            [
                'name' => 'Product 1',
                'sku' => 'SKU-001',
                'description' => 'Test product 1',
                'unit_price' => 10.00,
                'unit_cost' => 5.00,
                'category_id' => $category->id,
            ],
            [
                'name' => 'Product 2',
                'sku' => 'SKU-002',
                'description' => 'Test product 2',
                'unit_price' => 20.00,
                'unit_cost' => 10.00,
                'category_id' => $category->id,
            ],
        ]);

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
        $supplier = Supplier::factory()->create([
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
        $supplier = Supplier::factory()->create();

        $this->repository->delete($supplier);

        $this->assertNull(Supplier::find($supplier->id));
    }

    public function test_activate_supplier(): void
    {
        $supplier = Supplier::factory()->create(['is_active' => false]);

        $result = $this->repository->activate($supplier);

        $this->assertTrue($result->is_active);
    }

    public function test_deactivate_supplier(): void
    {
        $supplier = Supplier::factory()->create(['is_active' => true]);

        $result = $this->repository->deactivate($supplier);

        $this->assertFalse($result->is_active);
    }
}
