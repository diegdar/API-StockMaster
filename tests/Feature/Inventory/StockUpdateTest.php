<?php
declare(strict_types=1);

namespace Tests\Feature\Inventory;

use App\Models\Product;
use App\Models\Warehouse;
use App\Models\StockMovement;
use App\Models\Inventory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockUpdateTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_updates_inventory_on_incoming_movement()
    {
        $product = Product::factory()->create();
        $warehouse = Warehouse::factory()->create();

        // Ensure inventory record exists (or will be created by observer if we want that logic)
        $inventory = Inventory::factory()->create([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 50,
        ]);

        StockMovement::create([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'type' => 'in',
            'quantity' => 10,
        ]);

        $this->assertEquals(60, $inventory->fresh()->quantity);
    }

    /** @test */
    public function it_updates_inventory_on_outgoing_movement()
    {
        $product = Product::factory()->create();
        $warehouse = Warehouse::factory()->create();

        $inventory = Inventory::factory()->create([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 50,
        ]);

        StockMovement::create([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'type' => 'out',
            'quantity' => 10,
        ]);

        $this->assertEquals(40, $inventory->fresh()->quantity);
    }

    /** @test */
    public function it_creates_inventory_record_if_not_exists_on_incoming_movement()
    {
        $product = Product::factory()->create();
        $warehouse = Warehouse::factory()->create();

        StockMovement::create([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'type' => 'in',
            'quantity' => 25,
        ]);

        $this->assertDatabaseHas('inventories', [
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 25,
        ]);
    }
}
