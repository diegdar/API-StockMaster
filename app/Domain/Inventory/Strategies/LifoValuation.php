<?php
declare(strict_types=1);

namespace App\Domain\Inventory\Strategies;

use App\Domain\Inventory\Contracts\InventoryValuationStrategy;
use App\Models\Product;
use Illuminate\Support\Collection;

class LifoValuation implements InventoryValuationStrategy
{
    /**
     * Calculate the total valuation of a product using the LIFO method.
     */
    public function calculate(Product $product): float
    {
        $inMovements = $this->getInMovements($product);
        $totalOutQuantity = $this->getTotalOutQuantity($product);

        return $this->calculateValuationFromMovements($inMovements, $totalOutQuantity);
    }

    /**
     * Get all IN stock movements for a product, ordered by creation date (DESC for LIFO).
     */
    private function getInMovements(Product $product): Collection
    {
        return $product->stockMovements()
            ->where('type', 'in')
            ->whereNotNull('unit_cost')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get the total quantity of OUT stock movements for a product.
     */
    private function getTotalOutQuantity(Product $product): float
    {
        return (float) $product->stockMovements()
            ->where('type', 'out')
            ->sum('quantity');
    }

    /**
     * Core LIFO valuation logic: calculates the value of remaining stock.
     */
    private function calculateValuationFromMovements(Collection $inMovements, float $totalOut): float
    {
        $remainingValue = 0.0;
        $processedOut = 0.0;

        foreach ($inMovements as $movement) {
            if ($processedOut >= $totalOut) {
                // All OUTs accounted for, this entire batch remains
                $remainingValue += $movement->quantity * $movement->unit_cost;
                continue;
            }

            // Check if this batch is fully consumed or partially consumed
            $remainingInBatch = (float) $movement->quantity;
            $neededToCoverOut = $totalOut - $processedOut;

            if ($neededToCoverOut >= $remainingInBatch) {
                // This entire batch is consumed by OUTs
                $processedOut += $remainingInBatch;
            } else {
                // Partial consumption
                $remainingInBatch -= $neededToCoverOut;
                $processedOut += $neededToCoverOut;
                // Add the value of the remaining part
                $remainingValue += $remainingInBatch * $movement->unit_cost;
            }
        }

        return (float) $remainingValue;
    }
}
