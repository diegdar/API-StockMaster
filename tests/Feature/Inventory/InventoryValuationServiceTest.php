<?php
declare(strict_types=1);

namespace Tests\Feature\Inventory;

use App\Domain\Inventory\Services\InventoryValuationService;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryValuationServiceTest extends TestCase
{
    use RefreshDatabase;

    private InventoryValuationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(InventoryValuationService::class);
    }

    /** @test */
    public function it_calculates_valuation_using_the_correct_strategy()
    {
        $product = Product::factory()->create(['valuation_strategy' => 'fifo']);
        $warehouse = Warehouse::factory()->create();

        // 1. IN: 10 units @ $10
        StockMovement::factory()->create([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'type' => 'in',
            'quantity' => 10,
            'unit_cost' => 10,
        ]);

        // 2. IN: 10 units @ $20
        StockMovement::factory()->create([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'type' => 'in',
            'quantity' => 10,
            'unit_cost' => 20,
        ]);

        // 3. OUT: 15 units
        StockMovement::factory()->create([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'type' => 'out',
            'quantity' => 15,
        ]);

        // FIFO: 10@$10 (gone) + 5@$20 (gone) -> 5@$20 remains = $100
        $product->valuation_strategy = 'fifo';
        $fifoValue = $this->service->calculate($product);
        $this->assertEquals(100.0, (float) $fifoValue);

        // LIFO: 10@$20 (gone) + 5@$10 (gone) -> 5@$10 remains = $50
        $product->valuation_strategy = 'lifo';
        $lifoValue = $this->service->calculate($product);
        $this->assertEquals(50.0, (float) $lifoValue);

        // AVG: 20 units @ $15 avg cost. 15 out -> 5 remains @ $15 = $75
        $product->valuation_strategy = 'avg';
        $avgValue = $this->service->calculate($product);
        $this->assertEquals(75.0, (float) $avgValue);
    }
}
