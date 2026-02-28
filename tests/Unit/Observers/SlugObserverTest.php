<?php
declare(strict_types=1);

namespace Tests\Unit\Observers;

use App\Models\Category;
use App\Models\Supplier;
use App\Models\Warehouse;
use Tests\TestCase;

class SlugObserverTest extends TestCase
{
    // ==================== WAREHOUSE TESTS ====================

    /**
     * Test that slug is auto-generated from name on create.
     */
    public function test_slug_observer_generates_slug_on_create_for_warehouse(): void
    {
        $warehouse = Warehouse::create([
            'name' => 'Almacén Central',
            'location' => 'Madrid, España',
            'capacity' => 50000,
            'is_active' => true,
        ]);

        $this->assertEquals('almacen-central', $warehouse->slug);
    }

    /**
     * Test that slug is regenerated when name changes.
     */
    public function test_slug_observer_regenerates_slug_on_name_change_for_warehouse(): void
    {
        $warehouse = Warehouse::create([
            'name' => 'Almacén Central',
            'location' => 'Madrid, España',
            'capacity' => 50000,
            'is_active' => true,
        ]);

        $this->assertEquals('almacen-central', $warehouse->slug);

        $warehouse->update(['name' => 'Almacén Norte']);

        $this->assertEquals('almacen-norte', $warehouse->fresh()->slug);
    }

    /**
     * Test that duplicate slugs get numeric suffix.
     */
    public function test_slug_observer_handles_duplicate_slugs_for_warehouse(): void
    {
        $warehouse1 = Warehouse::create([
            'name' => 'Almacén Central',
            'location' => 'Madrid, España',
            'capacity' => 50000,
            'is_active' => true,
        ]);

        $warehouse2 = Warehouse::create([
            'name' => 'Almacén Central',
            'location' => 'Barcelona, España',
            'capacity' => 40000,
            'is_active' => true,
        ]);

        $this->assertEquals('almacen-central', $warehouse1->slug);
        $this->assertEquals('almacen-central-1', $warehouse2->slug);
    }

    // ==================== CATEGORY TESTS ====================

    /**
     * Test that slug is auto-generated from name on create for Category.
     */
    public function test_slug_observer_generates_slug_on_create_for_category(): void
    {
        $category = Category::create([
            'name' => 'Electrónica',
            'description' => 'Productos electrónicos',
        ]);

        $this->assertEquals('electronica', $category->slug);
    }

    /**
     * Test that slug is regenerated when name changes for Category.
     */
    public function test_slug_observer_regenerates_slug_on_name_change_for_category(): void
    {
        $category = Category::create([
            'name' => 'Electrónica',
            'description' => 'Productos electrónicos',
        ]);

        $this->assertEquals('electronica', $category->slug);

        $category->update(['name' => 'Informática']);

        $this->assertEquals('informatica', $category->fresh()->slug);
    }

    /**
     * Test that duplicate slugs get numeric suffix for Category.
     */
    public function test_slug_observer_handles_duplicate_slugs_for_category(): void
    {
        $category1 = Category::create([
            'name' => 'Electrónica',
            'description' => 'Productos electrónicos',
        ]);

        $category2 = Category::create([
            'name' => 'Electrónica',
            'description' => 'Otros productos electrónicos',
        ]);

        $this->assertEquals('electronica', $category1->slug);
        $this->assertEquals('electronica-1', $category2->slug);
    }

    // ==================== SUPPLIER TESTS ====================

    /**
     * Test that slug is auto-generated from name on create for Supplier.
     */
    public function test_slug_observer_generates_slug_on_create_for_supplier(): void
    {
        $supplier = Supplier::create([
            'name' => 'Proveedor Principal',
            'contact_email' => 'contacto@proveedor.com',
            'phone' => '+34999999999',
            'address' => 'Calle Principal 123',
            'is_active' => true,
        ]);

        $this->assertEquals('proveedor-principal', $supplier->slug);
    }

    /**
     * Test that slug is regenerated when name changes for Supplier.
     */
    public function test_slug_observer_regenerates_slug_on_name_change_for_supplier(): void
    {
        $supplier = Supplier::create([
            'name' => 'Proveedor Principal',
            'contact_email' => 'contacto@proveedor.com',
            'phone' => '+34999999999',
            'address' => 'Calle Principal 123',
            'is_active' => true,
        ]);

        $this->assertEquals('proveedor-principal', $supplier->slug);

        $supplier->update(['name' => 'Proveedor Secundario']);

        $this->assertEquals('proveedor-secundario', $supplier->fresh()->slug);
    }

    /**
     * Test that duplicate slugs get numeric suffix for Supplier.
     */
    public function test_slug_observer_handles_duplicate_slugs_for_supplier(): void
    {
        $supplier1 = Supplier::create([
            'name' => 'Proveedor Principal',
            'contact_email' => 'contacto@proveedor1.com',
            'phone' => '+34999999999',
            'address' => 'Calle Principal 123',
            'is_active' => true,
        ]);

        $supplier2 = Supplier::create([
            'name' => 'Proveedor Principal',
            'contact_email' => 'contacto@proveedor2.com',
            'phone' => '+34888888888',
            'address' => 'Calle Secundaria 456',
            'is_active' => true,
        ]);

        $this->assertEquals('proveedor-principal', $supplier1->slug);
        $this->assertEquals('proveedor-principal-1', $supplier2->slug);
    }

    // ==================== COMMON TESTS ====================

    /**
     * Test that slug is not changed when other fields are updated.
     */
    public function test_slug_observer_does_not_change_slug_on_other_field_update(): void
    {
        $warehouse = Warehouse::create([
            'name' => 'Almacén Central',
            'location' => 'Madrid, España',
            'capacity' => 50000,
            'is_active' => true,
        ]);

        $originalSlug = $warehouse->slug;

        $warehouse->update(['capacity' => 60000, 'is_active' => false]);

        $this->assertEquals($originalSlug, $warehouse->fresh()->slug);
    }

    /**
     * Test that slug handles special characters.
     */
    public function test_slug_observer_handles_special_characters_in_name(): void
    {
        $warehouse = Warehouse::create([
            'name' => 'Almacén & Distribución #1',
            'location' => 'Madrid, España',
            'capacity' => 50000,
            'is_active' => true,
        ]);

        $this->assertEquals('almacen-distribucion-1', $warehouse->slug);
    }
}
