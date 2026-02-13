<?php
declare(strict_types=1);

namespace Tests\Unit\Domain\Inventory\Services;

use App\Domain\Inventory\Services\StockService;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\StockMovement;
use App\Models\User;
use Tests\TestCase;

class StockServiceTest extends TestCase
{
    private StockService $service;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
        $this->service = app(StockService::class);
    }

    public function test_it_can_record_an_in_movement()
    {
        $product = Product::factory()->create();
        $warehouse = Warehouse::factory()->create();

        $movement = $this->service->recordIn($product, $warehouse, 10, 100.0);

        $this->assertInstanceOf(StockMovement::class, $movement);
        $this->assertEquals('in', $movement->type);
        $this->assertEquals(10, $movement->quantity);
        $this->assertEquals(100.0, (float) $movement->unit_cost);
        $this->assertEquals($product->id, $movement->product_id);
        $this->assertEquals($warehouse->id, $movement->warehouse_id);

        $this->assertDatabaseHas('stock_movements', [
            'id' => $movement->id,
            'type' => 'in',
            'quantity' => 10,
        ]);
    }

    public function test_it_can_record_an_out_movement()
    {
        $product = Product::factory()->create();
        $warehouse = Warehouse::factory()->create();

        $movement = $this->service->recordOut($product, $warehouse, 5);

        $this->assertInstanceOf(StockMovement::class, $movement);
        $this->assertEquals('out', $movement->type);
        $this->assertEquals(5, $movement->quantity);
        $this->assertEquals($product->id, $movement->product_id);
        $this->assertEquals($warehouse->id, $movement->warehouse_id);

        $this->assertDatabaseHas('stock_movements', [
            'id' => $movement->id,
            'type' => 'out',
            'quantity' => 5,
        ]);
    }

    public function test_it_can_record_a_transfer_movement()
    {
        $product = Product::factory()->create();
        $sourceWarehouse = Warehouse::factory()->create(['name' => 'Source']);
        $destinationWarehouse = Warehouse::factory()->create(['name' => 'Destination']);

        $movements = $this->service->recordTransfer($product, $sourceWarehouse, $destinationWarehouse, 3);

        $this->assertCount(2, $movements);

        $out = $movements->firstWhere('type', 'out');
        $in = $movements->firstWhere('type', 'in');

        $this->assertEquals('out', $out->type);
        $this->assertEquals($sourceWarehouse->id, $out->warehouse_id);

        $this->assertEquals('in', $in->type);
        $this->assertEquals($destinationWarehouse->id, $in->warehouse_id);

        $this->assertDatabaseCount('stock_movements', 2);
    }
}
