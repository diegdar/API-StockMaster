<?php
declare(strict_types=1);

namespace Tests\Unit\Domain\Inventory\Strategies;
use App\Models\StockMovement;


use App\Domain\Inventory\Strategies\FifoValuation;
use App\Models\Product;
use Tests\TestCase;
use App\Models\Warehouse;

class FifoValuationTest extends TestCase
{
    public function test_it_calculates_valuation_using_fifo_method()
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
            'created_at' => now()->subDays(5),
        ]);

        // 2. IN: 10 units @ $60
        StockMovement::factory()->create([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'type' => 'in',
            'quantity' => 10,
            'unit_cost' => 60,
            'created_at' => now()->subDays(3),
        ]);

        // 3. OUT: 5 units (Should take from the first batch of $50)
        // Note: The Strategy might need to fetch *net* stock first. 
        // If we have 20 IN and 5 OUT, we have 15 Remaining.
        // FIFO means the first 5 IN are gone.
        // Remaining: 5 units @ $50 + 10 units @ $60.
        // Value: $250 + $600 = $850.

        // We simulate the OUT movement by creating a record
        StockMovement::factory()->create([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'type' => 'out',
            'quantity' => 5,
            'created_at' => now()->subDays(1),
        ]);

        // Act
        $strategy = new FifoValuation();
        $value = $strategy->calculate($product);

        // Assert
        $this->assertEquals(850, $value);
    }
}
