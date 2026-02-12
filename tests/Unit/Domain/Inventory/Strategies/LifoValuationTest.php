<?php
declare(strict_types= 1);

namespace Tests\Unit\Domain\Inventory\Strategies;

use App\Domain\Inventory\Strategies\LifoValuation;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LifoValuationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_calculates_valuation_using_lifo_method()
    {
        // Arrange
        $product = Product::factory()->create();
        $warehouse = Warehouse::factory()->create();

        // 1. IN: 10 units @ $50 (Oldest)
        StockMovement::factory()->create([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'type' => 'in',
            'quantity' => 10,
            'unit_cost' => 50,
            'created_at' => now()->subDays(5),
        ]);

        // 2. IN: 10 units @ $60 (Newest)
        StockMovement::factory()->create([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'type' => 'in',
            'quantity' => 10,
            'unit_cost' => 60,
            'created_at' => now()->subDays(3),
        ]);

        // 3. OUT: 5 units (Should take from the NEWEST batch of $60)
        // LIFO means the last 5 IN are gone.
        // Remaining from Batch 2: 5 units @ $60.
        // Remaining from Batch 1: 10 units @ $50.
        // Value: (5 * $60) + (10 * $50) = $300 + $500 = $800.

        StockMovement::factory()->create([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'type' => 'out',
            'quantity' => 5,
            'created_at' => now()->subDays(1),
        ]);

        // Act
        $strategy = new LifoValuation();
        $value = $strategy->calculate($product);

        // Assert
        $this->assertEquals(800, $value);
    }
}
