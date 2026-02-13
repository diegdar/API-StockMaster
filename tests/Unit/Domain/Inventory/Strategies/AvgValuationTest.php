<?php
declare(strict_types=1);

namespace Tests\Unit\Domain\Inventory\Strategies;

use App\Domain\Inventory\Strategies\AvgValuation;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Warehouse;
use Tests\TestCase;

class AvgValuationTest extends TestCase
{
    public function test_it_calculates_valuation_using_average_cost_method()
    {
        // Arrange
        $product = Product::factory()->create();
        $warehouse = Warehouse::factory()->create();

        // 1. IN: 10 units @ $50
        StockMovement::factory()->create([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'type' => 'in',
            'quantity' => 10,
            'unit_cost' => 50,
        ]);

        // 2. IN: 10 units @ $60
        StockMovement::factory()->create([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'type' => 'in',
            'quantity' => 10,
            'unit_cost' => 60,
        ]);

        // Total IN: 20 units, Total Cost: $1100 -> Avg Cost: $55

        // 3. OUT: 5 units
        // Remaining: 15 units.
        // Value: 15 * $55 = $825.

        StockMovement::factory()->create([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'type' => 'out',
            'quantity' => 5,
        ]);

        // Act
        $strategy = new AvgValuation();
        $value = $strategy->calculate($product);

        // Assert
        $this->assertEquals(825, $value);
    }
}