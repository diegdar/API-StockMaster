<?php
declare(strict_types=1);

namespace Tests\Unit\Observers;

use App\Models\Warehouse;

use Tests\TestCase;

class WarehouseObserverTest extends TestCase
{

    /**
     * Test that slug is auto-generated from name on create.
     */
    public function test_observer_generates_slug_on_create(): void
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
    public function test_observer_regenerates_slug_on_name_change(): void
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
    public function test_observer_handles_duplicate_slugs(): void
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

    /**
     * Test that slug is not changed when other fields are updated.
     */
    public function test_observer_does_not_change_slug_on_other_field_update(): void
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
    public function test_observer_handles_special_characters_in_name(): void
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
